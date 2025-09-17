<?php

namespace Tests\Unit\AI;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class PredictionAccuracyTest extends TestCase
{
    #[Test]
    public function it_validates_price_prediction_accuracy(): void
    {
        $predictions = [
            ['actual' => 999.00, 'predicted' => 950.00],
            ['actual' => 899.00, 'predicted' => 920.00],
            ['actual' => 799.00, 'predicted' => 780.00],
            ['actual' => 699.00, 'predicted' => 720.00]
        ];

        $accuracy = $this->calculatePredictionAccuracy($predictions);
        $this->assertGreaterThan(0.8, $accuracy);
    }

    #[Test]
    public function it_validates_demand_prediction_accuracy(): void
    {
        $predictions = [
            ['actual' => 100, 'predicted' => 95],
            ['actual' => 150, 'predicted' => 160],
            ['actual' => 200, 'predicted' => 190],
            ['actual' => 80, 'predicted' => 85]
        ];

        $accuracy = $this->calculatePredictionAccuracy($predictions);
        $this->assertGreaterThan(0.85, $accuracy);
    }

    #[Test]
    public function it_validates_trend_prediction_accuracy(): void
    {
        $trends = [
            ['actual' => 'up', 'predicted' => 'up'],
            ['actual' => 'down', 'predicted' => 'down'],
            ['actual' => 'stable', 'predicted' => 'stable'],
            ['actual' => 'up', 'predicted' => 'down'],
            ['actual' => 'down', 'predicted' => 'up']
        ];

        $accuracy = $this->calculateTrendPredictionAccuracy($trends);
        $this->assertEquals(0.6, $accuracy); // 3 out of 5 correct
    }

    #[Test]
    public function it_validates_seasonal_prediction_accuracy(): void
    {
        $seasonalPredictions = [
            ['actual' => 'winter', 'predicted' => 'winter'],
            ['actual' => 'spring', 'predicted' => 'spring'],
            ['actual' => 'summer', 'predicted' => 'summer'],
            ['actual' => 'fall', 'predicted' => 'winter']
        ];

        $accuracy = $this->calculateSeasonalPredictionAccuracy($seasonalPredictions);
        $this->assertEquals(0.75, $accuracy); // 3 out of 4 correct
    }

    #[Test]
    public function it_validates_category_prediction_accuracy(): void
    {
        $categoryPredictions = [
            ['actual' => 'Electronics', 'predicted' => 'Electronics'],
            ['actual' => 'Clothing', 'predicted' => 'Clothing'],
            ['actual' => 'Books', 'predicted' => 'Books'],
            ['actual' => 'Home', 'predicted' => 'Electronics']
        ];

        $accuracy = $this->calculateCategoryPredictionAccuracy($categoryPredictions);
        $this->assertEquals(0.75, $accuracy); // 3 out of 4 correct
    }

    #[Test]
    public function it_validates_rating_prediction_accuracy(): void
    {
        $ratingPredictions = [
            ['actual' => 4.5, 'predicted' => 4.3],
            ['actual' => 3.8, 'predicted' => 3.9],
            ['actual' => 4.2, 'predicted' => 4.0],
            ['actual' => 3.5, 'predicted' => 3.7]
        ];

        $accuracy = $this->calculateRatingPredictionAccuracy($ratingPredictions);
        $this->assertGreaterThan(0.8, $accuracy);
    }

    #[Test]
    public function it_validates_sales_prediction_accuracy(): void
    {
        $salesPredictions = [
            ['actual' => 1000, 'predicted' => 950],
            ['actual' => 1500, 'predicted' => 1600],
            ['actual' => 800, 'predicted' => 820],
            ['actual' => 1200, 'predicted' => 1150]
        ];

        $accuracy = $this->calculateSalesPredictionAccuracy($salesPredictions);
        $this->assertGreaterThan(0.85, $accuracy);
    }

    #[Test]
    public function it_validates_stock_prediction_accuracy(): void
    {
        $stockPredictions = [
            ['actual' => 50, 'predicted' => 45],
            ['actual' => 30, 'predicted' => 35],
            ['actual' => 75, 'predicted' => 70],
            ['actual' => 20, 'predicted' => 25]
        ];

        $accuracy = $this->calculateStockPredictionAccuracy($stockPredictions);
        $this->assertGreaterThan(0.8, $accuracy);
    }

