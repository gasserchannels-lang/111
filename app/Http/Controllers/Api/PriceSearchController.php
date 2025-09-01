<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PriceSearchController extends Controller
{
    public function bestOffer(Request $request )
    {
        try {
            $validator = Validator::make($request->all(), [
                'product' => 'required|string|min:3|max:255',
                'country' => 'required|string|size:2',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $productName = $request->input('product');
            $countryCode = $request->input('country');

            $product = Product::where('name', 'like', '%' . $productName . '%')->first();

            if (!$product) {
                return response()->json(['message' => 'Product not found.'], 404);
            }

            $cheapestOffer = $product->priceOffers()
                ->join('stores', 'price_offers.store_id', '=', 'stores.id')
                ->where('stores.country_code', $countryCode)
                ->orderBy('price', 'asc')
                ->select('price_offers.*', 'stores.name as store_name', 'stores.country_code')
                ->first();

            if (!$cheapestOffer) {
                return response()->json(['message' => 'No offers found for this product in the specified country.'], 404);
            }

            return response()->json($cheapestOffer);
        } catch (\Throwable $e) {
            Log::error("PriceSearchController@bestOffer failed: " . $e->getMessage());
            return response()->json(['message' => 'An unexpected error occurred.'], 500);
        }
    }

    public function supportedStores(Request $request)
    {
        try {
            $countryCode = $this->getCountryCode($request);
            $stores = Store::where('country_code', $countryCode)
                ->where('is_active', true)
                ->get();
            return response()->json($stores);
        } catch (\Throwable $e) {
            Log::error("PriceSearchController@supportedStores failed: " . $e->getMessage());
            return response()->json(['message' => 'An unexpected error occurred.'], 500);
        }
    }

    private function getCountryCode(Request $request): string
    {
        if ($request->has('country') && strlen((string) $request->input('country')) === 2) {
            return strtoupper((string) $request->input('country'));
        }
        if ($request->header('CF-IPCountry')) {
            return strtoupper($request->header('CF-IPCountry'));
        }
        try {
            $response = Http::timeout(2)->get('https://ipapi.co/country' );
            if ($response->successful() && strlen(trim($response->body())) === 2) {
                return strtoupper(trim($response->body()));
            }
        } catch (\Exception $e) {
            // Fallback to US
        }
        return 'US';
    }
}
