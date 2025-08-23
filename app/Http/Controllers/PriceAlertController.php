<?php

namespace App\Http\Controllers;

use App\Models\PriceAlert;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PriceAlertController extends Controller
{
    // تم إضافة هذا الثابت لتقليل التكرار
    private const UNAUTHORIZED_MESSAGE = 'Unauthorized action.';

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $priceAlerts = Auth::user()->priceAlerts()->with('product')->latest()->paginate(10);

        return view('price-alerts.index', compact('priceAlerts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $product = null;
        if ($request->has('product_id')) {
            $product = Product::findOrFail($request->product_id);
        }

        return view('price-alerts.create', compact('product'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'target_price' => 'required|numeric|min:0.01',
            'repeat_alert' => 'boolean',
        ]);

        Auth::user()->priceAlerts()->create([
            'product_id' => $request->product_id,
            'target_price' => $request->target_price,
            'repeat_alert' => $request->input('repeat_alert', false),
            'is_active' => true,
        ]);

        return redirect()->route('price-alerts.index')
            ->with('success', 'Price alert created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(PriceAlert $priceAlert)
    {
        // التحقق من أن المستخدم هو صاحب التنبيه
        if ($priceAlert->user_id !== Auth::id()) {
            abort(403, self::UNAUTHORIZED_MESSAGE); // تم استخدام الثابت هنا
        }

        $priceAlert->load(['product', 'product.priceOffers' => function ($query) {
            $query->where('in_stock', true)->orderBy('price', 'asc')->limit(5);
        }]);

        return view('price-alerts.show', compact('priceAlert'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PriceAlert $priceAlert)
    {
        // التحقق من أن المستخدم هو صاحب التنبيه
        if ($priceAlert->user_id !== Auth::id()) {
            abort(403, self::UNAUTHORIZED_MESSAGE); // تم استخدام الثابت هنا
        }

        return view('price-alerts.edit', compact('priceAlert'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PriceAlert $priceAlert)
    {
        // التحقق من أن المستخدم هو صاحب التنبيه
        if ($priceAlert->user_id !== Auth::id()) {
            abort(403, self::UNAUTHORIZED_MESSAGE); // تم استخدام الثابت هنا
        }

        $request->validate([
            'target_price' => 'required|numeric|min:0.01',
            'repeat_alert' => 'boolean',
        ]);

        $priceAlert->update([
            'target_price' => $request->target_price,
            'repeat_alert' => $request->input('repeat_alert', false),
        ]);

        return redirect()->route('price-alerts.index')
            ->with('success', 'Price alert updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PriceAlert $priceAlert)
    {
        // التحقق من أن المستخدم هو صاحب التنبيه
        if ($priceAlert->user_id !== Auth::id()) {
            abort(403, self::UNAUTHORIZED_MESSAGE); // تم استخدام الثابت هنا
        }

        $priceAlert->delete();

        return redirect()->route('price-alerts.index')
            ->with('success', 'Price alert deleted successfully!');
    }

    /**
     * Toggle alert status
     */
    public function toggle(PriceAlert $priceAlert)
    {
        // التحقق من أن المستخدم هو صاحب التنبيه
        if ($priceAlert->user_id !== Auth::id()) {
            abort(403, self::UNAUTHORIZED_MESSAGE); // تم استخدام الثابت هنا
        }

        $priceAlert->update([
            'is_active' => ! $priceAlert->is_active,
        ]);

        return back()->with('success', 'Alert status updated!');
    }
}
