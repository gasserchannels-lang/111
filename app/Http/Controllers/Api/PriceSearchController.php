<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PriceOffer;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PriceSearchController extends Controller
{
    private const VALIDATION_RULE_COUNTRY = 'sometimes|string|size:2';

    /**
     * محاولة تحديد دولة المستخدم من خلال IP أو الهيدر.
     */
    private function detectUserCountry(Request $request): string
    {
        $country = $request->header('CF-IPCountry');

        if ($country && $country !== 'XX') {
            return $country;
        }

        $ip = $request->ip();
        if (app()->environment('local') && in_array($ip, ['127.0.0.1', '::1'])) {
            return 'US';
        }

        try {
            $response = Http::get("https://ipapi.co/{$ip}/country_code/");
            if ($response->successful() && ! empty(trim($response->body()))) {
                $country = trim($response->body());
            }
        } catch (\Exception $e) {
            Log::warning("Could not detect country for IP {$ip}: ".$e->getMessage());
        }

        return $country ?? 'US';
    }

    /**
     * الحصول على أفضل عرض لمنتج معين.
     */
    public function bestOffer(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product' => 'required|string|min:2|max:255',
            'country' => self::VALIDATION_RULE_COUNTRY,
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $productName = $request->input('product');
            $country = $request->input('country', $this->detectUserCountry($request));

            $bestOffer = PriceOffer::whereHas('product', function ($query) use ($productName) {
                $query->where('name', 'like', "%{$productName}%");
            })->whereHas('store', function ($query) use ($country) {
                $query->where('country_code', $country);
            })->with(['product', 'store.currency'])->orderBy('price', 'asc')->first();

            if ($bestOffer) {
                return response()->json($bestOffer);
            }

            return response()->json([
                'message' => 'No offers found for this product in the specified country.',
            ], 404);

        } catch (\Exception $e) {
            Log::error('Best offer search failed: '.$e->getMessage());
        }

        return response()->json(['message' => 'An error occurred.'], 500);
    }

    /**
     * الحصول على قائمة المتاجر المدعومة لدولة معينة.
     */
    public function supportedStores(Request $request): JsonResponse
    {
        $country = $request->input('country', $this->detectUserCountry($request));
        $stores = Store::where('country_code', $country)->where('is_active', true)->get();

        return response()->json($stores);
    }
}
