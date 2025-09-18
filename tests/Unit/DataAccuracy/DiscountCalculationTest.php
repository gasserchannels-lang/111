<?php

namespace Tests\Unit\DataAccuracy;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class DiscountCalculationTest extends TestCase
{
    #[Test]
    public function it_calculates_percentage_discount_correctly(): void
    {
        $price = 100.00;
        $discountPercentage = 20; // 20%
        $expectedDiscount = 20.00;

        $actualDiscount = $this->calculatePercentageDiscount($price, $discountPercentage);

        $this->assertEquals($expectedDiscount, $actualDiscount);
    }

    #[Test]
    public function it_calculates_fixed_discount_correctly(): void
    {
        $price = 100.00;
        $fixedDiscount = 15.00;
        $expectedDiscount = 15.00;

        $actualDiscount = $this->calculateFixedDiscount($price, $fixedDiscount);

        $this->assertEquals($expectedDiscount, $actualDiscount);
    }

    #[Test]
    public function it_handles_discount_greater_than_price(): void
    {
        $price = 50.00;
        $discountPercentage = 150; // 150%
        $expectedDiscount = 50.00; // Should not exceed price

        $actualDiscount = $this->calculatePercentageDiscount($price, $discountPercentage);

        $this->assertEquals($expectedDiscount, $actualDiscount);
    }

    #[Test]
    public function it_calculates_final_price_after_discount(): void
    {
        $price = 100.00;
        $discountPercentage = 25; // 25%
        $expectedFinalPrice = 75.00;

        $discount = $this->calculatePercentageDiscount($price, $discountPercentage);
        $finalPrice = $price - $discount;

        $this->assertEquals($expectedFinalPrice, $finalPrice);
    }

    #[Test]
    public function it_handles_zero_discount(): void
    {
        $price = 100.00;
        $discountPercentage = 0;
        $expectedDiscount = 0.00;

        $actualDiscount = $this->calculatePercentageDiscount($price, $discountPercentage);

        $this->assertEquals($expectedDiscount, $actualDiscount);
    }

    #[Test]
    public function it_handles_negative_discount(): void
    {
        $price = 100.00;
        $discountPercentage = -10; // -10%
        $expectedDiscount = 0.00; // Should not be negative

        $actualDiscount = $this->calculatePercentageDiscount($price, $discountPercentage);

        $this->assertEquals($expectedDiscount, $actualDiscount);
    }

    private function calculatePercentageDiscount(float $price, float $discountPercentage): float
    {
        $discount = $price * ($discountPercentage / 100);
        // Don't exceed the price and don't allow negative discounts
        return max(0, min($discount, $price));
    }

    private function calculateFixedDiscount(float $price, float $fixedDiscount): float
    {
        return min($fixedDiscount, $price); // Don't exceed the price
    }
}
