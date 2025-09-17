<?php

namespace Tests\Unit\Recommendations;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class BrandRecommendationTest extends TestCase
{
    #[Test]
    #[CoversNothing]
    public function it_recommends_brands_based_on_user_preferences(): void
    {
        $userPreferences = [
            'Apple' => 0.9,
            'Samsung' => 0.7,
            'Sony' => 0.6,
            'LG' => 0.4
        ];

        $recommendations = $this->getBrandRecommendations($userPreferences, 3);

        $this->assertContains('Apple', $recommendations);
        $this->assertContains('Samsung', $recommendations);
        $this->assertContains('Sony', $recommendations);
        $this->assertCount(3, $recommendations);
    }

    #[Test]
    #[CoversNothing]
    public function it_recommends_popular_brands_in_category(): void
    {
        $category = 'Electronics';
        $brandPopularity = [
            'Electronics' => [
                'Apple' => 1000,
                'Samsung' => 800,
                'Sony' => 600,
                'LG' => 400
            ],
            'Clothing' => [
                'Nike' => 1200,
                'Adidas' => 900,
                'Puma' => 500
            ]
        ];

        $recommendations = $this->getPopularBrandsInCategory($category, $brandPopularity, 2);

        $this->assertContains('Apple', $recommendations);
        $this->assertContains('Samsung', $recommendations);
        $this->assertCount(2, $recommendations);
    }

    #[Test]
    #[CoversNothing]
    public function it_recommends_similar_brands(): void
    {
        $currentBrand = 'Apple';
        $brandSimilarity = [
            'Apple' => ['Samsung', 'Google', 'Microsoft'],
            'Nike' => ['Adidas', 'Puma', 'Under Armour'],
            'Coca-Cola' => ['Pepsi', 'Sprite', 'Fanta']
        ];

        $recommendations = $this->getSimilarBrandRecommendations($currentBrand, $brandSimilarity);

        $this->assertContains('Samsung', $recommendations);
        $this->assertContains('Google', $recommendations);
        $this->assertContains('Microsoft', $recommendations);
    }

    #[Test]
    #[CoversNothing]
    public function it_recommends_brands_based_on_price_range(): void
    {
        $userBudget = 500;
        $brandPriceRanges = [
            'Apple' => ['min' => 800, 'max' => 2000],
            'Samsung' => ['min' => 300, 'max' => 1500],
            'Xiaomi' => ['min' => 100, 'max' => 600],
            'OnePlus' => ['min' => 400, 'max' => 800]
        ];

        $recommendations = $this->getBrandsInPriceRange($userBudget, $brandPriceRanges);

        $this->assertContains('Samsung', $recommendations);
        $this->assertContains('Xiaomi', $recommendations);
        $this->assertNotContains('Apple', $recommendations);
    }

    #[Test]
    #[CoversNothing]
    public function it_recommends_brands_based_on_quality_rating(): void
    {
        $brandRatings = [
            'Apple' => 4.8,
            'Samsung' => 4.5,
            'Sony' => 4.3,
            'LG' => 4.0,
            'Xiaomi' => 3.8
        ];

        $minRating = 4.2;
        $recommendations = $this->getHighRatedBrands($brandRatings, $minRating);

        $this->assertContains('Apple', $recommendations);
        $this->assertContains('Samsung', $recommendations);
        $this->assertContains('Sony', $recommendations);
        $this->assertNotContains('LG', $recommendations);
        $this->assertNotContains('Xiaomi', $recommendations);
    }

    #[Test]
    #[CoversNothing]
    public function it_handles_brand_availability_by_region(): void
    {
        $userRegion = 'US';
        $brandAvailability = [
            'Apple' => ['US', 'EU', 'Asia'],
            'Samsung' => ['US', 'EU', 'Asia'],
            'Xiaomi' => ['Asia', 'EU'],
            'Huawei' => ['Asia', 'EU']
        ];

        $availableBrands = $this->getAvailableBrandsInRegion($userRegion, $brandAvailability);

        $this->assertContains('Apple', $availableBrands);
        $this->assertContains('Samsung', $availableBrands);
        $this->assertNotContains('Xiaomi', $availableBrands);
        $this->assertNotContains('Huawei', $availableBrands);
    }

    #[Test]
    #[CoversNothing]
    public function it_recommends_brands_based_on_user_purchase_history(): void
    {
        $purchaseHistory = [
            ['brand' => 'Apple', 'amount' => 1200, 'date' => '2024-01-15'],
            ['brand' => 'Apple', 'amount' => 800, 'date' => '2024-02-10'],
            ['brand' => 'Samsung', 'amount' => 600, 'date' => '2024-01-20'],
            ['brand' => 'Sony', 'amount' => 400, 'date' => '2024-03-05']
        ];

        $recommendations = $this->getBrandsFromPurchaseHistory($purchaseHistory, 2);

        $this->assertContains('Apple', $recommendations);
        $this->assertContains('Samsung', $recommendations);
        $this->assertCount(2, $recommendations);
    }

