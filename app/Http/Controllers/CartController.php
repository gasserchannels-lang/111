<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use Darryldecode\Cart\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Livewire\LivewireManager;

class CartController extends Controller
{
    private Cart $cart;

    private LivewireManager $livewire;

    public function __construct(Cart $cart, LivewireManager $livewire)
    {
        $this->cart = $cart;
        $this->livewire = $livewire;
    }

    public function index(): View
    {
        $cartItems = $this->cart->getContent();
        $total = $this->cart->getTotal();

        return view('cart-index', [
            'cartItems' => $cartItems,
            'total' => $total,
        ]);
    }

    public function add(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $this->cart->add([
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'quantity' => $request->quantity,
            'attributes' => [
                'image' => $product->image ?? 'default-product.jpg',
                'slug' => $product->slug,
            ],
        ]);

        $this->livewire->dispatch('cartUpdated');

        return redirect()->back()->with('success', 'Product added to cart!');
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'id' => 'required',
            'quantity' => 'required|integer|min:1',
        ]);

        $this->cart->update($request->id, [
            'quantity' => [
                'relative' => false,
                'value' => $request->quantity,
            ],
        ]);

        $this->livewire->dispatch('cartUpdated');

        return redirect()->back()->with('success', 'Cart updated!');
    }

    public function remove(string $itemId): RedirectResponse
    {
        $this->cart->remove($itemId);

        $this->livewire->dispatch('cartUpdated');

        return redirect()->back()->with('success', 'Item removed from cart!');
    }

    public function clear(): RedirectResponse
    {
        $this->cart->clear();

        $this->livewire->dispatch('cartUpdated');

        return redirect()->back()->with('success', 'Cart cleared!');
    }
}
