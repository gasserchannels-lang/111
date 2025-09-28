<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Contracts\Auth\Guard;

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
        $reviews = $auth->user()?->reviews()->with('product')->latest()->paginate(10) ?? collect();

        /** @var view-string $view */
        $view = 'reviews.index';

        return view($view, ['reviews' => $reviews]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Product $product, Guard $auth): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        // التحقق مما إذا كان المستخدم قد قام بمراجعة هذا المنتج بالفعل
        $existingReview = $product->reviews()->where('user_id', $auth->id())->exists();

        if ($existingReview) {
            return redirect()->route('products.show', $product->id)
                ->with('error', 'You have already reviewed this product.');
        }

        /** @var view-string $view */
        $view = 'reviews.create';

        return view($view, ['product' => $product]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReviewRequest $request, Guard $auth): \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
    {
        // التحقق مرة أخرى من عدم وجود مراجعة مسبقة
        $existingReview = $auth->user()?->reviews()->where('product_id', $request->product_id)->exists();

        if ($existingReview) {
            return redirect()->route('products.show', $request->product_id)
                ->with('error', 'You have already reviewed this product.');
        }

        $created = $auth->user()?->reviews()->create([
            'product_id' => $request->product_id,
            'rating' => $request->rating,
            'title' => $request->input('title'),
            'content' => $request->content,
            'is_approved' => true, // الموافقة التلقائية أو يمكن تغييرها لتتطلب مراجعة
        ]);
        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'created',
                'review_id' => $created->id ?? 0,
            ], 201);
        }

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

        /** @var view-string $view */
        $view = 'reviews.edit';

        return view($view, ['review' => $review]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateReviewRequest $request, Review $review, Guard $auth): \Illuminate\Http\RedirectResponse
    {
        // التحقق من أن المستخدم هو صاحب المراجعة
        if ($review->user_id !== $auth->id()) {
            abort(403, self::UNAUTHORIZED_MESSAGE); // تم استخدام الثابت هنا
        }

        $review->update($request->validated());

        return redirect()->route('products.show', $review->product_id)
            ->with('success', 'Review updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Review $review, Guard $auth): \Illuminate\Http\RedirectResponse
    {
        // التحقق من أن المستخدم هو صاحب المراجعة أو مدير
        if ($review->user_id !== $auth->id() && ! $auth->user()?->isAdmin()) {
            abort(403, self::UNAUTHORIZED_MESSAGE); // تم استخدام الثابت هنا
        }

        $productId = $review->product_id;
        $review->delete();

        return redirect()->route('products.show', $productId)
            ->with('success', 'Review deleted successfully.');
    }
}