    #[Test]
    public function it_validates_revenue_prediction_accuracy(): void
    {
        $revenuePredictions = [
            ['actual' => 10000.00, 'predicted' => 9500.00],
            ['actual' => 15000.00, 'predicted' => 16000.00],
            ['actual' => 8000.00, 'predicted' => 8200.00],
            ['actual' => 12000.00, 'predicted' => 11500.00]
        ];

        $accuracy = $this->calculateRevenuePredictionAccuracy($revenuePredictions);
        $this->assertGreaterThan(0.85, $accuracy);
    }

    #[Test]
    public function it_validates_customer_behavior_prediction_accuracy(): void
    {
        $behaviorPredictions = [
            ['actual' => 'buy', 'predicted' => 'buy'],
            ['actual' => 'browse', 'predicted' => 'browse'],
            ['actual' => 'buy', 'predicted' => 'browse'],
            ['actual' => 'browse', 'predicted' => 'buy']
        ];

        $accuracy = $this->calculateBehaviorPredictionAccuracy($behaviorPredictions);
        $this->assertEquals(0.5, $accuracy); // 2 out of 4 correct
    }

    #[Test]
    public function it_validates_market_trend_prediction_accuracy(): void
    {
        $marketPredictions = [
            ['actual' => 'bullish', 'predicted' => 'bullish'],
            ['actual' => 'bearish', 'predicted' => 'bearish'],
            ['actual' => 'neutral', 'predicted' => 'neutral'],
            ['actual' => 'bullish', 'predicted' => 'bearish']
        ];

        $accuracy = $this->calculateMarketTrendPredictionAccuracy($marketPredictions);
        $this->assertEquals(0.75, $accuracy); // 3 out of 4 correct
    }

    #[Test]
    public function it_validates_competitor_analysis_prediction_accuracy(): void
    {
        $competitorPredictions = [
            ['actual' => 'high', 'predicted' => 'high'],
            ['actual' => 'medium', 'predicted' => 'medium'],
            ['actual' => 'low', 'predicted' => 'low'],
            ['actual' => 'high', 'predicted' => 'medium']
        ];

        $accuracy = $this->calculateCompetitorAnalysisPredictionAccuracy($competitorPredictions);
        $this->assertEquals(0.75, $accuracy); // 3 out of 4 correct
    }

    #[Test]
    public function it_validates_forecasting_accuracy(): void
    {
        $forecastData = [
            ['period' => 1, 'actual' => 100, 'predicted' => 95],
            ['period' => 2, 'actual' => 110, 'predicted' => 105],
            ['period' => 3, 'actual' => 120, 'predicted' => 115],
            ['period' => 4, 'actual' => 130, 'predicted' => 125]
        ];

        $accuracy = $this->calculateForecastingAccuracy($forecastData);
        $this->assertGreaterThan(0.9, $accuracy);
    }

    #[Test]
    public function it_validates_anomaly_detection_accuracy(): void
    {
        $anomalyData = [
            ['actual' => 'normal', 'predicted' => 'normal'],
            ['actual' => 'anomaly', 'predicted' => 'anomaly'],
            ['actual' => 'normal', 'predicted' => 'normal'],
            ['actual' => 'anomaly', 'predicted' => 'normal'],
            ['actual' => 'normal', 'predicted' => 'anomaly']
        ];

        $accuracy = $this->calculateAnomalyDetectionAccuracy($anomalyData);
        $this->assertEquals(0.6, $accuracy); // 3 out of 5 correct
    }

    #[Test]
    public function it_validates_recommendation_accuracy(): void
    {
        $recommendations = [
            ['actual' => 'relevant', 'predicted' => 'relevant'],
            ['actual' => 'irrelevant', 'predicted' => 'irrelevant'],
            ['actual' => 'relevant', 'predicted' => 'relevant'],
            ['actual' => 'irrelevant', 'predicted' => 'relevant']
        ];

        $accuracy = $this->calculateRecommendationAccuracy($recommendations);
        $this->assertEquals(0.75, $accuracy); // 3 out of 4 correct
    }

    private function calculatePredictionAccuracy(array $predictions): float
    {
        $totalError = 0;
        $count = count($predictions);

        foreach ($predictions as $prediction) {
            $actual = $prediction['actual'];
            $predicted = $prediction['predicted'];
            $error = abs($actual - $predicted) / $actual;
            $totalError += $error;
        }

        $averageError = $totalError / $count;
        return 1 - $averageError; // Convert error to accuracy
    }

