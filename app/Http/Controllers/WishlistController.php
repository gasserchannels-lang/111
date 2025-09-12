<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Wishlist;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    private Guard $auth;

    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    // تم إضافة هذا الثابت لتقليل التكرار
    private const VALIDATION_RULE_PRODUCT_ID = 'required|exists:products,id';

    /**
     * Display a listing of the resource.
     */
    public function index(): \Illuminate\View\View
    {
        $wishlistItems = $this->auth->user()?->wishlists()->with('product')->get() ?? collect();

        return view('wishlist.index', ['wishlistItems' => $wishlistItems]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => self::VALIDATION_RULE_PRODUCT_ID, // تم استخدام الثابت هنا
        ]);

        // التحقق من أن المنتج ليس في المفضلة بالفعل
        $existingWishlist = $this->auth->user()?->wishlists()
            ->where('product_id', $request->product_id)
            ->first();

        if ($existingWishlist) {
            return response()->json([
                'status' => 'exists',
                'message' => 'Product is already in your wishlist.',
            ]);
        }

        $this->auth->user()?->wishlists()->create([
            'product_id' => $request->product_id,
        ]);

        return response()->json([
            'status' => 'added',
            'message' => 'Product added to wishlist successfully!',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => self::VALIDATION_RULE_PRODUCT_ID, // تم استخدام الثابت هنا
        ]);

        $wishlist = $this->auth->user()?->wishlists()
            ->where('product_id', $request->product_id)
            ->first();

        if ($wishlist) {
            $wishlist->delete();

            return response()->json([
                'status' => 'removed',
                'message' => 'Product removed from wishlist.',
            ]);
        }

        return response()->json([
            'status' => 'not_found',
            'message' => 'Product not found in wishlist.',
        ], 404);
    }

    /**
     * Toggle product in wishlist.
     */
    public function toggle(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => self::VALIDATION_RULE_PRODUCT_ID, // تم استخدام الثابت هنا
        ]);

        $wishlist = $this->auth->user()?->wishlists()
            ->where('product_id', $request->product_id)
            ->first();

        if ($wishlist) {
            // إذا كان موجودًا، قم بحذفه
            $wishlist->delete();

            return response()->json(['status' => 'removed', 'in_wishlist' => false]);
        }

        // إذا لم يكن موجودًا، قم بإضافته
        $this->auth->user()?->wishlists()->create([
            'product_id' => $request->product_id,
        ]);

        return response()->json(['status' => 'added', 'in_wishlist' => true]);
    }
}
