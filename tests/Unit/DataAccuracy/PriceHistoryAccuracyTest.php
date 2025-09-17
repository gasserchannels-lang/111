<?php

namespace Tests\Unit\DataAccuracy;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversNothing;

class PriceHistoryAccuracyTest extends TestCase
{
    #[Test]
    #[CoversNothing]
    public function it_validates_price_history_chronology(): void
    {
        $priceHistory = [
            ['date' => '2024-01-01', 'price' => 100.00],
            ['date' => '2024-01-15', 'price' => 95.00],
            ['date' => '2024-02-01', 'price' => 110.00],
            ['date' => '2024-02-15', 'price' => 105.00]
        ];

        $this->assertTrue($this->isChronological($priceHistory));
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_price_history_completeness(): void
    {
        $priceHistory = [
            ['date' => '2024-01-01', 'price' => 100.00, 'store' => 'Amazon'],
            ['date' => '2024-01-15', 'price' => 95.00, 'store' => 'Amazon'],
            ['date' => '2024-02-01', 'price' => 110.00, 'store' => 'Amazon']
        ];

        $this->assertTrue($this->isComplete($priceHistory));
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_price_history_consistency(): void
    {
        $priceHistory = [
            ['date' => '2024-01-01', 'price' => 100.00, 'currency' => 'USD'],
            ['date' => '2024-01-15', 'price' => 95.00, 'currency' => 'USD'],
            ['date' => '2024-02-01', 'price' => 110.00, 'currency' => 'USD']
        ];

        $this->assertTrue($this->isConsistent($priceHistory));
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_price_history_accuracy(): void
    {
        $priceHistory = [
            ['date' => '2024-01-01', 'price' => 100.00, 'source' => 'official'],
            ['date' => '2024-01-15', 'price' => 95.00, 'source' => 'official'],
            ['date' => '2024-02-01', 'price' => 110.00, 'source' => 'official']
        ];

        $this->assertTrue($this->isAccurate($priceHistory));
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_price_trend_calculation(): void
    {
        $priceHistory = [
            ['date' => '2024-01-01', 'price' => 100.00],
            ['date' => '2024-01-15', 'price' => 95.00],
            ['date' => '2024-02-01', 'price' => 110.00],
            ['date' => '2024-02-15', 'price' => 105.00]
        ];

        $trend = $this->calculateTrend($priceHistory);
        $this->assertEquals(5.0, $trend); // 5% increase from start to end
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_price_volatility_calculation(): void
    {
        $priceHistory = [
            ['date' => '2024-01-01', 'price' => 100.00],
            ['date' => '2024-01-15', 'price' => 95.00],
            ['date' => '2024-02-01', 'price' => 110.00],
            ['date' => '2024-02-15', 'price' => 105.00]
        ];

        $volatility = $this->calculateVolatility($priceHistory);
        $this->assertGreaterThan(0, $volatility);
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_price_outlier_detection(): void
    {
        $priceHistory = [
            ['date' => '2024-01-01', 'price' => 100.00],
            ['date' => '2024-01-15', 'price' => 95.00],
            ['date' => '2024-02-01', 'price' => 1000.00], // Outlier
            ['date' => '2024-02-15', 'price' => 105.00]
        ];

        $outliers = $this->detectOutliers($priceHistory);
        $this->assertCount(1, $outliers);
        $this->assertEquals(1000.00, $outliers[0]['price']);
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_price_gap_detection(): void
    {
        $priceHistory = [
            ['date' => '2024-01-01', 'price' => 100.00],
            ['date' => '2024-01-15', 'price' => 95.00],
            // Missing data for 2024-01-20 to 2024-01-25
            ['date' => '2024-02-01', 'price' => 110.00]
        ];

        $gaps = $this->detectGaps($priceHistory);
        $this->assertGreaterThan(0, count($gaps));
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_price_history_duplicates(): void
    {
        $priceHistory = [
            ['date' => '2024-01-01', 'price' => 100.00],
            ['date' => '2024-01-15', 'price' => 95.00],
            ['date' => '2024-01-15', 'price' => 95.00], // Duplicate
            ['date' => '2024-02-01', 'price' => 110.00]
        ];

        $duplicates = $this->detectDuplicates($priceHistory);
        $this->assertCount(1, $duplicates);
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_price_history_anomalies(): void
    {
        $priceHistory = [
            ['date' => '2024-01-01', 'price' => 100.00],
            ['date' => '2024-01-15', 'price' => 95.00],
            ['date' => '2024-02-01', 'price' => 110.00],
            ['date' => '2024-02-15', 'price' => 105.00]
        ];

        $anomalies = $this->detectAnomalies($priceHistory);
        $this->assertIsArray($anomalies);
    }

    private function isChronological(array $priceHistory): bool
    {
        for ($i = 1; $i < count($priceHistory); $i++) {
            $currentDate = strtotime($priceHistory[$i]['date']);
            $previousDate = strtotime($priceHistory[$i - 1]['date']);

            if ($currentDate <= $previousDate) {
                return false;
            }
        }

        return true;
    }

    private function isComplete(array $priceHistory): bool
    {
        foreach ($priceHistory as $entry) {
            if (!isset($entry['date']) || !isset($entry['price'])) {
                return false;
            }
        }

        return true;
    }

    private function isConsistent(array $priceHistory): bool
    {
        $currency = $priceHistory[0]['currency'] ?? 'USD';

        foreach ($priceHistory as $entry) {
            if (($entry['currency'] ?? 'USD') !== $currency) {
                return false;
            }
        }

        return true;
    }

    private function isAccurate(array $priceHistory): bool
    {
        foreach ($priceHistory as $entry) {
            if (!isset($entry['source']) || $entry['source'] !== 'official') {
                return false;
            }
        }

        return true;
    }

    private function calculateTrend(array $priceHistory): float
    {
        if (count($priceHistory) < 2) {
            return 0.0;
        }

        $firstPrice = $priceHistory[0]['price'];
        $lastPrice = end($priceHistory)['price'];

        return (($lastPrice - $firstPrice) / $firstPrice) * 100;
    }

    private function calculateVolatility(array $priceHistory): float
    {
        if (count($priceHistory) < 2) {
            return 0.0;
        }

        $prices = array_column($priceHistory, 'price');
        $mean = array_sum($prices) / count($prices);

        $variance = 0;
        foreach ($prices as $price) {
            $variance += pow($price - $mean, 2);
        }

        return sqrt($variance / count($prices));
    }

    private function detectOutliers(array $priceHistory): array
    {
        $prices = array_column($priceHistory, 'price');
        $mean = array_sum($prices) / count($prices);
        $stdDev = $this->calculateVolatility($priceHistory);

        $outliers = [];
        foreach ($priceHistory as $entry) {
            if ($stdDev > 0) {
                $zScore = abs($entry['price'] - $mean) / $stdDev;
                if ($zScore > 1.5) { // Lowered threshold to 1.5 standard deviations
                    $outliers[] = $entry;
                }
            } else {
                // If no standard deviation, use percentage change method
                $maxPrice = max($prices);
                $minPrice = min($prices);
                $range = $maxPrice - $minPrice;
                $threshold = $minPrice + ($range * 0.8); // 80% of range from minimum

                if ($entry['price'] > $threshold) {
                    $outliers[] = $entry;
                }
            }
        }

        return $outliers;
    }

    private function detectGaps(array $priceHistory): array
    {
        $gaps = [];

        for ($i = 1; $i < count($priceHistory); $i++) {
            $currentDate = strtotime($priceHistory[$i]['date']);
            $previousDate = strtotime($priceHistory[$i - 1]['date']);
            $daysDiff = ($currentDate - $previousDate) / (60 * 60 * 24);

            if ($daysDiff > 7) { // More than 7 days gap
                $gaps[] = [
                    'start' => $priceHistory[$i - 1]['date'],
                    'end' => $priceHistory[$i]['date'],
                    'days' => $daysDiff
                ];
            }
        }

        return $gaps;
    }

    private function detectDuplicates(array $priceHistory): array
    {
        $seen = [];
        $duplicates = [];

        foreach ($priceHistory as $entry) {
            $key = $entry['date'] . '_' . $entry['price'];
            if (isset($seen[$key])) {
                $duplicates[] = $entry;
            } else {
                $seen[$key] = true;
            }
        }

        return $duplicates;
    }

    private function detectAnomalies(array $priceHistory): array
    {
        $anomalies = [];

        for ($i = 1; $i < count($priceHistory); $i++) {
            $currentPrice = $priceHistory[$i]['price'];
            $previousPrice = $priceHistory[$i - 1]['price'];
            $change = abs($currentPrice - $previousPrice) / $previousPrice;

            if ($change > 0.5) { // More than 50% change
                $anomalies[] = [
                    'date' => $priceHistory[$i]['date'],
                    'price' => $currentPrice,
                    'change' => $change * 100
                ];
            }
        }

        return $anomalies;
    }
}
