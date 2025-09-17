<?php

namespace Tests\Unit\Recommendations;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PriceDropAlertTest extends TestCase
{
    #[Test]
    #[CoversNothing]
    public function it_detects_price_drops(): void
    {
        $priceHistory = [
            ['date' => '2024-01-01', 'price' => 1000.00],
            ['date' => '2024-01-15', 'price' => 950.00],
            ['date' => '2024-02-01', 'price' => 900.00],
            ['date' => '2024-02-15', 'price' => 850.00]
        ];

        $priceDrops = $this->detectPriceDrops($priceHistory, 5.0); // 5% threshold
        $this->assertCount(3, $priceDrops);
        $this->assertEquals(5.0, $priceDrops[0]['drop_percentage']);
    }

    #[Test]
    #[CoversNothing]
    public function it_detects_significant_price_drops(): void
    {
        $priceHistory = [
            ['date' => '2024-01-01', 'price' => 1000.00],
            ['date' => '2024-01-15', 'price' => 950.00],
            ['date' => '2024-02-01', 'price' => 800.00], // 20% drop
            ['date' => '2024-02-15', 'price' => 750.00]  // 6.25% drop
        ];

        $significantDrops = $this->detectSignificantPriceDrops($priceHistory, 10.0);
        $this->assertCount(1, $significantDrops);
        $this->assertEqualsWithDelta(20.0, $significantDrops[0]['drop_percentage'], 5.0);
    }

    #[Test]
    #[CoversNothing]
    public function it_creates_price_alert_for_user(): void
    {
        $user = ['id' => 1, 'email' => 'user@example.com'];
        $product = ['id' => 1, 'name' => 'iPhone 15', 'current_price' => 900.00];
        $targetPrice = 800.00;

        $alert = $this->createPriceAlert($user, $product, $targetPrice);
        $this->assertEquals(1, $alert['user_id']);
        $this->assertEquals(1, $alert['product_id']);
        $this->assertEquals(800.00, $alert['target_price']);
        $this->assertTrue($alert['is_active']);
    }

    #[Test]
    #[CoversNothing]
    public function it_triggers_price_alert_when_target_reached(): void
    {
        $alerts = [
            ['id' => 1, 'user_id' => 1, 'product_id' => 1, 'target_price' => 800.00, 'is_active' => true],
            ['id' => 2, 'user_id' => 2, 'product_id' => 2, 'target_price' => 600.00, 'is_active' => true]
        ];

        $currentPrices = [
            1 => 750.00, // Target reached
            2 => 650.00  // Target not reached
        ];

        $triggeredAlerts = $this->triggerPriceAlerts($alerts, $currentPrices);
        $this->assertCount(1, $triggeredAlerts);
        $this->assertEquals(1, $triggeredAlerts[0]['alert_id']);
    }

    #[Test]
    #[CoversNothing]
    public function it_calculates_price_drop_percentage(): void
    {
        $oldPrice = 1000.00;
        $newPrice = 800.00;

        $dropPercentage = $this->calculatePriceDropPercentage($oldPrice, $newPrice);
        $this->assertEquals(20.0, $dropPercentage);
    }

    #[Test]
    #[CoversNothing]
    public function it_identifies_best_price_drops(): void
    {
        $priceDrops = [
            ['product_id' => 1, 'old_price' => 1000.00, 'new_price' => 900.00, 'drop_percentage' => 10.0],
            ['product_id' => 2, 'old_price' => 500.00, 'new_price' => 400.00, 'drop_percentage' => 20.0],
            ['product_id' => 3, 'old_price' => 200.00, 'new_price' => 190.00, 'drop_percentage' => 5.0]
        ];

        $bestDrops = $this->getBestPriceDrops($priceDrops, 2);
        $this->assertCount(2, $bestDrops);
        $this->assertEquals(2, $bestDrops[0]['product_id']); // Highest percentage
    }

    #[Test]
    #[CoversNothing]
    public function it_identifies_historical_low_prices(): void
    {
        $priceHistory = [
            ['date' => '2024-01-01', 'price' => 1000.00],
            ['date' => '2024-01-15', 'price' => 950.00],
            ['date' => '2024-02-01', 'price' => 900.00],
            ['date' => '2024-02-15', 'price' => 850.00],
            ['date' => '2024-03-01', 'price' => 900.00]
        ];

        $historicalLows = $this->getHistoricalLowPrices($priceHistory);
        $this->assertCount(1, $historicalLows);
        $this->assertEquals(850.00, $historicalLows[0]['price']);
    }

