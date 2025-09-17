<?php

declare(strict_types=1);

namespace Tests\Unit\DataAccuracy;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CurrencyConversionTest extends TestCase
{
    private array $exchangeRates = [
        'USD' => 1.0,
        'EUR' => 0.85,
        'GBP' => 0.73,
        'JPY' => 110.0,
        'AED' => 3.67,
        'EGP' => 15.7
    ];

    #[Test]
    public function it_converts_usd_to_eur(): void
    {
        $usdAmount = 100.00;
        $expectedEur = $usdAmount * $this->exchangeRates['EUR'];

        $this->assertEquals(85.00, $expectedEur);
    }

    #[Test]
    public function it_converts_eur_to_usd(): void
    {
        $eurAmount = 85.00;
        $expectedUsd = $eurAmount / $this->exchangeRates['EUR'];

        $this->assertEquals(100.00, round($expectedUsd, 2));
    }

    #[Test]
    public function it_converts_usd_to_gbp(): void
    {
        $usdAmount = 100.00;
        $expectedGbp = $usdAmount * $this->exchangeRates['GBP'];

        $this->assertEquals(73.00, $expectedGbp);
    }

    #[Test]
    public function it_converts_usd_to_jpy(): void
    {
        $usdAmount = 100.00;
        $expectedJpy = $usdAmount * $this->exchangeRates['JPY'];

        $this->assertEquals(11000.00, $expectedJpy);
    }

    #[Test]
    public function it_converts_usd_to_aed(): void
    {
        $usdAmount = 100.00;
        $expectedAed = $usdAmount * $this->exchangeRates['AED'];

        $this->assertEquals(367.00, $expectedAed);
    }

    #[Test]
    public function it_converts_usd_to_egp(): void
    {
        $usdAmount = 100.00;
        $expectedEgp = $usdAmount * $this->exchangeRates['EGP'];

        $this->assertEquals(1570.00, $expectedEgp);
    }

    #[Test]
    public function it_handles_round_trip_conversion(): void
    {
        $originalAmount = 100.00;
        $convertedAmount = $originalAmount * $this->exchangeRates['EUR'];
        $backToOriginal = $convertedAmount / $this->exchangeRates['EUR'];

        $this->assertEquals($originalAmount, round($backToOriginal, 2));
    }

    #[Test]
    public function it_validates_exchange_rate_precision(): void
    {
        foreach ($this->exchangeRates as $currency => $rate) {
            $this->assertIsFloat($rate);
            $this->assertGreaterThan(0, $rate);
        }
    }

    #[Test]
    public function it_handles_zero_amount(): void
    {
        $zeroAmount = 0.00;
        $convertedAmount = $zeroAmount * $this->exchangeRates['EUR'];

        $this->assertEquals(0.00, $convertedAmount);
    }

    #[Test]
    public function it_handles_negative_amount(): void
    {
        $negativeAmount = -100.00;
        $convertedAmount = $negativeAmount * $this->exchangeRates['EUR'];

        $this->assertEquals(-85.00, $convertedAmount);
    }

    #[Test]
    public function it_validates_currency_codes(): void
    {
        $validCurrencies = ['USD', 'EUR', 'GBP', 'JPY', 'AED', 'EGP'];

        foreach ($validCurrencies as $currency) {
            $this->assertArrayHasKey($currency, $this->exchangeRates);
            $this->assertEquals(3, strlen($currency));
        }
    }

    #[Test]
    public function it_calculates_conversion_fee(): void
    {
        $amount = 100.00;
        $feeRate = 0.02; // 2%
        $fee = $amount * $feeRate;

        $this->assertEquals(2.00, $fee);
    }

    #[Test]
    public function it_handles_large_amounts(): void
    {
        $largeAmount = 1000000.00;
        $convertedAmount = $largeAmount * $this->exchangeRates['EUR'];

        $this->assertEquals(850000.00, $convertedAmount);
    }

    #[Test]
    public function it_handles_small_amounts(): void
    {
        $smallAmount = 0.01;
        $convertedAmount = $smallAmount * $this->exchangeRates['EUR'];

        $this->assertEquals(0.0085, round($convertedAmount, 4));
    }
}
