<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class ProductService
{
    public function __construct(
        private readonly ProductRepository $repository,
        private readonly CacheService $cache
    ) {}

    /**
     * Get paginated active products.
     *
     * @return LengthAwarePaginator<int, Product<\Database\Factories\ProductFactory>>
     */
    public function getPaginatedProducts(int $perPage = 15): LengthAwarePaginator
    {
        $page = request()->get('page', 1);
        $pageNumber = is_numeric($page) ? (int) $page : 1;

        $result = $this->cache->remember(
            'products.page.'.$pageNumber,
            3600,
            fn (): \Illuminate\Pagination\LengthAwarePaginator => $this->repository->getPaginatedActive($perPage),
            ['products']
        );

        // Return empty paginator if result is null
        if ($result === null) {
            return new LengthAwarePaginator(
                collect([]),
                0,
                $perPage,
                $pageNumber,
                ['path' => request()->url(), 'pageName' => 'page']
            );
        }

        return $result;
    }

    /**
     * Get product by slug.
     *
     * @return Product<\Database\Factories\ProductFactory>
     */
    public function getBySlug(string $slug): ?Product
    {
        return $this->cache->remember(
            'product.slug.'.$slug,
            3600,
            function () use ($slug): \App\Models\Product {
                $product = $this->repository->findBySlug($slug);
                if (! $product instanceof \App\Models\Product) {
                    throw new \Illuminate\Database\Eloquent\ModelNotFoundException;
                }

                return $product;
            },
            ['products']
        );
    }

    /**
     * Get related products.
     *
     * @param  Product<\Database\Factories\ProductFactory>  $product
     * @return Collection<int, Product<\Database\Factories\ProductFactory>>
     */
    public function getRelatedProducts(Product $product, int $limit = 4): Collection
    {
        return $this->cache->remember(
            'product.related.'.$product->id,
            3600,
            fn (): \Illuminate\Database\Eloquent\Collection => $this->repository->getRelated($product, $limit),
            ['products']
        );
    }

    /**
     * Search products.
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Product>
     */
    public function searchProducts(string $query, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        // Don't cache search results as they're likely to be unique per user
        return $this->repository->search($query, $filters, $perPage);
    }

    /**
     * Update product price.
     *
     * @param  Product<\Database\Factories\ProductFactory>  $product
     */
    public function updatePrice(Product $product, float $newPrice): bool
    {
        try {
            $updated = $this->repository->updatePrice($product, $newPrice);

            if ($updated) {
                // Clear product-related caches
                $this->cache->forgetByTags(['products']);

                Log::info('Product price updated', [
                    'product_id' => $product->id,
                    'old_price' => $product->getOriginal('price'),
                    'new_price' => $newPrice,
                ]);
            }

            return $updated;
        } catch (\Exception $e) {
            Log::error('Failed to update product price', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
