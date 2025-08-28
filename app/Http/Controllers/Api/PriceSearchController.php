<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PriceOffer;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PriceSearchController extends Controller
{
    public function bestOffer(Request $request)
    {
        $validated = $request->validate([
            'product' => 'required|string|min:2|max:255',
            'country' => 'required|string|size:2',
        ]);

        $productName = $validated['product'];
        $countryCode = strtoupper($validated['country']);

        $bestOffer = PriceOffer::with(['product', 'store.currency'])
            ->whereHas('product', fn ($q) => $q->where('name', $productName))
            ->whereHas('store', fn ($q) => $q->where('country_code', $countryCode)->where('is_active', true))
            ->orderBy('price', 'asc')
            ->first();

        if (! $bestOffer) {
            return response()->json(['message' => 'No offers found for this product in the specified country.'], 404);
        }

        return response()->json($bestOffer);
    }

    public function supportedStores(Request $request)
    {
        $countryCode = $this->getCountryCode($request);

        $stores = Store::where('country_code', $countryCode)
            ->where('is_active', true)
            ->get();

        return response()->json($stores);
    }

    private function getCountryCode(Request $request): string
    {
        if ($request->has('country') && strlen($request->input('country')) === 2) {
            return strtoupper($request->input('country'));
        }

        if ($request->header('CF-IPCountry')) {
            return strtoupper($request->header('CF-IPCountry'));
        }

        try {
            $response = Http::get('https://ipapi.co/country' );
            if ($response->successful() && strlen(trim($response->body())) === 2) {
                return strtoupper(trim($response->body()));
            }
        } catch (\Exception $e) {
            // Fallback to US
        }

        return 'US';
    }
}
