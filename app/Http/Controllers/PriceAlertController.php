<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PriceAlert;
use App\Models\Product;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

class PriceAlertController extends Controller
{
    private const UNAUTHORIZED_MESSAGE = 'Unauthorized action.';

    public function index(Guard $auth): \Illuminate\View\View
    {
        $priceAlerts = $auth->user()->priceAlerts()->with('product')->latest()->paginate(10);

        return view('price-alerts.index', ['priceAlerts' => $priceAlerts]);
    }

    public function create(Request $request): \Illuminate\View\View
    {
        $product = null;
        if ($request->has('product_id')) {
            $product = app(Product::class)->findOrFail($request->product_id);
        }

        return view('price-alerts.create', ['product' => $product]);
    }

    public function store(Request $request, Guard $auth): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'target_price' => 'required|numeric|min:0.01',
            'repeat_alert' => 'nullable|boolean',
        ]);

        $auth->user()->priceAlerts()->create([
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
     */
    public function show(PriceAlert $priceAlert, Guard $auth): \Illuminate\View\View
    {
        // التحقق من أن المستخدم هو صاحب التنبيه
        if ($priceAlert->user_id !== $auth->id()) {
            abort(403, self::UNAUTHORIZED_MESSAGE);
        }

        $priceAlert->load(['product', 'product.priceOffers' => function ($query): void {
            $query->where('in_stock', true)->orderBy('price', 'asc')->limit(5);
        }]);

        return view('price-alerts.show', ['priceAlert' => $priceAlert]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PriceAlert $priceAlert, Guard $auth): \Illuminate\View\View
    {
        if ($priceAlert->user_id !== $auth->id()) {
            abort(403, self::UNAUTHORIZED_MESSAGE);
        }

        return view('price-alerts.edit', ['priceAlert' => $priceAlert]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PriceAlert $priceAlert, Guard $auth): \Illuminate\Http\RedirectResponse
    {
        if ($priceAlert->user_id !== $auth->id()) {
            abort(403, self::UNAUTHORIZED_MESSAGE);
        }

        $request->validate([
            'target_price' => 'required|numeric|min:0.01',
            'repeat_alert' => 'nullable|boolean',
        ]);

        $priceAlert->update([
            'target_price' => $request->target_price,
            'repeat_alert' => $request->boolean('repeat_alert'),
        ]);

        return redirect()->route('price-alerts.index')
            ->with('success', 'Price alert updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PriceAlert $priceAlert, Guard $auth): \Illuminate\Http\RedirectResponse
    {
        if ($priceAlert->user_id !== $auth->id()) {
            abort(403, self::UNAUTHORIZED_MESSAGE);
        }

        $priceAlert->delete();

        return redirect()->route('price-alerts.index')
            ->with('success', 'Price alert deleted successfully!');
    }

    /**
     * Toggle alert status
     */
    public function toggle(PriceAlert $priceAlert, Guard $auth): \Illuminate\Http\RedirectResponse
    {
        if ($priceAlert->user_id !== $auth->id()) {
            abort(403, self::UNAUTHORIZED_MESSAGE);
        }

        $priceAlert->update([
            'is_active' => ! $priceAlert->is_active,
        ]);

        return back()->with('success', 'Alert status updated!');
    }
}
