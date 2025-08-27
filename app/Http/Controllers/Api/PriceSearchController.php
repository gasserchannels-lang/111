<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PriceOffer;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PriceSearchController extends Controller
{
    public function bestOffer(Request $request)
    {
        $validated = $request->validate([
            'product' => 'required|string|min:2|max:255',
            'country' => 'required|string|size:2',
        ]);

        try {
            $productName = $validated['product'];
            $countryCode = strtoupper($validated['country']);

            // ✅ *** هذا هو الجزء الذي تم إصلاحه باستخدام LIKE ***
            // البحث عن أفضل عرض للمنتج مع مرونة أكبر في الاسم
            $bestOffer = PriceOffer::with(['product', 'store.currency'])
                ->whereHas('product', function ($q) use ($productName) {
                    $q->where('name', 'like', '%' . $productName . '%');
                })
                ->whereHas('store', function ($q) use ($countryCode) {
                    $q->where('country_code', $countryCode)->where('is_active', true);
                })
                ->orderBy('price', 'asc')
                ->first();

            if (! $bestOffer) {
                return response()->json(['message' => 'No offers found for this product in the specified country.'], 404);
            }

            return response()->json($bestOffer);

        } catch (\Exception $e) {
            Log::error('Best offer search failed: '.$e->getMessage());
            return response()->json(['message' => 'An unexpected error occurred.'], 500);
        }
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
            if ($response->successful() && strlen($response->body()) === 2) {
                return strtoupper($response->body());
            }
        } catch (\Exception $e) {
            Log::warning('Could not fetch country from ipapi.co: '.$e->getMessage());
        }

        return 'US';
    }
}
