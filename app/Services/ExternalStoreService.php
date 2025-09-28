<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExternalStoreService
{
    private array $storeConfigs;

    public function __construct()
    {
        $this->storeConfigs = [
            'amazon' => [
                'api_url' => 'https://api.amazon.com/products',
                'api_key' => env('AMAZON_API_KEY'),
                'rate_limit' => 100, // requests per minute
            ],
            'ebay' => [
                'api_url' => 'https://api.ebay.com/buy/browse/v1',
                'api_key' => env('EBAY_API_KEY'),
                'rate_limit' => 5000, // requests per day
            ],
            'aliexpress' => [
                'api_url' => 'https://api.aliexpress.com/products',
                'api_key' => env('ALIEXPRESS_API_KEY'),
                'rate_limit' => 200, // requests per minute
            ],
        ];
    }

    public function searchProducts(string $query, array $filters = []): array
    {
        $results = [];

        foreach ($this->storeConfigs as $storeName => $config) {
            try {
                $storeResults = $this->searchInStore($storeName, $query, $filters);
                $results = array_merge($results, $storeResults);
            } catch (\Exception $e) {
                Log::error("Failed to search in {$storeName}", [
                    'query' => $query,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $this->sortAndFilterResults($results, $filters);
    }

    public function getProductDetails(string $storeName, string $productId): ?array
    {
        $cacheKey = "external_product_{$storeName}_{$productId}";

        return Cache::remember($cacheKey, 3600, function () use ($storeName, $productId) {
            $config = $this->storeConfigs[$storeName] ?? null;

            if (! $config) {
                return null;
            }

            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $config['api_key'],
                    'Accept' => 'application/json',
                ])->timeout(10)->get($config['api_url'] . "/{$productId}");

                if ($response->successful()) {
                    return $this->normalizeProductData($response->json(), $storeName);
                }

                return null;
            } catch (\Exception $e) {
                Log::error("Failed to get product details from {$storeName}", [
                    'product_id' => $productId,
                    'error' => $e->getMessage(),
                ]);

                return null;
            }
        });
    }

    public function syncStoreProducts(string $storeName): int
    {
        $config = $this->storeConfigs[$storeName] ?? null;

        if (! $config) {
            return 0;
        }

        $syncedCount = 0;
        $page = 1;
        $hasMore = true;

        while ($hasMore) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $config['api_key'],
                    'Accept' => 'application/json',
                ])->timeout(30)->get($config['api_url'], [
                    'page' => $page,
                    'limit' => 100,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $products = $data['products'] ?? [];

                    foreach ($products as $productData) {
                        $this->syncProduct($productData, $storeName);
                        $syncedCount++;
                    }

                    $hasMore = $data['has_more'] ?? false;
                    $page++;
                } else {
                    $hasMore = false;
                }
            } catch (\Exception $e) {
                Log::error("Failed to sync products from {$storeName}", [
                    'page' => $page,
                    'error' => $e->getMessage(),
                ]);
                $hasMore = false;
            }
        }

        return $syncedCount;
    }

    private function searchInStore(string $storeName, string $query, array $filters): array
    {
        $config = $this->storeConfigs[$storeName];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $config['api_key'],
            'Accept' => 'application/json',
        ])->timeout(10)->get($config['api_url'] . '/search', [
            'q' => $query,
            'limit' => 20,
            'filters' => $filters,
        ]);

        if (! $response->successful()) {
            throw new \Exception('API request failed: ' . $response->body());
        }

        $data = $response->json();
        $products = $data['products'] ?? [];

        return array_map(function ($product) use ($storeName) {
            return $this->normalizeProductData($product, $storeName);
        }, $products);
    }

    private function normalizeProductData(array $productData, string $storeName): array
    {
        return [
            'external_id' => $productData['id'] ?? null,
            'name' => $productData['title'] ?? $productData['name'] ?? '',
            'description' => $productData['description'] ?? '',
            'price' => $productData['price'] ?? 0,
            'currency' => $productData['currency'] ?? 'USD',
            'image_url' => $productData['image'] ?? $productData['thumbnail'] ?? '',
            'store_name' => $storeName,
            'store_url' => $productData['url'] ?? '',
            'rating' => $productData['rating'] ?? 0,
            'reviews_count' => $productData['reviews_count'] ?? 0,
            'availability' => $productData['availability'] ?? 'in_stock',
            'shipping_info' => $productData['shipping'] ?? [],
            'category' => $productData['category'] ?? '',
            'brand' => $productData['brand'] ?? '',
        ];
    }

    private function sortAndFilterResults(array $results, array $filters): array
    {
        // Sort by price if specified
        if (isset($filters['sort_by']) && $filters['sort_by'] === 'price') {
            usort($results, function ($a, $b) {
                return $a['price'] <=> $b['price'];
            });
        }

        // Filter by price range
        if (isset($filters['min_price'])) {
            $results = array_filter($results, function ($product) use ($filters) {
                return $product['price'] >= $filters['min_price'];
            });
        }

        if (isset($filters['max_price'])) {
            $results = array_filter($results, function ($product) use ($filters) {
                return $product['price'] <= $filters['max_price'];
            });
        }

        return array_values($results);
    }

    private function syncProduct(array $productData, string $storeName): void
    {
        $normalizedData = $this->normalizeProductData($productData, $storeName);

        // Find or create store
        $store = Store::firstOrCreate(
            ['name' => $storeName],
            ['is_active' => true, 'api_config' => $this->storeConfigs[$storeName]]
        );

        // Create or update product
        Product::updateOrCreate(
            [
                'external_id' => $normalizedData['external_id'],
                'store_id' => $store->id,
            ],
            [
                'name' => $normalizedData['name'],
                'description' => $normalizedData['description'],
                'price' => $normalizedData['price'],
                'currency' => $normalizedData['currency'],
                'image' => $normalizedData['image_url'],
                'rating' => $normalizedData['rating'],
                'reviews_count' => $normalizedData['reviews_count'],
                'is_active' => true,
                'external_data' => $normalizedData,
            ]
        );
    }

    public function getStoreStatus(): array
    {
        $status = [];

        foreach ($this->storeConfigs as $storeName => $config) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $config['api_key'],
                ])->timeout(5)->get($config['api_url'] . '/health');

                $status[$storeName] = [
                    'status' => $response->successful() ? 'online' : 'offline',
                    'response_time' => $response->transferStats?->getHandlerStat('total_time') ?? 0,
                    'last_check' => now()->toISOString(),
                ];
            } catch (\Exception $e) {
                $status[$storeName] = [
                    'status' => 'error',
                    'error' => $e->getMessage(),
                    'last_check' => now()->toISOString(),
                ];
            }
        }

        return $status;
    }
}
