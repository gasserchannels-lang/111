<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function createOrder(User $user, array $cartItems, array $addresses): Order
    {
        return DB::transaction(function () use ($user, $cartItems, $addresses) {
            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'user_id' => $user->id,
                'status' => 'pending',
                'subtotal' => $this->calculateSubtotal($cartItems),
                'tax_amount' => $this->calculateTax($cartItems),
                'shipping_amount' => $this->calculateShipping($cartItems),
                'total_amount' => 0, // Will be calculated
                'currency' => 'USD',
                'shipping_address' => $addresses['shipping'],
                'billing_address' => $addresses['billing'],
            ]);

            $totalAmount = $order->subtotal + $order->tax_amount + $order->shipping_amount;
            $order->update(['total_amount' => $totalAmount]);

            foreach ($cartItems as $item) {
                $product = Product::findOrFail($item['product_id']);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'total_price' => $product->price * $item['quantity'],
                    'product_details' => [
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'image' => $product->image,
                    ],
                ]);
            }

            return $order;
        });
    }

    public function updateOrderStatus(Order $order, string $status): bool
    {
        $allowedTransitions = [
            'pending' => ['processing', 'cancelled'],
            'processing' => ['shipped', 'cancelled'],
            'shipped' => ['delivered'],
            'delivered' => [],
            'cancelled' => [],
            'refunded' => [],
        ];

        if (! in_array($status, $allowedTransitions[$order->status] ?? [])) {
            return false;
        }

        $updateData = ['status' => $status];

        if ($status === 'shipped') {
            $updateData['shipped_at'] = now();
        } elseif ($status === 'delivered') {
            $updateData['delivered_at'] = now();
        }

        return $order->update($updateData);
    }

    public function cancelOrder(Order $order, ?string $reason = null): bool
    {
        if (! in_array($order->status, ['pending', 'processing'])) {
            return false;
        }

        $order->update([
            'status' => 'cancelled',
            'notes' => $order->notes."\nCancelled: ".($reason ?? 'No reason provided'),
        ]);

        // Restore product stock
        foreach ($order->items as $item) {
            $product = $item->product;
            $product->increment('stock', $item->quantity);
        }

        return true;
    }

    private function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-'.date('Y').'-'.strtoupper(Str::random(8));
        } while (Order::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    private function calculateSubtotal(array $cartItems): float
    {
        return collect($cartItems)->sum(function ($item) {
            $product = Product::find($item['product_id']);

            return $product ? $product->price * $item['quantity'] : 0;
        });
    }

    private function calculateTax(array $cartItems): float
    {
        $subtotal = $this->calculateSubtotal($cartItems);

        return $subtotal * 0.1; // 10% tax rate
    }

    private function calculateShipping(array $cartItems): float
    {
        $subtotal = $this->calculateSubtotal($cartItems);

        return $subtotal > 100 ? 0 : 10; // Free shipping over $100
    }

    public function getOrderHistory(User $user, int $limit = 10)
    {
        return $user->orders()
            ->with(['items.product', 'payments'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
