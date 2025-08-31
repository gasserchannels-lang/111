<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PriceAlert;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PriceAlertController extends Controller
{
    private const UNAUTHORIZED_MESSAGE = 'Unauthorized action.';

    public function index()
    {
        $priceAlerts = Auth::user()->priceAlerts()->with('product')->latest()->paginate(10);

        return view('price-alerts.index', compact('priceAlerts'));
    }

    public function create(Request $request)
    {
        $product = null;
        if ($request->has('product_id')) {
            $product = Product::findOrFail($request->product_id);
        }

        return view('price-alerts.create', compact('product'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'target_price' => 'required|numeric|min:0.01',
            'repeat_alert' => 'nullable|boolean',
        ]);

        Auth::user()->priceAlerts()->create([
            'product_id' => $request->product_id,
            'target_price' => $request->target_price,
            'repeat_alert' => $request->boolean('repeat_alert'),
            'is_active' => true,
        ]);

        return redirect()->route('price-alerts.index')
            ->with('success', 'Price alert created successfully!');
    }

    /**
     * Display the specified resource.
     *
     * ✅✅✅ التعديل الرئيسي هنا ✅✅✅
     * تم تغيير اسم المتغير من $priceAlert إلى $price_alert ليتطابق مع Route Model Binding.
     */
    public function show(PriceAlert $price_alert)
    {
        // التحقق من أن المستخدم هو صاحب التنبيه
        if ($price_alert->user_id !== Auth::id()) {
            abort(403, self::UNAUTHORIZED_MESSAGE);
        }

        $price_alert->load(['product', 'product.priceOffers' => function ($query) {
            $query->where('in_stock', true)->orderBy('price', 'asc')->limit(5);
        }]);

        // تم تمرير المتغير إلى الواجهة باسم "priceAlert" للحفاظ على التوافق مع ملف الـ view.
        return view('price-alerts.show', ['priceAlert' => $price_alert]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * ✅✅✅ التعديل الرئيسي هنا أيضًا ✅✅✅
     */
    public function edit(PriceAlert $price_alert)
    {
        if ($price_alert->user_id !== Auth::id()) {
            abort(403, self::UNAUTHORIZED_MESSAGE);
        }

        // تم تمرير المتغير إلى الواجهة باسم "priceAlert" للحفاظ على التوافق.
        return view('price-alerts.edit', ['priceAlert' => $price_alert]);
    }

    /**
     * Update the specified resource in storage.
     *
     * ✅✅✅ التعديل الرئيسي هنا أيضًا ✅✅✅
     */
    public function update(Request $request, PriceAlert $price_alert)
    {
        if ($price_alert->user_id !== Auth::id()) {
            abort(403, self::UNAUTHORIZED_MESSAGE);
        }

        $request->validate([
            'target_price' => 'required|numeric|min:0.01',
            'repeat_alert' => 'nullable|boolean',
        ]);

        $price_alert->update([
            'target_price' => $request->target_price,
            'repeat_alert' => $request->boolean('repeat_alert'),
        ]);

        return redirect()->route('price-alerts.index')
            ->with('success', 'Price alert updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * ✅✅✅ التعديل الرئيسي هنا أيضًا ✅✅✅
     */
    public function destroy(PriceAlert $price_alert)
    {
        if ($price_alert->user_id !== Auth::id()) {
            abort(403, self::UNAUTHORIZED_MESSAGE);
        }

        $price_alert->delete();

        return redirect()->route('price-alerts.index')
            ->with('success', 'Price alert deleted successfully!');
    }

    /**
     * Toggle alert status
     *
     * ✅✅✅ التعديل الرئيسي هنا أيضًا ✅✅✅
     */
    public function toggle(PriceAlert $price_alert)
    {
        if ($price_alert->user_id !== Auth::id()) {
            abort(403, self::UNAUTHORIZED_MESSAGE);
        }

        $price_alert->update([
            'is_active' => ! $price_alert->is_active,
        ]);

        return back()->with('success', 'Alert status updated!');
    }
}