    #[Test]
    #[CoversNothing]
    public function it_calculates_brand_loyalty_score(): void
    {
        $userPurchases = [
            'Apple' => 5,
            'Samsung' => 2,
            'Sony' => 1
        ];

        $brand = 'Apple';
        $loyaltyScore = $this->calculateBrandLoyaltyScore($userPurchases, $brand);

        $this->assertGreaterThan(0.6, $loyaltyScore);
    }

    #[Test]
    #[CoversNothing]
    public function it_recommends_trending_brands(): void
    {
        $brandTrends = [
            'Apple' => ['trend' => 'stable', 'growth' => 0.05],
            'Samsung' => ['trend' => 'up', 'growth' => 0.15],
            'Xiaomi' => ['trend' => 'up', 'growth' => 0.25],
            'LG' => ['trend' => 'down', 'growth' => -0.10]
        ];

        $trendingBrands = $this->getTrendingBrands($brandTrends);

        $this->assertContains('Samsung', $trendingBrands);
        $this->assertContains('Xiaomi', $trendingBrands);
        $this->assertNotContains('LG', $trendingBrands);
    }

    #[Test]
    #[CoversNothing]
    public function it_handles_brand_recommendation_weights(): void
    {
        $brands = [
            'Apple' => ['preference' => 0.8, 'popularity' => 0.9, 'quality' => 0.95],
            'Samsung' => ['preference' => 0.6, 'popularity' => 0.8, 'quality' => 0.85],
            'Sony' => ['preference' => 0.7, 'popularity' => 0.6, 'quality' => 0.9]
        ];

        $weights = ['preference' => 0.4, 'popularity' => 0.3, 'quality' => 0.3];

        $rankedBrands = $this->rankBrandsByWeightedScore($brands, $weights);

        $this->assertEquals('Apple', $rankedBrands[0]['brand']);
        $this->assertGreaterThan($rankedBrands[1]['score'], $rankedBrands[0]['score']);
    }

    private function getBrandRecommendations(array $userPreferences, int $limit): array
    {
        arsort($userPreferences);
        return array_slice(array_keys($userPreferences), 0, $limit);
    }

    private function getPopularBrandsInCategory(string $category, array $brandPopularity, int $limit): array
    {
        if (!isset($brandPopularity[$category])) {
            return [];
        }

        $categoryBrands = $brandPopularity[$category];
        arsort($categoryBrands);

        return array_slice(array_keys($categoryBrands), 0, $limit);
    }

    private function getSimilarBrandRecommendations(string $currentBrand, array $brandSimilarity): array
    {
        return $brandSimilarity[$currentBrand] ?? [];
    }

    private function getBrandsInPriceRange(float $budget, array $brandPriceRanges): array
    {
        $suitableBrands = [];

        foreach ($brandPriceRanges as $brand => $range) {
            if ($budget >= $range['min'] && $budget <= $range['max']) {
                $suitableBrands[] = $brand;
            }
        }

        return $suitableBrands;
    }

    private function getHighRatedBrands(array $brandRatings, float $minRating): array
    {
        return array_keys(array_filter($brandRatings, function ($rating) use ($minRating) {
            return $rating >= $minRating;
        }));
    }

    private function getAvailableBrandsInRegion(string $region, array $brandAvailability): array
    {
        $availableBrands = [];

        foreach ($brandAvailability as $brand => $regions) {
            if (in_array($region, $regions)) {
                $availableBrands[] = $brand;
            }
        }

        return $availableBrands;
    }

    private function getBrandsFromPurchaseHistory(array $purchaseHistory, int $limit): array
    {
        $brandCounts = [];

        foreach ($purchaseHistory as $purchase) {
            $brand = $purchase['brand'];
            $brandCounts[$brand] = ($brandCounts[$brand] ?? 0) + 1;
        }

        arsort($brandCounts);

        return array_slice(array_keys($brandCounts), 0, $limit);
    }

    private function calculateBrandLoyaltyScore(array $userPurchases, string $brand): float
    {
        $totalPurchases = array_sum($userPurchases);
        $brandPurchases = $userPurchases[$brand] ?? 0;

        return $totalPurchases > 0 ? $brandPurchases / $totalPurchases : 0;
    }

    private function getTrendingBrands(array $brandTrends): array
    {
        $trending = [];

        foreach ($brandTrends as $brand => $data) {
            if ($data['trend'] === 'up' && $data['growth'] > 0.1) {
                $trending[] = $brand;
            }
        }

        return $trending;
    }

    private function rankBrandsByWeightedScore(array $brands, array $weights): array
    {
        $rankedBrands = [];

        foreach ($brands as $brand => $scores) {
            $weightedScore = 0;
            foreach ($weights as $metric => $weight) {
                $weightedScore += $scores[$metric] * $weight;
            }

            $rankedBrands[] = [
                'brand' => $brand,
                'score' => $weightedScore
            ];
        }

        usort($rankedBrands, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return $rankedBrands;
    }
}
