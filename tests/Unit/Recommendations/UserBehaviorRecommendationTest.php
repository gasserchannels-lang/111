<?php

namespace Tests\Unit\Recommendations;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class UserBehaviorRecommendationTest extends TestCase
{
    #[Test]
    #[CoversNothing]
    public function it_analyzes_user_browsing_patterns(): void
    {
        $browsingHistory = [
            ['page' => 'electronics', 'time_spent' => 300, 'timestamp' => '2024-01-15 10:00:00'],
            ['page' => 'smartphones', 'time_spent' => 600, 'timestamp' => '2024-01-15 10:05:00'],
            ['page' => 'iphone', 'time_spent' => 900, 'timestamp' => '2024-01-15 10:15:00'],
            ['page' => 'accessories', 'time_spent' => 180, 'timestamp' => '2024-01-15 10:30:00']
        ];

        $patterns = $this->analyzeBrowsingPatterns($browsingHistory);

        $this->assertArrayHasKey('most_viewed_category', $patterns);
        $this->assertArrayHasKey('average_session_time', $patterns);
        $this->assertEquals('iphone', $patterns['most_viewed_category']);
    }

    #[Test]
    #[CoversNothing]
    public function it_tracks_user_purchase_frequency(): void
    {
        $purchaseHistory = [
            ['date' => '2024-01-01', 'amount' => 100],
            ['date' => '2024-01-15', 'amount' => 200],
            ['date' => '2024-02-01', 'amount' => 150],
            ['date' => '2024-02-15', 'amount' => 300]
        ];

        $frequency = $this->calculatePurchaseFrequency($purchaseHistory);

        $this->assertGreaterThan(0, $frequency);
        $this->assertLessThan(1, $frequency); // Purchases per day
    }

    #[Test]
    #[CoversNothing]
    public function it_identifies_user_preferences_from_behavior(): void
    {
        $userBehavior = [
            'browsed_categories' => ['Electronics', 'Electronics', 'Clothing', 'Electronics'],
            'clicked_products' => ['iPhone', 'Samsung', 'Nike', 'iPhone'],
            'time_on_pages' => [300, 600, 120, 900],
            'searches' => ['smartphone', 'iPhone', 'shoes', 'iPhone case']
        ];

        $preferences = $this->identifyUserPreferences($userBehavior);

        $this->assertArrayHasKey('preferred_category', $preferences);
        $this->assertArrayHasKey('preferred_brand', $preferences);
        $this->assertEquals('Electronics', $preferences['preferred_category']);
        $this->assertEquals('iPhone', $preferences['preferred_brand']);
    }

    #[Test]
    #[CoversNothing]
    public function it_predicts_user_intent(): void
    {
        $recentBehavior = [
            'searches' => ['iPhone 15', 'iPhone accessories', 'iPhone case'],
            'browsed_products' => ['iPhone 15 Pro', 'iPhone 15 Pro Max'],
            'time_spent' => 1800, // 30 minutes
            'pages_viewed' => 8
        ];

        $intent = $this->predictUserIntent($recentBehavior);

        $this->assertArrayHasKey('intent_type', $intent);
        $this->assertArrayHasKey('confidence', $intent);
        $this->assertEquals('purchasing', $intent['intent_type']);
        $this->assertGreaterThan(0.7, $intent['confidence']);
    }

    #[Test]
    #[CoversNothing]
    public function it_analyzes_user_engagement_level(): void
    {
        $userMetrics = [
            'sessions_per_week' => 5,
            'average_session_duration' => 600, // 10 minutes
            'pages_per_session' => 8,
            'bounce_rate' => 0.2,
            'return_visits' => 3
        ];

        $engagementLevel = $this->calculateEngagementLevel($userMetrics);

        $this->assertArrayHasKey('level', $engagementLevel);
        $this->assertArrayHasKey('score', $engagementLevel);
        $this->assertContains($engagementLevel['level'], ['low', 'medium', 'high']);
    }

