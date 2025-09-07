<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\Currency;

class PriceHelper
{
    /**
     * Format price with currency symbol
     */
    public static function formatPrice(float $price, ?string $currencyCode = null): string
    {
        $currencyCode = $currencyCode ?? config('coprra.default_currency', 'USD');

        $currency = Currency::where('code', $currencyCode)->first();

        if (! $currency) {
            return number_format($price, 2).' '.$currencyCode;
        }

        return $currency->symbol.number_format($price, 2);
    }

    /**
     * Calculate price difference percentage
     */
    public static function calculatePriceDifference(float $originalPrice, float $comparePrice): float
    {
        if ($originalPrice <= 0) {
            return 0.0;
        }

        return (($comparePrice - $originalPrice) / $originalPrice) * 100;
    }

    /**
     * Get price difference as formatted string
     */
    public static function getPriceDifferenceString(float $originalPrice, float $comparePrice): string
    {
        $difference = self::calculatePriceDifference($originalPrice, $comparePrice);

        if ($difference > 0) {
            return '+'.number_format($difference, 1).'%';
        } elseif ($difference < 0) {
            return number_format($difference, 1).'%';
        }

        return '0%';
    }

    /**
     * Check if price is a good deal (below average)
     */
    public static function isGoodDeal(float $price, array $allPrices): bool
    {
        if (empty($allPrices)) {
            return false;
        }

        $average = array_sum($allPrices) / count($allPrices);

        return $price < $average * 0.9; // 10% below average
    }

    /**
     * Get best price from array of prices
     */
    public static function getBestPrice(array $prices): ?float
    {
        if (empty($prices)) {
            return null;
        }

        return min($prices);
    }

    /**
     * Convert price between currencies (placeholder - would need real exchange rates)
     */
    public static function convertCurrency(float $amount, string $fromCurrency, string $toCurrency): float
    {
        // This is a placeholder - in real implementation, you'd use exchange rate API
        $exchangeRates = [
            'USD' => 1.0,
            'EUR' => 0.85,
            'GBP' => 0.73,
            'JPY' => 110.0,
            'SAR' => 3.75,
            'AED' => 3.67,
            'EGP' => 30.9,
        ];

        $fromRate = $exchangeRates[$fromCurrency] ?? 1.0;
        $toRate = $exchangeRates[$toCurrency] ?? 1.0;

        return ($amount / $fromRate) * $toRate;
    }

    /**
     * Format price range
     */
    public static function formatPriceRange(float $minPrice, float $maxPrice, ?string $currencyCode = null): string
    {
        $currencyCode = $currencyCode ?? config('coprra.default_currency', 'USD');

        $currency = Currency::where('code', $currencyCode)->first();
        $symbol = $currency ? $currency->symbol : $currencyCode;

        if ($minPrice === $maxPrice) {
            return $symbol.number_format($minPrice, 2);
        }

        return $symbol.number_format($minPrice, 2).' - '.$symbol.number_format($maxPrice, 2);
    }
}
