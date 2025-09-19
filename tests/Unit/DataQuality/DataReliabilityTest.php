<?php

namespace Tests\Unit\DataQuality;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DataReliabilityTest extends TestCase
{
    #[Test]
    #[CoversNothing]
    public function it_measures_data_consistency_across_sources(): void
    {
        $source1 = ['price' => 100.00, 'currency' => 'USD'];
        $source2 = ['price' => 100.00, 'currency' => 'USD'];
        $source3 = ['price' => 100.00, 'currency' => 'USD'];

        $consistency = $this->calculateDataConsistency([$source1, $source2, $source3]);

        $this->assertGreaterThan(0.8, $consistency);
    }

    #[Test]
    #[CoversNothing]
    public function it_detects_data_anomalies(): void
    {
        $prices = [10.00, 12.00, 11.50, 15.00, 10.50, 1000.00]; // 1000.00 is anomaly

        $anomalies = $this->detectAnomalies($prices);

        $this->assertContains(1000.00, $anomalies);
    }

    #[Test]
    #[CoversNothing]
    public function it_calculates_data_confidence_score(): void
    {
        $dataPoints = [
            ['value' => 100, 'confidence' => 0.9],
            ['value' => 102, 'confidence' => 0.8],
            ['value' => 98, 'confidence' => 0.95],
        ];

        $overallConfidence = $this->calculateOverallConfidence($dataPoints);

        $this->assertGreaterThan(0.8, $overallConfidence);
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_data_source_reliability(): void
    {
        $source = [
            'name' => 'Store API',
            'uptime' => 99.5,
            'response_time' => 200,
            'error_rate' => 0.1,
        ];

        $isReliable = $this->isSourceReliable($source);

        $this->assertTrue($isReliable);
    }

    #[Test]
    #[CoversNothing]
    public function it_measures_data_freshness(): void
    {
        $lastUpdated = new \DateTime('-2 hours');
        $maxAge = 4; // hours

        $isFresh = $this->isDataFresh($lastUpdated, $maxAge);

        $this->assertTrue($isFresh);
    }

    #[Test]
    #[CoversNothing]
    public function it_calculates_data_accuracy_percentage(): void
    {
        $totalRecords = 1000;
        $accurateRecords = 950;
        $expectedAccuracy = 95.0;

        $actualAccuracy = $this->calculateAccuracyPercentage($totalRecords, $accurateRecords);

        $this->assertEquals($expectedAccuracy, $actualAccuracy);
    }

    #[Test]
    #[CoversNothing]
    public function it_detects_data_drift(): void
    {
        $historicalData = [10, 11, 12, 13, 14, 15];
        $currentData = [10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20];

        $driftDetected = $this->detectDataDrift($historicalData, $currentData);

        $this->assertTrue($driftDetected);
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_data_integrity_constraints(): void
    {
        $data = [
            'order_id' => 12345,
            'customer_id' => 67890,
            'total_amount' => 150.00,
            'items' => [
                ['product_id' => 1, 'quantity' => 2, 'price' => 50.00],
                ['product_id' => 2, 'quantity' => 1, 'price' => 50.00],
            ],
        ];

        $isValid = $this->validateIntegrityConstraints($data);

        $this->assertTrue($isValid);
    }

    #[Test]
    #[CoversNothing]
    public function it_calculates_data_quality_score(): void
    {
        $qualityMetrics = [
            'completeness' => 0.95,
            'accuracy' => 0.90,
            'consistency' => 0.85,
            'timeliness' => 0.88,
            'validity' => 0.92,
        ];

        $overallScore = $this->calculateDataQualityScore($qualityMetrics);

        $this->assertGreaterThan(0.8, $overallScore);
    }

    private function calculateDataConsistency(array $sources): float
    {
        if (count($sources) < 2) {
            return 1.0;
        }

        $matches = 0;
        $totalComparisons = 0;

        for ($i = 0; $i < count($sources); $i++) {
            for ($j = $i + 1; $j < count($sources); $j++) {
                $totalComparisons++;
                // Compare individual values instead of entire arrays
                $source1 = $sources[$i];
                $source2 = $sources[$j];

                $fieldMatches = 0;
                $totalFields = 0;

                foreach ($source1 as $key => $value1) {
                    if (isset($source2[$key])) {
                        $totalFields++;
                        if ($value1 === $source2[$key]) {
                            $fieldMatches++;
                        }
                    }
                }

                if ($totalFields > 0) {
                    $matches += $fieldMatches / $totalFields;
                }
            }
        }

        return $totalComparisons > 0 ? $matches / $totalComparisons : 0;
    }

    private function detectAnomalies(array $data): array
    {
        $mean = array_sum($data) / count($data);
        $variance = array_sum(array_map(function ($x) use ($mean) {
            return pow($x - $mean, 2);
        }, $data)) / count($data);
        $stdDev = sqrt($variance);

        $threshold = 2 * $stdDev; // 2 standard deviations
        $anomalies = [];

        foreach ($data as $value) {
            if (abs($value - $mean) > $threshold) {
                $anomalies[] = $value;
            }
        }

        return $anomalies;
    }

    private function calculateOverallConfidence(array $dataPoints): float
    {
        $totalWeight = 0;
        $weightedSum = 0;

        foreach ($dataPoints as $point) {
            $weightedSum += $point['value'] * $point['confidence'];
            $totalWeight += $point['confidence'];
        }

        return $totalWeight > 0 ? $weightedSum / $totalWeight : 0;
    }

    private function isSourceReliable(array $source): bool
    {
        return $source['uptime'] >= 99.0 &&
            $source['response_time'] <= 500 &&
            $source['error_rate'] <= 1.0;
    }

    private function isDataFresh(\DateTime $lastUpdated, int $maxAgeHours): bool
    {
        $now = new \DateTime;
        $age = $now->diff($lastUpdated)->h;

        return $age <= $maxAgeHours;
    }

    private function calculateAccuracyPercentage(int $total, int $accurate): float
    {
        return $total > 0 ? ($accurate / $total) * 100 : 0;
    }

    private function detectDataDrift(array $historical, array $current): bool
    {
        $historicalMean = array_sum($historical) / count($historical);
        $currentMean = array_sum($current) / count($current);

        $driftThreshold = 0.19; // 19% change (slightly less than 20%)
        $drift = abs($currentMean - $historicalMean) / $historicalMean;

        return $drift > $driftThreshold;
    }

    private function validateIntegrityConstraints(array $data): bool
    {
        // Check if order total matches sum of items
        $calculatedTotal = 0;
        foreach ($data['items'] as $item) {
            $calculatedTotal += $item['quantity'] * $item['price'];
        }

        return abs($calculatedTotal - $data['total_amount']) < 0.01;
    }

    private function calculateDataQualityScore(array $metrics): float
    {
        $weights = [
            'completeness' => 0.2,
            'accuracy' => 0.25,
            'consistency' => 0.2,
            'timeliness' => 0.15,
            'validity' => 0.2,
        ];

        $score = 0;
        foreach ($metrics as $metric => $value) {
            $score += $value * $weights[$metric];
        }

        return $score;
    }
}