    #[Test]
    #[CoversNothing]
    public function it_tracks_user_price_sensitivity(): void
    {
        $priceBehavior = [
            'viewed_products' => [
                ['price' => 100, 'purchased' => true],
                ['price' => 200, 'purchased' => false],
                ['price' => 150, 'purchased' => true],
                ['price' => 300, 'purchased' => false]
            ],
            'price_alerts_set' => 2,
            'discount_searches' => 5
        ];

        $sensitivity = $this->calculatePriceSensitivity($priceBehavior);

        $this->assertArrayHasKey('level', $sensitivity);
        $this->assertArrayHasKey('threshold', $sensitivity);
        $this->assertLessThan(200, $sensitivity['threshold']);
    }

    #[Test]
    #[CoversNothing]
    public function it_analyzes_user_device_preferences(): void
    {
        $deviceUsage = [
            'mobile' => ['sessions' => 15, 'purchases' => 3, 'time_spent' => 1800],
            'desktop' => ['sessions' => 8, 'purchases' => 5, 'time_spent' => 2400],
            'tablet' => ['sessions' => 3, 'purchases' => 1, 'time_spent' => 600]
        ];

        $preferences = $this->analyzeDevicePreferences($deviceUsage);

        $this->assertArrayHasKey('primary_device', $preferences);
        $this->assertArrayHasKey('purchase_device', $preferences);
        $this->assertEquals('mobile', $preferences['primary_device']);
        $this->assertEquals('desktop', $preferences['purchase_device']);
    }

    #[Test]
    #[CoversNothing]
    public function it_tracks_user_seasonal_patterns(): void
    {
        $seasonalData = [
            'Winter' => ['purchases' => 10, 'categories' => ['Winter Clothing', 'Heating']],
            'Spring' => ['purchases' => 8, 'categories' => ['Spring Clothing', 'Gardening']],
            'Summer' => ['purchases' => 12, 'categories' => ['Summer Clothing', 'Outdoor']],
            'Fall' => ['purchases' => 6, 'categories' => ['Fall Clothing', 'Indoor']]
        ];

        $patterns = $this->analyzeSeasonalPatterns($seasonalData);

        $this->assertArrayHasKey('peak_season', $patterns);
        $this->assertArrayHasKey('preferred_categories', $patterns);
        $this->assertEquals('Summer', $patterns['peak_season']);
    }

    #[Test]
    #[CoversNothing]
    public function it_predicts_user_churn_risk(): void
    {
        $userMetrics = [
            'days_since_last_visit' => 30,
            'sessions_last_month' => 2,
            'purchases_last_month' => 0,
            'engagement_score' => 0.3,
            'support_tickets' => 2
        ];

        $churnRisk = $this->predictChurnRisk($userMetrics);

        $this->assertArrayHasKey('risk_level', $churnRisk);
        $this->assertArrayHasKey('probability', $churnRisk);
        $this->assertContains($churnRisk['risk_level'], ['low', 'medium', 'high']);
    }

    #[Test]
    #[CoversNothing]
    public function it_generates_personalized_recommendations(): void
    {
        $userProfile = [
            'preferences' => ['Electronics', 'Apple'],
            'price_range' => [100, 500],
            'engagement_level' => 'high',
            'purchase_frequency' => 0.5
        ];

        $availableProducts = [
            ['name' => 'iPhone', 'category' => 'Electronics', 'brand' => 'Apple', 'price' => 400],
            ['name' => 'Samsung', 'category' => 'Electronics', 'brand' => 'Samsung', 'price' => 350],
            ['name' => 'Nike Shoes', 'category' => 'Clothing', 'brand' => 'Nike', 'price' => 120]
        ];

        $recommendations = $this->generatePersonalizedRecommendations($userProfile, $availableProducts);

        $this->assertContains('iPhone', array_column($recommendations, 'name'));
        $this->assertNotContains('Nike Shoes', array_column($recommendations, 'name'));
    }

