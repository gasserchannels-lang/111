<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    // تم إضافة هذا الثابت لتقليل التكرار
    private const UNAUTHORIZED_MESSAGE = 'Unauthorized action.';

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // عرض المراجعات الخاصة بالمستخدم الحالي
        $reviews = Auth::user()->reviews()->with('product')->latest()->paginate(10);

        return view('reviews.index', compact('reviews'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        // التحقق مما إذا كان المستخدم قد قام بمراجعة هذا المنتج بالفعل
        $existingReview = Review::where('user_id', Auth::id())
            ->where('product_id', $product->id)
            ->exists();

        if ($existingReview) {
            return redirect()->route('products.show', $product->id)
                ->with('error', 'You have already reviewed this product.');
        }

        return view('reviews.create', compact('product'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        // التحقق مرة أخرى من عدم وجود مراجعة مسبقة
        $existingReview = Review::where('user_id', Auth::id())
            ->where('product_id', $request->product_id)
            ->exists();

        if ($existingReview) {
            return redirect()->route('products.show', $request->product_id)
                ->with('error', 'You have already reviewed this product.');
        }

        Review::create([
            'user_id' => Auth::id(),
            'product_id' => $request->product_id,
            'rating' => $request->rating,
            'title' => $request->title,
            'content' => $request->content,
            'is_approved' => true, // الموافقة التلقائية أو يمكن تغييرها لتتطلب مراجعة
        ]);

        return redirect()->route('products.show', $request->product_id)
            ->with('success', 'Thank you for your review!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Review $review)
    {
        // التحقق من أن المستخدم هو صاحب المراجعة
        if ($review->user_id !== Auth::id()) {
            abort(403, self::UNAUTHORIZED_MESSAGE); // تم استخدام الثابت هنا
        }

        return view('reviews.edit', compact('review'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Review $review)
    {
        // التحقق من أن المستخدم هو صاحب المراجعة
        if ($review->user_id !== Auth::id()) {
            abort(403, self::UNAUTHORIZED_MESSAGE); // تم استخدام الثابت هنا
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $review->update($request->only(['rating', 'title', 'content']));

        return redirect()->route('products.show', $review->product_id)
            ->with('success', 'Review updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Review $review)
    {
        // التحقق من أن المستخدم هو صاحب المراجعة أو مدير
        if ($review->user_id !== Auth::id() && ! Auth::user()->is_admin) {
            abort(403, self::UNAUTHORIZED_MESSAGE); // تم استخدام الثابت هنا
        }

        $productId = $review->product_id;
        $review->delete();

        return redirect()->route('products.show', $productId)
            ->with('success', 'Review deleted successfully.');
    }
}