    #[Test]
    #[CoversNothing]
    public function it_identifies_trending_down_products(): void
    {
        $products = [
            ['id' => 1, 'name' => 'iPhone 15', 'trend' => 'down', 'price_change' => -10.0],
            ['id' => 2, 'name' => 'Samsung Galaxy S24', 'trend' => 'up', 'price_change' => 5.0],
            ['id' => 3, 'name' => 'Google Pixel 8', 'trend' => 'down', 'price_change' => -15.0]
        ];

        $trendingDown = $this->getTrendingDownProducts($products);
        $this->assertCount(2, $trendingDown);
        $this->assertEquals(3, $trendingDown[0]['id']); // Highest negative change
    }

    #[Test]
    #[CoversNothing]
    public function it_identifies_seasonal_price_drops(): void
    {
        $products = [
            ['id' => 1, 'name' => 'Winter Jacket', 'season' => 'winter', 'price_drop' => 30.0],
            ['id' => 2, 'name' => 'Summer Dress', 'season' => 'summer', 'price_drop' => 20.0],
            ['id' => 3, 'name' => 'Sunglasses', 'season' => 'summer', 'price_drop' => 15.0]
        ];

        $seasonalDrops = $this->getSeasonalPriceDrops($products, 'winter');
        $this->assertCount(1, $seasonalDrops);
        $this->assertEquals(1, $seasonalDrops[0]['id']);
    }

    #[Test]
    #[CoversNothing]
    public function it_identifies_clearance_price_drops(): void
    {
        $products = [
            ['id' => 1, 'name' => 'Old Model Phone', 'is_clearance' => true, 'price_drop' => 50.0],
            ['id' => 2, 'name' => 'New Model Phone', 'is_clearance' => false, 'price_drop' => 10.0],
            ['id' => 3, 'name' => 'Discontinued Item', 'is_clearance' => true, 'price_drop' => 70.0]
        ];

        $clearanceDrops = $this->getClearancePriceDrops($products);
        $this->assertCount(2, $clearanceDrops);
        $this->assertEquals(3, $clearanceDrops[0]['id']); // Highest clearance drop
    }

    #[Test]
    #[CoversNothing]
    public function it_identifies_limited_time_offers(): void
    {
        $offers = [
            ['id' => 1, 'name' => 'Flash Sale', 'discount' => 30.0, 'end_date' => '2024-12-31'],
            ['id' => 2, 'name' => 'Weekend Special', 'discount' => 20.0, 'end_date' => '2024-12-25'],
            ['id' => 3, 'name' => 'Black Friday', 'discount' => 50.0, 'end_date' => '2024-11-30']
        ];

        $activeOffers = $this->getActiveLimitedTimeOffers($offers, '2024-12-20');
        $this->assertCount(2, $activeOffers);
        $this->assertEquals(1, $activeOffers[0]['id']); // Highest discount
    }

    #[Test]
    #[CoversNothing]
    public function it_identifies_price_drop_patterns(): void
    {
        $priceHistory = [
            ['date' => '2024-01-01', 'price' => 1000.00],
            ['date' => '2024-01-15', 'price' => 950.00],
            ['date' => '2024-02-01', 'price' => 900.00],
            ['date' => '2024-02-15', 'price' => 850.00],
            ['date' => '2024-03-01', 'price' => 800.00]
        ];

        $patterns = $this->identifyPriceDropPatterns($priceHistory);
        $this->assertCount(1, $patterns);
        $this->assertEquals('continuous_decline', $patterns[0]['pattern']);
    }

    private function detectPriceDrops(array $priceHistory, float $threshold): array
    {
        $priceDrops = [];

        for ($i = 1; $i < count($priceHistory); $i++) {
            $currentPrice = $priceHistory[$i]['price'];
            $previousPrice = $priceHistory[$i - 1]['price'];

            $dropPercentage = $this->calculatePriceDropPercentage($previousPrice, $currentPrice);

            if ($dropPercentage >= $threshold) {
                $priceDrops[] = [
                    'date' => $priceHistory[$i]['date'],
                    'old_price' => $previousPrice,
                    'new_price' => $currentPrice,
                    'drop_percentage' => $dropPercentage
                ];
            }
        }

        return $priceDrops;
    }

