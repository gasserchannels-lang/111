<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CartRequest;
use App\Models\Product;
use Darryldecode\Cart\Facades\CartFacade as Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct()
    {
        // Constructor can be empty or used for middleware
    }

    public function index(): View
    {
        $cartItems = Cart::getContent();
        $total = Cart::getTotal();

        return view('cart.index', [
            'cartItems' => $cartItems,
            'total' => $total,
        ]);
    }

    /**
     * @param  \App\Models\Product<\Database\Factories\ProductFactory>  $product
     */
    public function add(CartRequest $request, Product $product): RedirectResponse
    {
        $validated = $request->validated();

        Cart::add([
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'quantity' => $validated['quantity'],
            'attributes' => array_merge([
                'image' => $product->image ?? 'default-product.jpg',
                'slug' => $product->slug,
            ], $validated['attributes'] ?? []),
        ]);

        return redirect()->back()->with('success', 'Product added to cart!');
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'id' => 'required',
            'quantity' => 'required|integer|min:1',
        ]);

        Cart::update($request->id, [
            'quantity' => [
                'relative' => false,
                'value' => $request->quantity,
            ],
        ]);

        return redirect()->back()->with('success', 'Cart updated!');
    }

    public function remove(string $itemId): RedirectResponse
    {
        Cart::remove($itemId);

        return redirect()->back()->with('success', 'Item removed from cart!');
    }

    public function clear(): RedirectResponse
    {
        Cart::clear();

        return redirect()->back()->with('success', 'Cart cleared!');
    }
}
