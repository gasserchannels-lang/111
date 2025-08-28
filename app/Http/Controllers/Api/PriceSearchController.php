<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PriceOffer;
use Illuminate\Http\Request;

class PriceSearchController extends Controller
{
    public function bestOffer(Request $request)
    {
        // الخطوة 1: التحقق من المدخلات.
        // إذا فشل، سيرمي ValidationException وسيعالجه الـ Handler ليرجع 422.
        $validated = $request->validate([
            'product' => 'required|string|min:2|max:255',
            'country' => 'required|string|size:2',
        ]);

        // الخطوة 2: تنفيذ منطق العمل.
        // أي خطأ هنا (مثل خطأ قاعدة البيانات) سيلتقطه الـ Handler ويرجع 500.
        $productName = $validated['product'];
        $countryCode = strtoupper($validated['country']);

        $bestOffer = PriceOffer::with(['product', 'store.currency'])
            ->whereHas('product', fn ($q) => $q->where('name', $productName))
            ->whereHas('store', fn ($q) => $q->where('country_code', $countryCode)->where('is_active', true))
            ->orderBy('price', 'asc')
            ->first();

        // الخطوة 3: إرجاع الاستجابة الصحيحة.
        if (! $bestOffer) {
            return response()->json(['message' => 'No offers found for this product in the specified country.'], 404);
        }

        return response()->json($bestOffer);
    }
}