    private function calculateTrendPredictionAccuracy(array $trends): float
    {
        $correct = 0;
        $total = count($trends);

        foreach ($trends as $trend) {
            if ($trend['actual'] === $trend['predicted']) {
                $correct++;
            }
        }

        return $correct / $total;
    }

    private function calculateSeasonalPredictionAccuracy(array $predictions): float
    {
        $correct = 0;
        $total = count($predictions);

        foreach ($predictions as $prediction) {
            if ($prediction['actual'] === $prediction['predicted']) {
                $correct++;
            }
        }

        return $correct / $total;
    }

    private function calculateCategoryPredictionAccuracy(array $predictions): float
    {
        $correct = 0;
        $total = count($predictions);

        foreach ($predictions as $prediction) {
            if ($prediction['actual'] === $prediction['predicted']) {
                $correct++;
            }
        }

        return $correct / $total;
    }

    private function calculateRatingPredictionAccuracy(array $predictions): float
    {
        $totalError = 0;
        $count = count($predictions);

        foreach ($predictions as $prediction) {
            $actual = $prediction['actual'];
            $predicted = $prediction['predicted'];
            $error = abs($actual - $predicted) / 5.0; // Normalize by max rating
            $totalError += $error;
        }

        $averageError = $totalError / $count;
        return 1 - $averageError;
    }

    private function calculateSalesPredictionAccuracy(array $predictions): float
    {
        $totalError = 0;
        $count = count($predictions);

        foreach ($predictions as $prediction) {
            $actual = $prediction['actual'];
            $predicted = $prediction['predicted'];
            $error = abs($actual - $predicted) / $actual;
            $totalError += $error;
        }

        $averageError = $totalError / $count;
        return 1 - $averageError;
    }

    private function calculateStockPredictionAccuracy(array $predictions): float
    {
        $totalError = 0;
        $count = count($predictions);

        foreach ($predictions as $prediction) {
            $actual = $prediction['actual'];
            $predicted = $prediction['predicted'];
            $error = abs($actual - $predicted) / $actual;
            $totalError += $error;
        }

        $averageError = $totalError / $count;
        return 1 - $averageError;
    }

    private function calculateRevenuePredictionAccuracy(array $predictions): float
    {
        $totalError = 0;
        $count = count($predictions);

        foreach ($predictions as $prediction) {
            $actual = $prediction['actual'];
            $predicted = $prediction['predicted'];
            $error = abs($actual - $predicted) / $actual;
            $totalError += $error;
        }

        $averageError = $totalError / $count;
        return 1 - $averageError;
    }

    private function calculateBehaviorPredictionAccuracy(array $predictions): float
    {
        $correct = 0;
        $total = count($predictions);

        foreach ($predictions as $prediction) {
            if ($prediction['actual'] === $prediction['predicted']) {
                $correct++;
            }
        }

        return $correct / $total;
    }

    private function calculateMarketTrendPredictionAccuracy(array $predictions): float
    {
        $correct = 0;
        $total = count($predictions);

        foreach ($predictions as $prediction) {
            if ($prediction['actual'] === $prediction['predicted']) {
                $correct++;
            }
        }

        return $correct / $total;
    }

    private function calculateCompetitorAnalysisPredictionAccuracy(array $predictions): float
    {
        $correct = 0;
        $total = count($predictions);

        foreach ($predictions as $prediction) {
            if ($prediction['actual'] === $prediction['predicted']) {
                $correct++;
            }
        }

        return $correct / $total;
    }

    private function calculateForecastingAccuracy(array $forecastData): float
    {
        $totalError = 0;
        $count = count($forecastData);

        foreach ($forecastData as $data) {
            $actual = $data['actual'];
            $predicted = $data['predicted'];
            $error = abs($actual - $predicted) / $actual;
            $totalError += $error;
        }

        $averageError = $totalError / $count;
        return 1 - $averageError;
    }

    private function calculateAnomalyDetectionAccuracy(array $anomalyData): float
    {
        $correct = 0;
        $total = count($anomalyData);

        foreach ($anomalyData as $data) {
            if ($data['actual'] === $data['predicted']) {
                $correct++;
            }
        }

        return $correct / $total;
    }

    private function calculateRecommendationAccuracy(array $recommendations): float
    {
        $correct = 0;
        $total = count($recommendations);

        foreach ($recommendations as $recommendation) {
            if ($recommendation['actual'] === $recommendation['predicted']) {
                $correct++;
            }
        }

        return $correct / $total;
    }
}
