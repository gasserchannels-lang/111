<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Store;
use App\Services\PriceSearchService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class PriceSearchController extends Controller
{
    public function __construct(private readonly PriceSearchService $priceSearchService)
    {
        // PriceSearchService is properly injected via constructor
    }

    public function bestOffer(Request $request): JsonResponse
    {
        try {
            // Support both product_id and product_name parameters
            $productId = $request->input('product_id');
            $productName = $request->input('product_name');

            if (empty($productId) && empty($productName)) {
                // If no parameters, return all products as a list
                $products = Product::with([
                    'priceOffers' => function ($query) {
                        $query->where('is_available', true)
                            ->orderBy('price', 'asc')
                            ->with('store:id,name');
                    },
                    'brand:id,name',
                    'category:id,name',
                ])->where('is_active', true)->limit(10)->get();

                if ($products->isEmpty()) {
                    return response()->json([
                        'message' => 'No products available',
                    ], 404);
                }

                return response()->json([
                    'data' => $products->map(function ($product) {
                        $bestOffer = $product->priceOffers->first();

                        return [
                            'product_id' => $product->id,
                            'name' => $product->name,
                            'price' => $bestOffer ? $bestOffer->price : $product->price,
                            'store' => $bestOffer && $bestOffer->store ? $bestOffer->store->name : 'Unknown Store',
                            'is_available' => $bestOffer ? $bestOffer->is_available : true,
                        ];
                    })->toArray(),
                ]);
            }

            // Find product by ID or name
            $product = null;
            if ($productId) {
                $product = Product::with([
                    'priceOffers' => function ($query) {
                        $query->where('is_available', true)
                            ->orderBy('price', 'asc')
                            ->with('store:id,name');
                    },
                    'brand:id,name',
                    'category:id,name',
                ])->find($productId);
            } elseif ($productName) {
                $product = Product::with([
                    'priceOffers' => function ($query) {
                        $query->where('is_available', true)
                            ->orderBy('price', 'asc')
                            ->with('store:id,name');
                    },
                    'brand:id,name',
                    'category:id,name',
                ])->where('name', 'like', '%' . $productName . '%')->first();
            }

            if (! $product) {
                return response()->json([
                    'message' => 'Product not found',
                ], 404);
            }

            if ($product->priceOffers->isEmpty()) {
                return response()->json([
                    'message' => 'No offers available for this product',
                ], 404);
            }

            $bestOffer = $product->priceOffers->first();

            return response()->json([
                'data' => [
                    'product_id' => $product->id,
                    'price' => $bestOffer->price,
                    'store_id' => $bestOffer->store_id,
                    'store' => $bestOffer->store ? $bestOffer->store->name : 'Unknown Store',
                    'store_url' => $bestOffer->store_url,
                    'is_available' => $bestOffer->is_available,
                    'total_offers' => $product->priceOffers->count(),
                ],
                'offers' => $product->priceOffers->map(function ($offer) {
                    return [
                        'id' => $offer->id,
                        'price' => $offer->price,
                        'store_id' => $offer->store_id,
                        'store' => $offer->store ? $offer->store->name : 'Unknown Store',
                        'store_url' => $offer->store_url,
                        'is_available' => $offer->is_available,
                    ];
                })->toArray(),
            ]);
        } catch (\Exception $e) {
            Log::error('PriceSearchController@bestOffer failed: ' . $e->getMessage());

            return response()->json([
                'message' => 'An error occurred while finding the best offer',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function supportedStores(Request $request): JsonResponse
    {
        try {
            $countryCode = $this->getCountryCode($request);
            $stores = Store::where('country_code', $countryCode)
                ->where('is_active', true)
                ->get();

            return response()->json($stores);
        } catch (Throwable $e) {
            Log::error('PriceSearchController@supportedStores failed: ' . $e->getMessage());

            return response()->json(['message' => 'An unexpected error occurred.'], 500);
        }
    }

    public function search(Request $request): JsonResponse
    {
        try {
            // Support 'q', 'query', and 'name' parameters for backward compatibility
            $query = $request->input('q', $request->input('query', $request->input('name', '')));

            if (empty($query)) {
                return response()->json([
                    'products' => [],
                    'message' => 'Search query is required. Use parameter: q, query, or name',
                ], 400);
            }

            $queryStr = is_string($query) ? $query : '';

            // Use caching for better performance
            $cacheKey = 'price_search_' . md5($queryStr);
            $results = \Illuminate\Support\Facades\Cache::remember($cacheKey, 60, function () use ($queryStr) {
                // Optimize query with proper indexing and limit
                $products = Product::select(['id', 'name', 'description', 'slug', 'price', 'brand_id', 'category_id'])
                    ->where('is_active', true)
                    ->where(function ($q) use ($queryStr) {
                        $q->where('name', 'like', '%' . $queryStr . '%')
                            ->orWhere('description', 'like', '%' . $queryStr . '%');
                    })
                    ->with([
                        'brand:id,name',
                        'category:id,name',
                        'priceOffers' => function ($query) {
                            $query->select(['id', 'product_id', 'price', 'store_id', 'is_available', 'store_url'])
                                ->with('store:id,name')
                                ->where('is_available', true)
                                ->orderBy('price', 'asc')
                                ->limit(3); // Limit price offers per product
                        },
                    ])
                    ->limit(5) // Further reduce limit for better performance
                    ->get();

                return $products->map(fn(Product $product): array => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'slug' => $product->slug,
                    'price' => $product->price,
                    'brand' => $product->brand ? $product->brand->name : null,
                    'category' => $product->category ? $product->category->name : null,
                    'prices' => $product->priceOffers->map(fn(\App\Models\PriceOffer $offer): array => [
                        'id' => $offer->id,
                        'price' => $offer->price,
                        'url' => $offer->store_url ?? null,
                        'store' => $offer->store ? $offer->store->name : null,
                        'is_available' => $offer->is_available,
                    ])->values(),
                ]);
            });

            return response()->json([
                'data' => $results,
                'results' => $results,
                'products' => $results,
                'total' => $results->count(),
                'query' => $query,
            ]);
        } catch (Throwable $e) {
            Log::error('PriceSearchController@search failed: ' . $e->getMessage());

            return response()->json(['message' => 'An unexpected error occurred.'], 500);
        }
    }

    private function getCountryCode(Request $request): string
    {
        $countryInput = $request->input('country');
        if ($request->has('country') && is_string($countryInput) && strlen($countryInput) === 2) {
            return strtoupper($countryInput);
        }

        $cfCountry = $request->header('CF-IPCountry');
        if (is_string($cfCountry)) {
            return strtoupper($cfCountry);
        }

        try {
            $response = Http::timeout(2)->get('https://ipapi.co/country');
            $body = $response->body();
            if ($response->successful() && strlen(trim($body)) === 2) {
                return strtoupper(trim($body));
            }
        } catch (Exception) {
            // Fallback to US
        }

        return 'US';
    }
}
