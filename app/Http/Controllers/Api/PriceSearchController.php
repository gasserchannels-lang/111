<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PriceOffer;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PriceSearchController extends Controller
{
    private const VALIDATION_RULE_COUNTRY = 'nullable|string|size:2';

    /**
     * البحث عن أسعار المنتجات بناءً على استعلام.
     */
    public function search(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2|max:255',
            'country' => self::VALIDATION_RULE_COUNTRY,
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $query = $request->input('query');
        $country = $request->input('country', $this->detectUserCountry($request));
        $limit = $request->input('limit', 20);

        try {
            $products = Product::where('name', 'like', "%{$query}%")
                ->with(['priceOffers' => function ($query) use ($country) {
                    $query->whereHas('store', function ($q) use ($country) {
                        $q->where('country_code', $country);
                    })->orderBy('price', 'asc');
                }])
                ->take($limit)
                ->get();

            return response()->json($products);
        } catch (\Exception $e) {
            Log::error('Price search failed: '.$e->getMessage());

            return response()->json(['message' => 'An error occurred during the search.'], 500);
        }
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
            })->orderBy('price', 'asc')->first();

            // تم حل التعارض هنا
            if ($bestOffer) {
                return response()->json($bestOffer);
            }

            return response()->json([
                'message' => 'No offers found for this product in the specified country.',
            ], 404);

        } catch (\Exception $e) {
            Log::error('Best offer search failed: '.$e->getMessage());

            return response()->json(['message' => 'An error occurred.'], 500);
        }
    }

    /**
     * الحصول على قائمة المتاجر المدعومة لدولة معينة
     */
    public function supportedStores(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'country' => self::VALIDATION_RULE_COUNTRY,
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $country = $request->input('country', $this->detectUserCountry($request));

        try {
            $stores = Store::where('country_code', $country)->get();

            return response()->json($stores);
        } catch (\Exception $e) {
            Log::error('Failed to fetch supported stores: '.$e->getMessage());

            return response()->json(['message' => 'Could not retrieve supported stores.'], 500);
        }
    }

    /**
     * محاولة تحديد دولة المستخدم من خلال IP.
     */
    private function detectUserCountry(Request $request): string
    {
        $ip = $request->ip();
        // في بيئة الإنتاج، استخدم خدمة GeoIP حقيقية
        if (app()->environment('local') && $ip === '127.0.0.1') {
            return 'US'; // قيمة افتراضية للتطوير المحلي
        }

        try {
            $response = Http::get("https://ipapi.co/{$ip}/country_code/");
            if ($response->successful() && ! empty($response->body())) {
                return trim($response->body());
            }
        } catch (\Exception $e) {
            Log::warning("Could not detect country for IP {$ip}: ".$e->getMessage());
        }

        return 'US'; // قيمة افتراضية عالمية
    }
}
