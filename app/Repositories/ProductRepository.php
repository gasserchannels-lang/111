<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Exceptions\ProductUpdateException;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class ProductRepository
{
    /**
     * Get paginated active products.
     *
     * @return LengthAwarePaginator<int, Product>
     */
    public function getPaginatedActive(int $perPage = 15): LengthAwarePaginator
    {
        return Product::query()
            ->with(['category', 'brand'])
            ->where('is_active', true)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Find product by slug with caching.
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     * @throws \InvalidArgumentException If slug is invalid
     */
    public function findBySlug(string $slug): ?Product
    {
        // Validate slug format
        if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            throw new InvalidArgumentException('Invalid slug format');
        }

        // Cache key includes version to allow mass cache invalidation
        $cacheKey = "product:slug:{$slug}:v1";

        $result = Cache::remember($cacheKey, now()->addHours(1), fn () => Product::query()
            ->with(['category', 'brand', 'reviews'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first());

        return $result instanceof Product ? $result : null;
    }

    /**
     * Get related products with caching.
     *
     * @return Collection<int, Product>
     *
     * @throws \InvalidArgumentException If limit is invalid
     */
    public function getRelated(Product $product, int $limit = 4): Collection
    {
        // Validate limit
        if ($limit < 1 || $limit > 20) {
            throw new InvalidArgumentException('Limit must be between 1 and 20');
        }

        // Cache key includes product ID and limit
        $cacheKey = "product:{$product->id}:related:limit:{$limit}:v1";
        $cacheDuration = now()->addHours(1);

        $result = Cache::remember($cacheKey, $cacheDuration, fn () => Product::query()
            ->select(['id', 'name', 'slug', 'price', 'image', 'category_id'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->inRandomOrder()
            ->limit($limit)
            ->get());

        return $result instanceof Collection ? $result : new Collection;
    }

    /**
     * Search products with validation and rate limiting.
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Product>
     *
     * @throws ValidationException If filters are invalid
     * @throws \InvalidArgumentException If parameters are invalid
     */
    public function search(string $query, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        // Validate and sanitize inputs
        $query = strip_tags($query);
        $perPage = min(50, max(1, $perPage));

        // Validate filters
        $validator = Validator::make($filters, [
            'category_id' => 'sometimes|integer|exists:categories,id',
            'brand_id' => 'sometimes|integer|exists:brands,id',
            'min_price' => 'sometimes|numeric|min:0',
            'max_price' => 'sometimes|numeric|min:0|gte:min_price',
            'sort_by' => 'sometimes|in:price_asc,price_desc,name_asc,name_desc,latest',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Generate cache key based on parameters
        $queryHash = hash('sha256', $query ?: '');
        $filtersJson = json_encode($filters);
        $filtersHash = hash('sha256', is_string($filtersJson) ? $filtersJson : '');
        $cacheKey = sprintf(
            'products:search:%s:%s:%d:%d',
            $queryHash,
            $filtersHash,
            $perPage,
            is_numeric(request()->get('page', 1)) ? (int) request()->get('page', 1) : 1
        );

        $result = Cache::remember($cacheKey, now()->addMinutes(15), function () use ($query, $filters, $perPage) {
            $productsQuery = Product::query()
                ->select(['id', 'name', 'slug', 'price', 'image', 'category_id', 'brand_id', 'description'])
                ->with([
                    'category:id,name,slug',
                    'brand:id,name,slug',
                ])
                ->where('is_active', true)
                ->where(function ($q) use ($query): void {
                    $searchTerm = '%'.addcslashes($query, '%_').'%';
                    $q->where('name', 'like', $searchTerm)
                        ->orWhere('description', 'like', $searchTerm);
                });

            // Apply validated filters
            if (! empty($filters['category_id']) && is_numeric($filters['category_id'])) {
                $productsQuery->where('category_id', (int) $filters['category_id']);
            }

            if (! empty($filters['brand_id']) && is_numeric($filters['brand_id'])) {
                $productsQuery->where('brand_id', (int) $filters['brand_id']);
            }

            if (! empty($filters['min_price']) && is_numeric($filters['min_price'])) {
                $productsQuery->where('price', '>=', (float) $filters['min_price']);
            }

            if (! empty($filters['max_price']) && is_numeric($filters['max_price'])) {
                $productsQuery->where('price', '<=', (float) $filters['max_price']);
            }

            // Apply sorting
            match ($filters['sort_by'] ?? 'latest') {
                'price_asc' => $productsQuery->orderBy('price', 'asc'),
                'price_desc' => $productsQuery->orderBy('price', 'desc'),
                'name_asc' => $productsQuery->orderBy('name', 'asc'),
                'name_desc' => $productsQuery->orderBy('name', 'desc'),
                default => $productsQuery->latest(),
            };

            return $productsQuery->paginate($perPage);
        });

        return $result instanceof LengthAwarePaginator ? $result : Product::query()->paginate($perPage);
    }

    /**
     * Update product price with validation, locking, and logging.
     *
     *
     * @throws ValidationException If price is invalid
     * @throws ProductUpdateException If update fails
     */
    public function updatePrice(Product $product, float $newPrice): bool
    {
        // Validate price
        $validator = Validator::make(
            ['price' => $newPrice],
            ['price' => 'required|numeric|min:0']
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Round to 2 decimal places
        $newPrice = round($newPrice, 2);

        // Cache keys for invalidation
        $cacheKeys = [
            "product:{$product->id}",
            "product:slug:{$product->slug}:v1",
        ];

        try {
            return DB::transaction(function () use ($product, $newPrice, $cacheKeys): true {
                $oldPrice = $product->price;

                // Lock the row for update
                $product = $product->lockForUpdate()->firstOrFail();

                $updated = $product->update(['price' => $newPrice]);

                if (! $updated) {
                    throw new ProductUpdateException('Failed to update product price');
                }

                // Log price change
                Log::info('Product price updated', [
                    'product_id' => $product->id,
                    'old_price' => $oldPrice,
                    'new_price' => $newPrice,
                    'user_id' => auth()->id(),
                    'ip' => request()->ip(),
                ]);

                // Create price history record (if priceHistory method exists)
                if (method_exists($product, 'priceHistory')) {
                    $product->priceHistory()->create([
                        'old_price' => $oldPrice,
                        'new_price' => $newPrice,
                        'changed_by' => auth()->id(),
                    ]);
                }

                // Invalidate cache
                foreach ($cacheKeys as $key) {
                    Cache::forget($key);
                }

                return true;
            });
        } catch (\Exception $e) {
            Log::error('Price update failed', [
                'product_id' => $product->id,
                'price' => $newPrice,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
