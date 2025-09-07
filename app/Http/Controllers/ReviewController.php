<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    // تم إضافة هذا الثابت لتقليل التكرار
    private const UNAUTHORIZED_MESSAGE = 'Unauthorized action.';

    /**
     * Display a listing of the resource.
     */
    public function index(Guard $auth): \Illuminate\View\View
    {
        // عرض المراجعات الخاصة بالمستخدم الحالي
        $reviews = $auth->user()->reviews()->with('product')->latest()->paginate(10);

        return view('reviews.index', ['reviews' => $reviews]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Product $product, Guard $auth): \Illuminate\View\View
    {
        // التحقق مما إذا كان المستخدم قد قام بمراجعة هذا المنتج بالفعل
        $existingReview = $product->reviews()->where('user_id', $auth->id())->exists();

        if ($existingReview) {
            return redirect()->route('products.show', $product->id)
                ->with('error', 'You have already reviewed this product.');
        }

        return view('reviews.create', ['product' => $product]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Guard $auth): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        // التحقق مرة أخرى من عدم وجود مراجعة مسبقة
        $existingReview = $auth->user()->reviews()->where('product_id', $request->product_id)->exists();

        if ($existingReview) {
            return redirect()->route('products.show', $request->product_id)
                ->with('error', 'You have already reviewed this product.');
        }

        $auth->user()->reviews()->create([
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
    public function edit(Review $review, Guard $auth): \Illuminate\View\View
    {
        // التحقق من أن المستخدم هو صاحب المراجعة
        if ($review->user_id !== $auth->id()) {
            abort(403, self::UNAUTHORIZED_MESSAGE); // تم استخدام الثابت هنا
        }

        return view('reviews.edit', ['review' => $review]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Review $review, Guard $auth): \Illuminate\Http\RedirectResponse
    {
        // التحقق من أن المستخدم هو صاحب المراجعة
        if ($review->user_id !== $auth->id()) {
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
    public function destroy(Review $review, Guard $auth): \Illuminate\Http\RedirectResponse
    {
        // التحقق من أن المستخدم هو صاحب المراجعة أو مدير
        if ($review->user_id !== $auth->id() && ! $auth->user()->isAdmin()) {
            abort(403, self::UNAUTHORIZED_MESSAGE); // تم استخدام الثابت هنا
        }

        $productId = $review->product_id;
        $review->delete();

        return redirect()->route('products.show', $productId)
            ->with('success', 'Review deleted successfully.');
    }
}
