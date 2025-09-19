<?php

namespace Tests\Unit\Recommendations;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PriceRangeRecommendationTest extends TestCase
{
    #[Test]
    #[CoversNothing]
    public function it_recommends_products_within_user_budget(): void
    {
        $userBudget = 500;
        $products = [
            ['name' => 'Product A', 'price' => 300],
            ['name' => 'Product B', 'price' => 450],
            ['name' => 'Product C', 'price' => 600],
            ['name' => 'Product D', 'price' => 400],
        ];

        $recommendations = $this->getProductsWithinBudget($products, $userBudget);

        $this->assertCount(3, $recommendations);
        $this->assertContains('Product A', array_column($recommendations, 'name'));
        $this->assertContains('Product B', array_column($recommendations, 'name'));
        $this->assertContains('Product D', array_column($recommendations, 'name'));
    }

    #[Test]
    #[CoversNothing]
    public function it_recommends_products_in_price_range(): void
    {
        $minPrice = 200;
        $maxPrice = 800;
        $products = [
            ['name' => 'Product A', 'price' => 150], // Below range
            ['name' => 'Product B', 'price' => 300], // In range
            ['name' => 'Product C', 'price' => 600], // In range
            ['name' => 'Product D', 'price' => 900],  // Above range
        ];

        $recommendations = $this->getProductsInPriceRange($products, $minPrice, $maxPrice);

        $this->assertCount(2, $recommendations);
        $this->assertContains('Product B', array_column($recommendations, 'name'));
        $this->assertContains('Product C', array_column($recommendations, 'name'));
    }

    #[Test]
    #[CoversNothing]
    public function it_calculates_optimal_price_range_based_on_history(): void
    {
        $purchaseHistory = [
            ['price' => 100, 'satisfaction' => 0.8],
            ['price' => 200, 'satisfaction' => 0.9],
            ['price' => 300, 'satisfaction' => 0.95],
            ['price' => 500, 'satisfaction' => 0.7],
            ['price' => 800, 'satisfaction' => 0.6],
        ];

        $optimalRange = $this->calculateOptimalPriceRange($purchaseHistory);

        $this->assertGreaterThanOrEqual(100, $optimalRange['min']);
        $this->assertLessThan(500, $optimalRange['max']);
    }

    #[Test]
    #[CoversNothing]
    public function it_recommends_products_by_value_score(): void
    {
        $products = [
            ['name' => 'Product A', 'price' => 100, 'rating' => 4.5, 'features' => 8],
            ['name' => 'Product B', 'price' => 200, 'rating' => 4.8, 'features' => 9],
            ['name' => 'Product C', 'price' => 150, 'rating' => 4.2, 'features' => 7],
        ];

        $recommendations = $this->getProductsByValueScore($products, 2);

        $this->assertCount(2, $recommendations);
        $this->assertEquals('Product A', $recommendations[0]['name']); // Best value
    }

    #[Test]
    #[CoversNothing]
    public function it_handles_dynamic_pricing_recommendations(): void
    {
        $basePrice = 100;
        $demandFactor = 1.2;
        $supplyFactor = 0.9;
        $seasonalFactor = 1.1;

        $dynamicPrice = $this->calculateDynamicPrice($basePrice, $demandFactor, $supplyFactor, $seasonalFactor);

        $expectedPrice = $basePrice * $demandFactor * $supplyFactor * $seasonalFactor;
        $this->assertEqualsWithDelta($expectedPrice, $dynamicPrice, 0.01);
    }

    #[Test]
    #[CoversNothing]
    public function it_recommends_products_by_price_sensitivity(): void
    {
        $userProfile = [
            'price_sensitivity' => 'high',
            'budget' => 300,
            'preferred_brands' => ['Brand A', 'Brand B'],
        ];

        $products = [
            ['name' => 'Product A', 'price' => 250, 'brand' => 'Brand A'],
            ['name' => 'Product B', 'price' => 400, 'brand' => 'Brand A'],
            ['name' => 'Product C', 'price' => 200, 'brand' => 'Brand C'],
            ['name' => 'Product D', 'price' => 280, 'brand' => 'Brand B'],
        ];

        $recommendations = $this->getProductsByPriceSensitivity($userProfile, $products);

        $this->assertContains('Product A', array_column($recommendations, 'name'));
        $this->assertContains('Product D', array_column($recommendations, 'name'));
        $this->assertNotContains('Product B', array_column($recommendations, 'name'));
    }

    #[Test]
    #[CoversNothing]
    public function it_calculates_price_elasticity(): void
    {
        $priceChanges = [
            ['old_price' => 100, 'new_price' => 90, 'demand_change' => 0.15],
            ['old_price' => 200, 'new_price' => 180, 'demand_change' => 0.12],
        ];

        $elasticity = $this->calculatePriceElasticity($priceChanges[0]);

        $this->assertGreaterThan(1, $elasticity); // Elastic demand
    }

    #[Test]
    #[CoversNothing]
    public function it_recommends_products_by_price_trends(): void
    {
        $priceHistory = [
            'Product A' => [100, 95, 90, 85], // Decreasing trend
            'Product B' => [200, 210, 220, 230], // Increasing trend
            'Product C' => [150, 150, 155, 150], // Stable trend
        ];

        $trendingProducts = $this->getProductsByPriceTrends($priceHistory, 'decreasing');

        $this->assertContains('Product A', $trendingProducts);
        $this->assertNotContains('Product B', $trendingProducts);
    }

