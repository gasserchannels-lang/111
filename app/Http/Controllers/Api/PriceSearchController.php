<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PriceOffer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PriceSearchController extends Controller
{
    private const VALIDATION_RULE_COUNTRY = 'sometimes|string|size:2';

    /**
     * Detect user's country from request headers.
     */
    private function detectUserCountry(Request $request): string
    {
        // Logic to detect country from Cloudflare header or fallback
        return $request->header('CF-IPCountry', 'US');
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
    // ... باقي الكود في الملف ...
}