    private function analyzeBrowsingPatterns(array $browsingHistory): array
    {
        $categoryTime = [];
        $totalTime = 0;

        foreach ($browsingHistory as $session) {
            $category = $session['page'];
            $time = $session['time_spent'];

            $categoryTime[$category] = ($categoryTime[$category] ?? 0) + $time;
            $totalTime += $time;
        }

        $mostViewedCategory = array_keys($categoryTime, max($categoryTime))[0];
        $averageSessionTime = $totalTime / count($browsingHistory);

        return [
            'most_viewed_category' => $mostViewedCategory,
            'average_session_time' => $averageSessionTime,
            'category_time_distribution' => $categoryTime
        ];
    }

    private function calculatePurchaseFrequency(array $purchaseHistory): float
    {
        if (empty($purchaseHistory)) {
            return 0;
        }

        $firstPurchase = new \DateTime($purchaseHistory[0]['date']);
        $lastPurchase = new \DateTime(end($purchaseHistory)['date']);
        $daysDiff = $lastPurchase->diff($firstPurchase)->days;

        return $daysDiff > 0 ? count($purchaseHistory) / $daysDiff : 0;
    }

    private function identifyUserPreferences(array $userBehavior): array
    {
        $categoryCounts = array_count_values($userBehavior['browsed_categories']);
        $brandCounts = array_count_values($userBehavior['clicked_products']);

        $preferredCategory = array_keys($categoryCounts, max($categoryCounts))[0];
        $preferredBrand = array_keys($brandCounts, max($brandCounts))[0];

        return [
            'preferred_category' => $preferredCategory,
            'preferred_brand' => $preferredBrand,
            'category_confidence' => max($categoryCounts) / array_sum($categoryCounts),
            'brand_confidence' => max($brandCounts) / array_sum($brandCounts)
        ];
    }

    private function predictUserIntent(array $recentBehavior): array
    {
        $intentScore = 0;

        // Analyze search patterns
        $searches = $recentBehavior['searches'];
        $productSearches = array_filter($searches, function ($search) {
            return strpos(strtolower($search), 'iphone') !== false;
        });
        $intentScore += count($productSearches) * 0.2;

        // Analyze browsing depth
        $intentScore += min($recentBehavior['pages_viewed'] * 0.1, 0.3);

        // Analyze time spent
        $intentScore += min($recentBehavior['time_spent'] / 3600, 0.3); // Max 0.3 for 1 hour

        $intentType = $intentScore > 0.5 ? 'purchasing' : 'browsing';

        return [
            'intent_type' => $intentType,
            'confidence' => min($intentScore, 1.0)
        ];
    }

    private function calculateEngagementLevel(array $userMetrics): array
    {
        $score = 0;

        // Sessions per week (0-0.2)
        $score += min($userMetrics['sessions_per_week'] * 0.04, 0.2);

        // Average session duration (0-0.3)
        $score += min($userMetrics['average_session_duration'] / 3600 * 0.3, 0.3);

        // Pages per session (0-0.2)
        $score += min($userMetrics['pages_per_session'] * 0.025, 0.2);

        // Bounce rate (0-0.2)
        $score += (1 - $userMetrics['bounce_rate']) * 0.2;

        // Return visits (0-0.1)
        $score += min($userMetrics['return_visits'] * 0.033, 0.1);

        $level = $score > 0.7 ? 'high' : ($score > 0.4 ? 'medium' : 'low');

        return [
            'level' => $level,
            'score' => $score
        ];
    }