    #[Test]
    #[CoversNothing]
    public function it_handles_currency_conversion_in_recommendations(): void
    {
        $userCurrency = 'EUR';
        $products = [
            ['name' => 'Product A', 'price' => 100, 'currency' => 'USD'],
            ['name' => 'Product B', 'price' => 80, 'currency' => 'EUR'],
            ['name' => 'Product C', 'price' => 90, 'currency' => 'USD'],
        ];

        $exchangeRates = ['USD' => 0.85, 'EUR' => 1.0];
        $userBudget = 100; // EUR

        $recommendations = $this->getProductsWithCurrencyConversion($products, $userCurrency, $exchangeRates, $userBudget);

        $this->assertCount(3, $recommendations);
        $this->assertContains('Product B', array_column($recommendations, 'name'));
    }

    #[Test]
    #[CoversNothing]
    public function it_calculates_price_recommendation_confidence(): void
    {
        $priceData = [
            'historical_prices' => [100, 105, 98, 102, 99],
            'market_average' => 101,
            'competitor_prices' => [95, 100, 105, 98],
        ];

        $confidence = $this->calculatePriceRecommendationConfidence($priceData);

        $this->assertGreaterThan(0.7, $confidence);
    }

    private function getProductsWithinBudget(array $products, float $budget): array
    {
        return array_filter($products, function ($product) use ($budget) {
            return $product['price'] <= $budget;
        });
    }

    private function getProductsInPriceRange(array $products, float $minPrice, float $maxPrice): array
    {
        return array_filter($products, function ($product) use ($minPrice, $maxPrice) {
            return $product['price'] >= $minPrice && $product['price'] <= $maxPrice;
        });
    }

    private function calculateOptimalPriceRange(array $purchaseHistory): array
    {
        $satisfactionThreshold = 0.8;
        $satisfactoryPurchases = array_filter($purchaseHistory, function ($purchase) use ($satisfactionThreshold) {
            return $purchase['satisfaction'] >= $satisfactionThreshold;
        });

        $prices = array_column($satisfactoryPurchases, 'price');

        return [
            'min' => min($prices),
            'max' => max($prices),
        ];
    }

    private function getProductsByValueScore(array $products, int $limit): array
    {
        foreach ($products as &$product) {
            $product['value_score'] = ($product['rating'] * $product['features']) / $product['price'];
        }

        usort($products, function ($a, $b) {
            return $b['value_score'] <=> $a['value_score'];
        });

        return array_slice($products, 0, $limit);
    }

    private function calculateDynamicPrice(float $basePrice, float $demandFactor, float $supplyFactor, float $seasonalFactor): float
    {
        return round($basePrice * $demandFactor * $supplyFactor * $seasonalFactor, 2);
    }

    private function getProductsByPriceSensitivity(array $userProfile, array $products): array
    {
        $budget = $userProfile['budget'];
        $preferredBrands = $userProfile['preferred_brands'];

        return array_filter($products, function ($product) use ($budget, $preferredBrands) {
            return $product['price'] <= $budget && in_array($product['brand'], $preferredBrands);
        });
    }

    private function calculatePriceElasticity(array $priceChange): float
    {
        $priceChangePercent = ($priceChange['new_price'] - $priceChange['old_price']) / $priceChange['old_price'];
        $demandChangePercent = $priceChange['demand_change'];

        return abs($demandChangePercent / $priceChangePercent);
    }

    private function getProductsByPriceTrends(array $priceHistory, string $trend): array
    {
        $trendingProducts = [];

        foreach ($priceHistory as $product => $prices) {
            $isDecreasing = $prices[0] > $prices[count($prices) - 1];
            $isIncreasing = $prices[0] < $prices[count($prices) - 1];

            if (($trend === 'decreasing' && $isDecreasing) ||
                ($trend === 'increasing' && $isIncreasing)
            ) {
                $trendingProducts[] = $product;
            }
        }

        return $trendingProducts;
    }

    private function getProductsWithCurrencyConversion(array $products, string $userCurrency, array $exchangeRates, float $userBudget): array
    {
        $convertedProducts = [];

        foreach ($products as $product) {
            $convertedPrice = $product['price'] * $exchangeRates[$product['currency']];

            if ($convertedPrice <= $userBudget) {
                $convertedProducts[] = array_merge($product, ['converted_price' => $convertedPrice]);
            }
        }

        return $convertedProducts;
    }

    private function calculatePriceRecommendationConfidence(array $priceData): float
    {
        $historicalPrices = $priceData['historical_prices'];
        $marketAverage = $priceData['market_average'];
        $competitorPrices = $priceData['competitor_prices'];

        $priceVariance = $this->calculateVariance($historicalPrices);
        $marketDeviation = abs(array_sum($historicalPrices) / count($historicalPrices) - $marketAverage) / $marketAverage;
        $competitorDeviation = $this->calculateCompetitorDeviation($historicalPrices, $competitorPrices);

        $confidence = 1 - ($priceVariance + $marketDeviation + $competitorDeviation) / 3;

        return max(0, min(1, $confidence));
    }

    private function calculateVariance(array $values): float
    {
        $mean = array_sum($values) / count($values);
        $variance = array_sum(array_map(function ($x) use ($mean) {
            return pow($x - $mean, 2);
        }, $values)) / count($values);

        return $variance / $mean; // Normalized variance
    }

    private function calculateCompetitorDeviation(array $historicalPrices, array $competitorPrices): float
    {
        $avgHistorical = array_sum($historicalPrices) / count($historicalPrices);
        $avgCompetitor = array_sum($competitorPrices) / count($competitorPrices);

        return abs($avgHistorical - $avgCompetitor) / $avgCompetitor;
    }
}