    private function detectSignificantPriceDrops(array $priceHistory, float $threshold): array
    {
        $priceDrops = $this->detectPriceDrops($priceHistory, $threshold);

        return array_filter($priceDrops, function ($drop) use ($threshold) {
            return $drop['drop_percentage'] >= $threshold;
        });
    }

    private function createPriceAlert(array $user, array $product, float $targetPrice): array
    {
        return [
            'user_id' => $user['id'],
            'product_id' => $product['id'],
            'target_price' => $targetPrice,
            'current_price' => $product['current_price'],
            'is_active' => true,
            'created_at' => date('Y-m-d H:i:s')
        ];
    }

    private function triggerPriceAlerts(array $alerts, array $currentPrices): array
    {
        $triggeredAlerts = [];

        foreach ($alerts as $alert) {
            if (!$alert['is_active']) {
                continue;
            }

            $productId = $alert['product_id'];
            $currentPrice = $currentPrices[$productId] ?? null;

            if ($currentPrice !== null && $currentPrice <= $alert['target_price']) {
                $triggeredAlerts[] = [
                    'alert_id' => $alert['id'],
                    'user_id' => $alert['user_id'],
                    'product_id' => $productId,
                    'target_price' => $alert['target_price'],
                    'current_price' => $currentPrice,
                    'triggered_at' => date('Y-m-d H:i:s')
                ];
            }
        }

        return $triggeredAlerts;
    }

    private function calculatePriceDropPercentage(float $oldPrice, float $newPrice): float
    {
        if ($oldPrice <= 0) {
            return 0.0;
        }

        return (($oldPrice - $newPrice) / $oldPrice) * 100;
    }

    private function getBestPriceDrops(array $priceDrops, int $limit): array
    {
        // Sort by drop percentage descending
        usort($priceDrops, function ($a, $b) {
            return $b['drop_percentage'] <=> $a['drop_percentage'];
        });

        return array_slice($priceDrops, 0, $limit);
    }

    private function getHistoricalLowPrices(array $priceHistory): array
    {
        $historicalLows = [];
        $minPrice = PHP_FLOAT_MAX;
        $minPriceDate = null;

        foreach ($priceHistory as $entry) {
            if ($entry['price'] < $minPrice) {
                $minPrice = $entry['price'];
                $minPriceDate = $entry['date'];
            }
        }

        if ($minPriceDate !== null) {
            $historicalLows[] = [
                'date' => $minPriceDate,
                'price' => $minPrice
            ];
        }

        return $historicalLows;
    }

    private function getTrendingDownProducts(array $products): array
    {
        $trendingDown = array_filter($products, function ($product) {
            return $product['trend'] === 'down';
        });

        // Sort by price change (most negative first)
        usort($trendingDown, function ($a, $b) {
            return $a['price_change'] <=> $b['price_change'];
        });

        return $trendingDown;
    }

    private function getSeasonalPriceDrops(array $products, string $season): array
    {
        $seasonalDrops = array_filter($products, function ($product) use ($season) {
            return $product['season'] === $season;
        });

        // Sort by price drop descending
        usort($seasonalDrops, function ($a, $b) {
            return $b['price_drop'] <=> $a['price_drop'];
        });

        return $seasonalDrops;
    }

    private function getClearancePriceDrops(array $products): array
    {
        $clearanceDrops = array_filter($products, function ($product) {
            return $product['is_clearance'] === true;
        });

        // Sort by price drop descending
        usort($clearanceDrops, function ($a, $b) {
            return $b['price_drop'] <=> $a['price_drop'];
        });

        return $clearanceDrops;
    }

    private function getActiveLimitedTimeOffers(array $offers, string $currentDate): array
    {
        $activeOffers = array_filter($offers, function ($offer) use ($currentDate) {
            return strtotime($offer['end_date']) >= strtotime($currentDate);
        });

        // Sort by discount descending
        usort($activeOffers, function ($a, $b) {
            return $b['discount'] <=> $a['discount'];
        });

        return $activeOffers;
    }

    private function identifyPriceDropPatterns(array $priceHistory): array
    {
        $patterns = [];
        $prices = array_column($priceHistory, 'price');

        // Check for continuous decline
        $isContinuousDecline = true;
        for ($i = 1; $i < count($prices); $i++) {
            if ($prices[$i] >= $prices[$i - 1]) {
                $isContinuousDecline = false;
                break;
            }
        }

        if ($isContinuousDecline) {
            $patterns[] = [
                'pattern' => 'continuous_decline',
                'description' => 'Price continuously declining over time'
            ];
        }

        return $patterns;
    }
}
