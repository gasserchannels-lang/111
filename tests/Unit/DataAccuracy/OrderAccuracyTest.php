<?php

namespace Tests\Unit\DataAccuracy;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class OrderAccuracyTest extends TestCase
{
    #[Test]
    public function it_validates_order_totals(): void
    {
        $orderItems = [
            ['price' => 10.00, 'quantity' => 2],
            ['price' => 15.50, 'quantity' => 1],
            ['price' => 5.25, 'quantity' => 3]
        ];

        $expectedTotal = 47.25; // (10*2) + (15.50*1) + (5.25*3)
        $actualTotal = $this->calculateOrderTotal($orderItems);

        $this->assertEquals($expectedTotal, $actualTotal);
    }

    #[Test]
    public function it_validates_order_tax_calculation(): void
    {
        $subtotal = 100.00;
        $taxRate = 0.08; // 8%
        $expectedTax = 8.00;

        $actualTax = $this->calculateOrderTax($subtotal, $taxRate);

        $this->assertEquals($expectedTax, $actualTax);
    }

    #[Test]
    public function it_validates_shipping_calculation(): void
    {
        $orderWeight = 2.5; // kg
        $shippingRate = 5.00; // per kg
        $expectedShipping = 12.50;

        $actualShipping = $this->calculateShipping($orderWeight, $shippingRate);

        $this->assertEquals($expectedShipping, $actualShipping);
    }

    #[Test]
    public function it_validates_discount_application(): void
    {
        $subtotal = 100.00;
        $discountPercentage = 15; // 15%
        $expectedDiscount = 15.00;

        $actualDiscount = $this->calculateDiscount($subtotal, $discountPercentage);

        $this->assertEquals($expectedDiscount, $actualDiscount);
    }

    #[Test]
    public function it_validates_final_order_total(): void
    {
        $subtotal = 100.00;
        $tax = 8.00;
        $shipping = 10.00;
        $discount = 5.00;
        $expectedTotal = 113.00; // 100 + 8 + 10 - 5

        $actualTotal = $this->calculateFinalTotal($subtotal, $tax, $shipping, $discount);

        $this->assertEquals($expectedTotal, $actualTotal);
    }

    #[Test]
    public function it_validates_order_status_transitions(): void
    {
        $validTransitions = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['shipped', 'cancelled'],
            'shipped' => ['delivered', 'returned'],
            'delivered' => ['returned'],
            'cancelled' => [],
            'returned' => []
        ];

        $this->assertTrue($this->isValidStatusTransition('pending', 'confirmed', $validTransitions));
        $this->assertTrue($this->isValidStatusTransition('confirmed', 'shipped', $validTransitions));
        $this->assertFalse($this->isValidStatusTransition('delivered', 'pending', $validTransitions));
    }

    #[Test]
    public function it_validates_order_item_quantities(): void
    {
        $orderItems = [
            ['product_id' => 1, 'quantity' => 2, 'available_stock' => 10],
            ['product_id' => 2, 'quantity' => 5, 'available_stock' => 3], // Insufficient stock
            ['product_id' => 3, 'quantity' => 1, 'available_stock' => 1]
        ];

        $validItems = $this->validateOrderItemQuantities($orderItems);

        $this->assertCount(2, $validItems);
        $this->assertNotContains($orderItems[1], $validItems);
    }

    private function calculateOrderTotal(array $orderItems): float
    {
        $total = 0;
        foreach ($orderItems as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }

    private function calculateOrderTax(float $subtotal, float $taxRate): float
    {
        return $subtotal * $taxRate;
    }

    private function calculateShipping(float $weight, float $rate): float
    {
        return $weight * $rate;
    }

    private function calculateDiscount(float $subtotal, float $discountPercentage): float
    {
        return $subtotal * ($discountPercentage / 100);
    }

    private function calculateFinalTotal(float $subtotal, float $tax, float $shipping, float $discount): float
    {
        return $subtotal + $tax + $shipping - $discount;
    }

    private function isValidStatusTransition(string $currentStatus, string $newStatus, array $validTransitions): bool
    {
        return in_array($newStatus, $validTransitions[$currentStatus] ?? []);
    }

    private function validateOrderItemQuantities(array $orderItems): array
    {
        return array_filter($orderItems, function ($item) {
            return $item['quantity'] <= $item['available_stock'];
        });
    }
}
