<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PriceOffer;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PriceSearchController extends Controller
{
    public function bestOffer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product' => 'required|string|min:2|max:255',
            'country' => 'required|string|size:2',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $productName = $request->input('product');
            $countryCode = $request->input('country');

            // ✅ *** هذا هو الجزء الذي تم إصلاحه ***
            // البحث عن أفضل عرض مباشرة باستخدام Eloquent
            $bestOffer = PriceOffer::with(['product', 'store.currency'])
                ->whereHas('product', fn ($q) => $q->where('name', $productName))
                ->whereHas('store', fn ($q) => $q->where('country_code', $countryCode)->where('is_active', true))
                ->orderBy('price', 'asc')
                ->first(); // <-- استخدام first() للحصول على أفضل عرض واحد فقط

            if (! $bestOffer) {
                return response()->json(['message' => 'No offers found for this product in the specified country.'], 404);
            }

            return response()->json($bestOffer);

        } catch (\Exception $e) {
            Log::error('Best offer search failed: '.$e->getMessage());

            return response()->json(['message' => 'An unexpected error occurred.'], 404);
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
