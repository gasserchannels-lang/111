<?php

namespace Tests\Unit\DataAccuracy;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TaxCalculationTest extends TestCase
{
    #[Test]
    public function it_calculates_tax_correctly(): void
    {
        $price = 100.00;
        $taxRate = 0.15; // 15%
        $expectedTax = 15.00;

        $actualTax = $this->calculateTax($price, $taxRate);

        $this->assertEquals($expectedTax, $actualTax);
    }

    #[Test]
    public function it_handles_zero_tax_rate(): void
    {
        $price = 100.00;
        $taxRate = 0.0;
        $expectedTax = 0.00;

        $actualTax = $this->calculateTax($price, $taxRate);

        $this->assertEquals($expectedTax, $actualTax);
    }

    #[Test]
    public function it_handles_negative_prices(): void
    {
        $price = -50.00;
        $taxRate = 0.15;
        $expectedTax = -7.50;

        $actualTax = $this->calculateTax($price, $taxRate);

        $this->assertEquals($expectedTax, $actualTax);
    }

    #[Test]
    public function it_handles_high_tax_rates(): void
    {
        $price = 100.00;
        $taxRate = 0.25; // 25%
        $expectedTax = 25.00;

        $actualTax = $this->calculateTax($price, $taxRate);

        $this->assertEquals($expectedTax, $actualTax);
    }

    #[Test]
    public function it_rounds_tax_calculation_correctly(): void
    {
        $price = 33.33;
        $taxRate = 0.15;
        $expectedTax = 5.00; // Rounded to 2 decimal places

        $actualTax = $this->calculateTax($price, $taxRate);

        $this->assertEquals($expectedTax, round($actualTax, 2));
    }

    private function calculateTax(float $price, float $taxRate): float
    {
        return $price * $taxRate;
    }
}