    private function calculatePriceSensitivity(array $priceBehavior): array
    {
        $purchasedPrices = array_column(array_filter($priceBehavior['viewed_products'], function ($p) {
            return $p['purchased'];
        }), 'price');

        $notPurchasedPrices = array_column(array_filter($priceBehavior['viewed_products'], function ($p) {
            return !$p['purchased'];
        }), 'price');

        $maxPurchasedPrice = empty($purchasedPrices) ? 0 : max($purchasedPrices);
        $minNotPurchasedPrice = empty($notPurchasedPrices) ? PHP_FLOAT_MAX : min($notPurchasedPrices);

        $threshold = $maxPurchasedPrice > 0 ? $maxPurchasedPrice : $minNotPurchasedPrice;

        $sensitivityScore = $priceBehavior['price_alerts_set'] * 0.1 +
            $priceBehavior['discount_searches'] * 0.05;

        $level = $sensitivityScore > 0.5 ? 'high' : ($sensitivityScore > 0.2 ? 'medium' : 'low');

        return [
            'level' => $level,
            'threshold' => $threshold,
            'score' => $sensitivityScore
        ];
    }

    private function analyzeDevicePreferences(array $deviceUsage): array
    {
        $primaryDevice = '';
        $purchaseDevice = '';
        $maxSessions = 0;
        $maxPurchases = 0;

        foreach ($deviceUsage as $device => $metrics) {
            if ($metrics['sessions'] > $maxSessions) {
                $maxSessions = $metrics['sessions'];
                $primaryDevice = $device;
            }

            if ($metrics['purchases'] > $maxPurchases) {
                $maxPurchases = $metrics['purchases'];
                $purchaseDevice = $device;
            }
        }

        return [
            'primary_device' => $primaryDevice,
            'purchase_device' => $purchaseDevice,
            'device_usage' => $deviceUsage
        ];
    }

    private function analyzeSeasonalPatterns(array $seasonalData): array
    {
        $peakSeason = '';
        $maxPurchases = 0;
        $allCategories = [];

        foreach ($seasonalData as $season => $data) {
            if ($data['purchases'] > $maxPurchases) {
                $maxPurchases = $data['purchases'];
                $peakSeason = $season;
            }

            $allCategories = array_merge($allCategories, $data['categories']);
        }

        $preferredCategories = array_count_values($allCategories);
        arsort($preferredCategories);

        return [
            'peak_season' => $peakSeason,
            'preferred_categories' => array_keys(array_slice($preferredCategories, 0, 3, true))
        ];
    }

    private function predictChurnRisk(array $userMetrics): array
    {
        $riskScore = 0;

        // Days since last visit (0-0.3)
        $riskScore += min($userMetrics['days_since_last_visit'] / 30 * 0.3, 0.3);

        // Sessions last month (0-0.2)
        $riskScore += max(0, (5 - $userMetrics['sessions_last_month']) * 0.04);

        // Purchases last month (0-0.2)
        $riskScore += max(0, (2 - $userMetrics['purchases_last_month']) * 0.1);

        // Engagement score (0-0.2)
        $riskScore += (1 - $userMetrics['engagement_score']) * 0.2;

        // Support tickets (0-0.1)
        $riskScore += min($userMetrics['support_tickets'] * 0.05, 0.1);

        $probability = min($riskScore, 1.0);
        $riskLevel = $probability > 0.7 ? 'high' : ($probability > 0.4 ? 'medium' : 'low');

        return [
            'risk_level' => $riskLevel,
            'probability' => $probability
        ];
    }

    private function generatePersonalizedRecommendations(array $userProfile, array $availableProducts): array
    {
        $recommendations = [];

        foreach ($availableProducts as $product) {
            $score = 0;

            // Category preference
            if (in_array($product['category'], $userProfile['preferences'])) {
                $score += 0.4;
            }

            // Brand preference
            if (in_array($product['brand'], $userProfile['preferences'])) {
                $score += 0.3;
            }

            // Price range
            if (
                $product['price'] >= $userProfile['price_range'][0] &&
                $product['price'] <= $userProfile['price_range'][1]
            ) {
                $score += 0.3;
            }

            if ($score > 0.5) {
                $recommendations[] = array_merge($product, ['recommendation_score' => $score]);
            }
        }

        usort($recommendations, function ($a, $b) {
            return $b['recommendation_score'] <=> $a['recommendation_score'];
        });

        return $recommendations;
    }
}
