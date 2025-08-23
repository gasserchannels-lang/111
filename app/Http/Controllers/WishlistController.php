<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    // تم إضافة هذا الثابت لتقليل التكرار
    private const VALIDATION_RULE_PRODUCT_ID = 'required|exists:products,id';

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $wishlistItems = Auth::user()->wishlist()->with('product')->get();

        return view('wishlist.index', compact('wishlistItems'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => self::VALIDATION_RULE_PRODUCT_ID, // تم استخدام الثابت هنا
        ]);

        // التحقق من أن المنتج ليس في المفضلة بالفعل
        $existingWishlist = Wishlist::where('user_id', Auth::id())
            ->where('product_id', $request->product_id)
            ->first();

        if ($existingWishlist) {
            return response()->json([
                'status' => 'exists',
                'message' => 'Product is already in your wishlist.',
            ]);
        }

        Wishlist::create([
            'user_id' => Auth::id(),
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
    public function destroy(Request $request)
    {
        $request->validate([
            'product_id' => self::VALIDATION_RULE_PRODUCT_ID, // تم استخدام الثابت هنا
        ]);

        $wishlist = Wishlist::where('user_id', Auth::id())
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
     * Toggle product in wishlist
     */
    public function toggle(Request $request)
    {
        $request->validate([
            'product_id' => self::VALIDATION_RULE_PRODUCT_ID, // تم استخدام الثابت هنا
        ]);

        $wishlist = Wishlist::where('user_id', Auth::id())
            ->where('product_id', $request->product_id)
            ->first();

        if ($wishlist) {
            // إذا كان موجودًا، قم بحذفه
            $wishlist->delete();

            return response()->json(['status' => 'removed', 'in_wishlist' => false]);
        } else {
            // إذا لم يكن موجودًا، قم بإضافته
            Wishlist::create([
                'user_id' => Auth::id(),
                'product_id' => $request->product_id,
            ]);

            return response()->json(['status' => 'added', 'in_wishlist' => true]);
        }
    }
}
