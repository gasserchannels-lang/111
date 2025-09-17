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
    }

    public function bestOffer(Request $request): JsonResponse
    {
        $validation = $this->priceSearchService->validateSearchRequest($request);

        if (! $validation['success']) {
            return response()->json([
                'errors' => $validation['errors'],
            ], 422);
        }

        $validated = $validation['data'];
        if (is_array($validated)) {
            $product = is_string($validated['product'] ?? null) ? $validated['product'] : '';
            $country = is_string($validated['country'] ?? null) ? $validated['country'] : '';
        } else {
            $product = '';
            $country = '';
        }

        $result = $this->priceSearchService->findBestOffer($product, $country);

        if (! $result['success']) {
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
            Log::error('PriceSearchController@supportedStores failed: '.$e->getMessage());

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

            $queryStr = is_string($query) ? $query : '';
            $products = Product::where('name', 'like', '%'.$queryStr.'%')
                ->orWhere('description', 'like', '%'.$queryStr.'%')
                ->with(['priceOffers.store', 'brand', 'category'])
                ->limit(20)
                ->get();

            $results = $products->map(fn(Product $product): array => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'slug' => $product->slug,
                'brand' => $product->brand ? $product->brand->name : null,
                'category' => $product->category ? $product->category->name : null,
                'price_offers' => $product->priceOffers->map(fn(\App\Models\PriceOffer $offer): array => [
                    'id' => $offer->id,
                    'price' => $offer->price,
                    'url' => $offer->store_url ?? null,
                    'store' => $offer->store ? $offer->store->name : null,
                    'is_available' => $offer->is_available,
                ])->values(),
            ]);

            return response()->json([
                'data' => $results,
                'total' => $results->count(),
                'query' => $query,
            ]);
        } catch (Throwable $e) {
            Log::error('PriceSearchController@search failed: '.$e->getMessage());

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
