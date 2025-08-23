<?php

namespace App\Http\Controllers;

use App\Models\PriceAlert;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PriceAlertController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $alerts = PriceAlert::where('user_id', Auth::id())
            ->with(['product'])
            ->latest()
            ->paginate(10);
            
        return view('price-alerts.index', compact('alerts'));
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

        // التحقق من عدم وجود تنبيه نشط للمنتج نفسه
        $existingAlert = PriceAlert::where('user_id', Auth::id())
            ->where('product_id', $request->product_id)
            ->where('is_active', true)
            ->first();

        if ($existingAlert) {
            return back()->withErrors(['product_id' => 'You already have an active alert for this product.']);
        }

        PriceAlert::create([
            'user_id' => Auth::id(),
            'product_id' => $request->product_id,
            'target_price' => $request->target_price,
            'repeat_alert' => $request->boolean('repeat_alert', false),
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
            abort(403, 'Unauthorized action.');
        }
        
        $priceAlert->load(['product', 'product.priceOffers' => function($query) {
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
            abort(403, 'Unauthorized action.');
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
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'target_price' => 'required|numeric|min:0.01',
            'repeat_alert' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $priceAlert->update([
            'target_price' => $request->target_price,
            'repeat_alert' => $request->boolean('repeat_alert'),
            'is_active' => $request->boolean('is_active', true),
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
            abort(403, 'Unauthorized action.');
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
            abort(403, 'Unauthorized action.');
        }

        $priceAlert->update([
            'is_active' => !$priceAlert->is_active
        ]);

        $status = $priceAlert->is_active ? 'activated' : 'deactivated';
        
        return response()->json([
            'success' => true,
            'message' => "Price alert {$status} successfully!",
            'is_active' => $priceAlert->is_active
        ]);
    }

    /**
     * Get user's alerts count
     */
    public function count()
    {
        $count = PriceAlert::where('user_id', Auth::id())
            ->where('is_active', true)
            ->count();
        
        return response()->json([
            'count' => $count
        ]);
    }
}
