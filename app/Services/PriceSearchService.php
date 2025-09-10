<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Http\Request;
use Illuminate\Log\LogManager;

class PriceSearchService
{
    private ValidationFactory $validationFactory;

    private LogManager $log;

    public function __construct(ValidationFactory $validationFactory, LogManager $log)
    {
        $this->validationFactory = $validationFactory;
        $this->log = $log;
    }

    /**
     * Find the best offer for a product in a specific country.
     */
    public function findBestOffer(string $productName, string $countryCode): array
    {
        $product = Product::where('name', 'like', '%' . $productName . '%')->first();

        if (!$product) {
            return [
                'success' => false,
                'message' => 'Product not found.',
                'data' => null,
            ];
        }

        $cheapestOffer = $product->priceOffers()
            ->join('stores', 'price_offers.store_id', '=', 'stores.id')
            ->where('stores.country_code', $countryCode)
            ->orderBy('price', 'asc')
            ->select('price_offers.*', 'stores.name as store_name', 'stores.country_code')
            ->first();

        if (!$cheapestOffer) {
            return [
                'success' => false,
                'message' => 'No offers found for this product in the specified country.',
                'data' => null,
            ];
        }

        return [
            'success' => true,
            'message' => 'Best offer found.',
            'data' => $cheapestOffer,
        ];
    }

    /**
     * Search for products with price comparison.
     */
    public function searchProducts(array $filters): array
    {
        $query = Product::with(['priceOffers.store', 'brand', 'category']);

        if (isset($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (isset($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['min_price'])) {
            $query->whereHas('priceOffers', function ($q) use ($filters) {
                $q->where('price', '>=', $filters['min_price']);
            });
        }

        if (isset($filters['max_price'])) {
            $query->whereHas('priceOffers', function ($q) use ($filters) {
                $q->where('price', '<=', $filters['max_price']);
            });
        }

        if (isset($filters['country_code'])) {
            $query->whereHas('priceOffers.store', function ($q) use ($filters) {
                $q->where('country_code', $filters['country_code']);
            });
        }

        $perPage = $filters['per_page'] ?? config('coprra.pagination.default_items_per_page', 20);
        $products = $query->paginate($perPage);

        return [
            'success' => true,
            'message' => 'Products found.',
            'data' => $products,
        ];
    }

    /**
     * Get price history for a product.
     */
    public function getPriceHistory(int $productId, int $days = 30): array
    {
        $product = Product::find($productId);

        if (!$product) {
            return [
                'success' => false,
                'message' => 'Product not found.',
                'data' => null,
            ];
        }

        $priceHistory = $product->priceOffers()
            ->with('store')
            ->where('created_at', '>=', now()->subDays($days))
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('store.name')
            ->map(function ($offers) {
                return $offers->map(function ($offer) {
                    return [
                        'price' => $offer->price,
                        'currency' => $offer->currency,
                        'date' => $offer->created_at->format('Y-m-d H:i:s'),
                        'in_stock' => $offer->in_stock,
                    ];
                });
            });

        return [
            'success' => true,
            'message' => 'Price history retrieved.',
            'data' => $priceHistory,
        ];
    }

    /**
     * Compare prices across multiple stores.
     */
    public function comparePrices(int $productId, array $storeIds = []): array
    {
        $product = Product::find($productId);

        if (!$product) {
            return [
                'success' => false,
                'message' => 'Product not found.',
                'data' => null,
            ];
        }

        $query = $product->priceOffers()->with('store');

        if (!empty($storeIds)) {
            $query->whereIn('store_id', $storeIds);
        }

        $offers = $query->orderBy('price', 'asc')->get();

        if ($offers->isEmpty()) {
            return [
                'success' => false,
                'message' => 'No price offers found.',
                'data' => null,
            ];
        }

        $comparison = $offers->map(function ($offer) {
            return [
                'store_name' => $offer->store->name,
                'price' => $offer->price,
                'currency' => $offer->currency,
                'in_stock' => $offer->in_stock,
                'product_url' => $offer->product_url,
                'affiliate_url' => $offer->affiliate_url,
                'rating' => $offer->rating,
                'reviews_count' => $offer->reviews_count,
            ];
        });

        return [
            'success' => true,
            'message' => 'Price comparison completed.',
            'data' => [
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'image' => $product->image,
                ],
                'offers' => $comparison,
                'lowest_price' => $offers->min('price'),
                'highest_price' => $offers->max('price'),
                'average_price' => $offers->avg('price'),
            ],
        ];
    }

    /**
     * Validate search request.
     */
    public function validateSearchRequest(Request $request): array
    {
        $validator = $this->validationFactory->make($request->all(), [
            'product' => 'required|string|min:3|max:255',
            'country' => 'required|string|size:2',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ];
        }

        return [
            'success' => true,
            'message' => 'Validation passed.',
            'data' => $validator->validated(),
        ];
    }
}
