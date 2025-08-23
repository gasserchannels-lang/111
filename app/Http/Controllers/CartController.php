<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Darryldecode\Cart\Facades\CartFacade as Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Livewire\Livewire;

class CartController extends Controller
{
    public function index(): View
    {
        $cartItems = Cart::getContent();
        $total = Cart::getTotal();

        return view('cart-index', [
            'cartItems' => $cartItems,
            'total' => $total,
        ]);
    }

    public function add(Request $request): RedirectResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);

        Cart::add([
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'quantity' => $request->quantity,
            'attributes' => [
                'image' => $product->image,
                'slug' => $product->slug,
            ],
        ]);

        Livewire::dispatch('cartUpdated');

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

        Livewire::dispatch('cartUpdated');

        return redirect()->back()->with('success', 'Cart updated!');
    }

    public function remove(string $id): RedirectResponse
    {
        Cart::remove($id);

        Livewire::dispatch('cartUpdated');

        return redirect()->back()->with('success', 'Item removed from cart!');
    }

    public function clear(): RedirectResponse
    {
        Cart::clear();

        Livewire::dispatch('cartUpdated');

        return redirect()->back()->with('success', 'Cart cleared!');
    }
}
