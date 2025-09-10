<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\PriceSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PriceSearchController extends Controller
{
    private PriceSearchService $priceSearchService;

    public function __construct(PriceSearchService $priceSearchService)
    {
        $this->priceSearchService = $priceSearchService;
    }

    public function bestOffer(Request $request): JsonResponse
    {
        $validation = $this->priceSearchService->validateSearchRequest($request);

        if (!$validation['success']) {
            return response()->json([
                'errors' => $validation['errors'],
            ], 422);
        }

        $validated = $validation['data'];
        $result = $this->priceSearchService->findBestOffer(
            $validated['product'],
            $validated['country']
        );

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
            ], 404);
        }

        return response()->json($result['data']);
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
            $this->log->error('PriceSearchController@supportedStores failed: ' . $e->getMessage());

            return response()->json(['message' => 'An unexpected error occurred.'], 500);
        }
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $query = $request->input('q', '');

            if (empty($query)) {
                return response()->json([
                    'data' => [],
                    'message' => 'Search query is required',
                ], 400);
            }

            $products = Product::where('name', 'like', '%' . $query . '%')
                ->orWhere('description', 'like', '%' . $query . '%')
                ->with(['priceOffers.store', 'brand', 'category'])
                ->limit(20)
                ->get();

            $results = $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'slug' => $product->slug,
                    'brand' => $product->brand ? $product->brand->name : null,
                    'category' => $product->category ? $product->category->name : null,
                    'price_offers' => $product->priceOffers->map(function ($offer) {
                        return [
                            'id' => $offer->id,
                            'price' => $offer->price,
                            'url' => $offer->url,
                            'store' => $offer->store ? $offer->store->name : null,
                            'is_available' => $offer->is_available,
                        ];
                    }),
                ];
            });

            return response()->json([
                'data' => $results,
                'total' => $results->count(),
                'query' => $query,
            ]);
        } catch (Throwable $e) {
            $this->log->error('PriceSearchController@search failed: ' . $e->getMessage());

            return response()->json(['message' => 'An unexpected error occurred.'], 500);
        }
    }

    private function getCountryCode(Request $request): string
    {
        if ($request->has('country') && strlen((string)$request->input('country')) === 2) {
            return strtoupper((string)$request->input('country'));
        }

        if ($request->header('CF-IPCountry')) {
            return strtoupper($request->header('CF-IPCountry'));
        }

        try {
            $response = Http::timeout(2)->get('https://ipapi.co/country');
            if ($response->successful() && strlen(trim($response->body())) === 2) {
                return strtoupper(trim($response->body()));
            }
        } catch (Exception $e) {
            // Fallback to US
        }

        return 'US';
    }
}
