<?php

declare(strict_types=1);

namespace Tests\Unit\Helpers;

use App\Helpers\PriceHelper;
use App\Models\Currency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PriceHelperTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_format_price_with_currency_symbol(): void
    {
        $currency = Currency::factory()->create([
            'code' => 'USD',
            'symbol' => '$',
        ]);

        $result = PriceHelper::formatPrice(99.99, 'USD');

        $this->assertEquals('$99.99', $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_formats_price_with_default_currency_when_none_specified(): void
    {
        config(['coprra.default_currency' => 'USD']);

        $currency = Currency::factory()->create([
            'code' => 'USD',
            'symbol' => '$',
        ]);

        $result = PriceHelper::formatPrice(150.50);

        $this->assertEquals('$150.50', $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_formats_price_with_currency_code_when_currency_not_found(): void
    {
        $result = PriceHelper::formatPrice(75.25, 'XYZ');

        $this->assertEquals('75.25 XYZ', $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_formats_price_with_two_decimal_places(): void
    {
        $currency = Currency::factory()->create([
            'code' => 'EUR',
            'symbol' => '€',
        ]);

        $result = PriceHelper::formatPrice(100.0, 'EUR');

        $this->assertEquals('€100.00', $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_calculate_price_difference_percentage(): void
    {
        $difference = PriceHelper::calculatePriceDifference(100.0, 120.0);

        $this->assertEquals(20.0, $difference);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_calculates_negative_price_difference(): void
    {
        $difference = PriceHelper::calculatePriceDifference(100.0, 80.0);

        $this->assertEquals(-20.0, $difference);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_zero_for_same_prices(): void
    {
        $difference = PriceHelper::calculatePriceDifference(100.0, 100.0);

        $this->assertEquals(0.0, $difference);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_zero_for_zero_original_price(): void
    {
        $difference = PriceHelper::calculatePriceDifference(0.0, 50.0);

        $this->assertEquals(0.0, $difference);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_zero_for_negative_original_price(): void
    {
        $difference = PriceHelper::calculatePriceDifference(-10.0, 50.0);

        $this->assertEquals(0.0, $difference);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_get_price_difference_string_positive(): void
    {
        $result = PriceHelper::getPriceDifferenceString(100.0, 125.0);

        $this->assertEquals('+25.0%', $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_get_price_difference_string_negative(): void
    {
        $result = PriceHelper::getPriceDifferenceString(100.0, 75.0);

        $this->assertEquals('-25.0%', $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_get_price_difference_string_zero(): void
    {
        $result = PriceHelper::getPriceDifferenceString(100.0, 100.0);

        $this->assertEquals('0%', $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_formats_price_difference_with_one_decimal(): void
    {
        $result = PriceHelper::getPriceDifferenceString(100.0, 133.33);

        $this->assertEquals('+33.3%', $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_identify_good_deal(): void
    {
        $allPrices = [100.0, 110.0, 120.0, 130.0]; // Average: 115
        $testPrice = 95.0; // Below 90% of average (103.5)

        $result = PriceHelper::isGoodDeal($testPrice, $allPrices);

        $this->assertTrue($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_identify_not_good_deal(): void
    {
        $allPrices = [100.0, 110.0, 120.0, 130.0]; // Average: 115
        $testPrice = 115.0; // Above 90% of average (103.5)

        $result = PriceHelper::isGoodDeal($testPrice, $allPrices);

        $this->assertFalse($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_false_for_good_deal_with_empty_prices(): void
    {
        $result = PriceHelper::isGoodDeal(50.0, []);

        $this->assertFalse($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_get_best_price_from_array(): void
    {
        $prices = [150.0, 99.99, 200.0, 75.50, 125.0];

        $result = PriceHelper::getBestPrice($prices);

        $this->assertEquals(75.50, $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_null_for_best_price_with_empty_array(): void
    {
        $result = PriceHelper::getBestPrice([]);

        $this->assertNull($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_get_best_price_with_single_price(): void
    {
        $result = PriceHelper::getBestPrice([99.99]);

        $this->assertEquals(99.99, $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_convert_usd_to_eur(): void
    {
        // Mock exchange rates
        config(['exchange_rates.USD' => 1.0]);
        config(['exchange_rates.EUR' => 0.85]);

        $result = PriceHelper::convertCurrency(100.0, 'USD', 'EUR');

        $this->assertEquals(85.0, $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_convert_eur_to_usd(): void
    {
        // Mock exchange rates
        config(['exchange_rates.EUR' => 0.85]);
        config(['exchange_rates.USD' => 1.0]);

        $result = PriceHelper::convertCurrency(85.0, 'EUR', 'USD');

        $this->assertEquals(100.0, $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_same_amount_for_same_currency(): void
    {
        $result = PriceHelper::convertCurrency(100.0, 'USD', 'USD');

        $this->assertEquals(100.0, $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_unknown_currency_conversion(): void
    {
        $result = PriceHelper::convertCurrency(100.0, 'XYZ', 'ABC');

        $this->assertEquals(100.0, $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_convert_to_egyptian_pounds(): void
    {
        // Mock exchange rates
        config(['exchange_rates.USD' => 1.0]);
        config(['exchange_rates.EGP' => 30.9]);

        $result = PriceHelper::convertCurrency(100.0, 'USD', 'EGP');

        $this->assertEquals(3090.0, $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_format_price_range_with_different_prices(): void
    {
        $currency = Currency::factory()->create([
            'code' => 'USD',
            'symbol' => '$',
        ]);

        $result = PriceHelper::formatPriceRange(99.99, 199.99, 'USD');

        $this->assertEquals('$99.99 - $199.99', $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_format_price_range_with_same_prices(): void
    {
        $currency = Currency::factory()->create([
            'code' => 'USD',
            'symbol' => '$',
        ]);

        $result = PriceHelper::formatPriceRange(99.99, 99.99, 'USD');

        $this->assertEquals('$99.99', $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_formats_price_range_with_currency_code_when_not_found(): void
    {
        $result = PriceHelper::formatPriceRange(50.0, 100.0, 'XYZ');

        $this->assertEquals('XYZ50.00 - XYZ100.00', $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_formats_price_range_with_default_currency(): void
    {
        config(['coprra.default_currency' => 'EUR']);

        $currency = Currency::factory()->create([
            'code' => 'EUR',
            'symbol' => '€',
        ]);

        $result = PriceHelper::formatPriceRange(25.0, 75.0);

        $this->assertEquals('€25.00 - €75.00', $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_precise_calculations(): void
    {
        $difference = PriceHelper::calculatePriceDifference(33.33, 66.66);

        $this->assertEquals(100.0, round($difference, 2));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_very_small_prices(): void
    {
        $currency = Currency::factory()->create([
            'code' => 'USD',
            'symbol' => '$',
        ]);

        $result = PriceHelper::formatPrice(0.01, 'USD');

        $this->assertEquals('$0.01', $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_large_prices(): void
    {
        $currency = Currency::factory()->create([
            'code' => 'USD',
            'symbol' => '$',
        ]);

        $result = PriceHelper::formatPrice(1234567.89, 'USD');

        $this->assertEquals('$1,234,567.89', $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_calculates_good_deal_threshold_correctly(): void
    {
        $allPrices = [100.0]; // Average: 100
        $testPrice = 89.0; // Below 90% threshold (90.0)
        $testPriceBad = 91.0; // Above 90% threshold (90.0)

        $resultGood = PriceHelper::isGoodDeal($testPrice, $allPrices);
        $resultBad = PriceHelper::isGoodDeal($testPriceBad, $allPrices);

        $this->assertTrue($resultGood);
        $this->assertFalse($resultBad);
    }
}
