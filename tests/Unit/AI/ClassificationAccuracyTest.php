<?php

namespace Tests\Unit\AI;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ClassificationAccuracyTest extends TestCase
{
    #[Test]
    #[CoversNothing]
    public function it_validates_product_classification_accuracy(): void
    {
        $classifications = [
            ['actual' => 'Electronics', 'predicted' => 'Electronics'],
            ['actual' => 'Clothing', 'predicted' => 'Clothing'],
            ['actual' => 'Books', 'predicted' => 'Books'],
            ['actual' => 'Home', 'predicted' => 'Electronics'],
        ];

        $accuracy = $this->calculateClassificationAccuracy($classifications);
        $this->assertEquals(0.75, $accuracy); // 3 out of 4 correct
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_sentiment_classification_accuracy(): void
    {
        $sentiments = [
            ['actual' => 'positive', 'predicted' => 'positive'],
            ['actual' => 'negative', 'predicted' => 'negative'],
            ['actual' => 'neutral', 'predicted' => 'neutral'],
            ['actual' => 'positive', 'predicted' => 'negative'],
        ];

        $accuracy = $this->calculateClassificationAccuracy($sentiments);
        $this->assertEquals(0.75, $accuracy); // 3 out of 4 correct
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_brand_classification_accuracy(): void
    {
        $brands = [
            ['actual' => 'Apple', 'predicted' => 'Apple'],
            ['actual' => 'Samsung', 'predicted' => 'Samsung'],
            ['actual' => 'Google', 'predicted' => 'Google'],
            ['actual' => 'Apple', 'predicted' => 'Samsung'],
        ];

        $accuracy = $this->calculateClassificationAccuracy($brands);
        $this->assertEquals(0.75, $accuracy); // 3 out of 4 correct
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_price_range_classification_accuracy(): void
    {
        $priceRanges = [
            ['actual' => 'budget', 'predicted' => 'budget'],
            ['actual' => 'mid-range', 'predicted' => 'mid-range'],
            ['actual' => 'premium', 'predicted' => 'premium'],
            ['actual' => 'luxury', 'predicted' => 'premium'],
        ];

        $accuracy = $this->calculateClassificationAccuracy($priceRanges);
        $this->assertEquals(0.75, $accuracy); // 3 out of 4 correct
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_quality_classification_accuracy(): void
    {
        $qualities = [
            ['actual' => 'high', 'predicted' => 'high'],
            ['actual' => 'medium', 'predicted' => 'medium'],
            ['actual' => 'low', 'predicted' => 'low'],
            ['actual' => 'high', 'predicted' => 'medium'],
        ];

        $accuracy = $this->calculateClassificationAccuracy($qualities);
        $this->assertEquals(0.75, $accuracy); // 3 out of 4 correct
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_urgency_classification_accuracy(): void
    {
        $urgencies = [
            ['actual' => 'urgent', 'predicted' => 'urgent'],
            ['actual' => 'normal', 'predicted' => 'normal'],
            ['actual' => 'low', 'predicted' => 'low'],
            ['actual' => 'urgent', 'predicted' => 'normal'],
        ];

        $accuracy = $this->calculateClassificationAccuracy($urgencies);
        $this->assertEquals(0.75, $accuracy); // 3 out of 4 correct
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_priority_classification_accuracy(): void
    {
        $priorities = [
            ['actual' => 'high', 'predicted' => 'high'],
            ['actual' => 'medium', 'predicted' => 'medium'],
            ['actual' => 'low', 'predicted' => 'low'],
            ['actual' => 'high', 'predicted' => 'low'],
        ];

        $accuracy = $this->calculateClassificationAccuracy($priorities);
        $this->assertEquals(0.75, $accuracy); // 3 out of 4 correct
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_risk_classification_accuracy(): void
    {
        $risks = [
            ['actual' => 'low', 'predicted' => 'low'],
            ['actual' => 'medium', 'predicted' => 'medium'],
            ['actual' => 'high', 'predicted' => 'high'],
            ['actual' => 'low', 'predicted' => 'high'],
        ];

        $accuracy = $this->calculateClassificationAccuracy($risks);
        $this->assertEquals(0.75, $accuracy); // 3 out of 4 correct
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_complexity_classification_accuracy(): void
    {
        $complexities = [
            ['actual' => 'simple', 'predicted' => 'simple'],
            ['actual' => 'moderate', 'predicted' => 'moderate'],
            ['actual' => 'complex', 'predicted' => 'complex'],
            ['actual' => 'simple', 'predicted' => 'complex'],
        ];

        $accuracy = $this->calculateClassificationAccuracy($complexities);
        $this->assertEquals(0.75, $accuracy); // 3 out of 4 correct
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_multi_class_classification_accuracy(): void
    {
        $multiClass = [
            ['actual' => 'A', 'predicted' => 'A'],
            ['actual' => 'B', 'predicted' => 'B'],
            ['actual' => 'C', 'predicted' => 'C'],
            ['actual' => 'D', 'predicted' => 'D'],
            ['actual' => 'A', 'predicted' => 'B'],
        ];

        $accuracy = $this->calculateClassificationAccuracy($multiClass);
        $this->assertEquals(0.8, $accuracy); // 4 out of 5 correct
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_binary_classification_accuracy(): void
    {
        $binary = [
            ['actual' => 'yes', 'predicted' => 'yes'],
            ['actual' => 'no', 'predicted' => 'no'],
            ['actual' => 'yes', 'predicted' => 'no'],
            ['actual' => 'no', 'predicted' => 'yes'],
        ];

        $accuracy = $this->calculateClassificationAccuracy($binary);
        $this->assertEquals(0.5, $accuracy); // 2 out of 4 correct
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_confidence_scores(): void
    {
        $classifications = [
            ['actual' => 'Electronics', 'predicted' => 'Electronics', 'confidence' => 0.95],
            ['actual' => 'Clothing', 'predicted' => 'Clothing', 'confidence' => 0.87],
            ['actual' => 'Books', 'predicted' => 'Electronics', 'confidence' => 0.45],
            ['actual' => 'Home', 'predicted' => 'Home', 'confidence' => 0.92],
        ];

        $avgConfidence = $this->calculateAverageConfidence($classifications);
        $this->assertGreaterThan(0.7, $avgConfidence);
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_classification_precision(): void
    {
        $classifications = [
            ['actual' => 'Electronics', 'predicted' => 'Electronics'],
            ['actual' => 'Clothing', 'predicted' => 'Electronics'],
            ['actual' => 'Electronics', 'predicted' => 'Electronics'],
            ['actual' => 'Books', 'predicted' => 'Electronics'],
        ];

        $precision = $this->calculatePrecision($classifications, 'Electronics');
        $this->assertEquals(0.5, $precision); // 2 out of 4 Electronics predictions correct
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_classification_recall(): void
    {
        $classifications = [
            ['actual' => 'Electronics', 'predicted' => 'Electronics'],
            ['actual' => 'Electronics', 'predicted' => 'Clothing'],
            ['actual' => 'Electronics', 'predicted' => 'Electronics'],
            ['actual' => 'Books', 'predicted' => 'Books'],
        ];

        $recall = $this->calculateRecall($classifications, 'Electronics');
        $this->assertEquals(0.67, round($recall, 2)); // 2 out of 3 actual Electronics predicted correctly
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_classification_f1_score(): void
    {
        $classifications = [
            ['actual' => 'Electronics', 'predicted' => 'Electronics'],
            ['actual' => 'Clothing', 'predicted' => 'Electronics'],
            ['actual' => 'Electronics', 'predicted' => 'Electronics'],
            ['actual' => 'Books', 'predicted' => 'Books'],
        ];

        $f1Score = $this->calculateF1Score($classifications, 'Electronics');
        $this->assertEquals(0.8, round($f1Score, 2)); // Harmonic mean of precision and recall
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_confusion_matrix(): void
    {
        $classifications = [
            ['actual' => 'Electronics', 'predicted' => 'Electronics'],
            ['actual' => 'Clothing', 'predicted' => 'Electronics'],
            ['actual' => 'Electronics', 'predicted' => 'Clothing'],
            ['actual' => 'Books', 'predicted' => 'Books'],
        ];

        $confusionMatrix = $this->calculateConfusionMatrix($classifications);
        $this->assertArrayHasKey('Electronics', $confusionMatrix);
        $this->assertArrayHasKey('Clothing', $confusionMatrix);
        $this->assertArrayHasKey('Books', $confusionMatrix);
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_class_balance(): void
    {
        $classifications = [
            ['actual' => 'Electronics', 'predicted' => 'Electronics'],
            ['actual' => 'Electronics', 'predicted' => 'Electronics'],
            ['actual' => 'Clothing', 'predicted' => 'Clothing'],
            ['actual' => 'Books', 'predicted' => 'Books'],
        ];

        $classBalance = $this->calculateClassBalance($classifications);
        $this->assertArrayHasKey('Electronics', $classBalance);
        $this->assertArrayHasKey('Clothing', $classBalance);
        $this->assertArrayHasKey('Books', $classBalance);
    }

    private function calculateClassificationAccuracy(array $classifications): float
    {
        $correct = 0;
        $total = count($classifications);

        foreach ($classifications as $classification) {
            if ($classification['actual'] === $classification['predicted']) {
                $correct++;
            }
        }

        return $correct / $total;
    }

    private function calculateAverageConfidence(array $classifications): float
    {
        $totalConfidence = 0;
        $count = count($classifications);

        foreach ($classifications as $classification) {
            $totalConfidence += $classification['confidence'] ?? 0;
        }

        return $totalConfidence / $count;
    }

    private function calculatePrecision(array $classifications, string $class): float
    {
        $truePositives = 0;
        $falsePositives = 0;

        foreach ($classifications as $classification) {
            if ($classification['predicted'] === $class) {
                if ($classification['actual'] === $class) {
                    $truePositives++;
                } else {
                    $falsePositives++;
                }
            }
        }

        $totalPredictions = $truePositives + $falsePositives;

        return $totalPredictions > 0 ? $truePositives / $totalPredictions : 0;
    }

    private function calculateRecall(array $classifications, string $class): float
    {
        $truePositives = 0;
        $falseNegatives = 0;

        foreach ($classifications as $classification) {
            if ($classification['actual'] === $class) {
                if ($classification['predicted'] === $class) {
                    $truePositives++;
                } else {
                    $falseNegatives++;
                }
            }
        }

        $totalActual = $truePositives + $falseNegatives;

        return $totalActual > 0 ? $truePositives / $totalActual : 0;
    }

    private function calculateF1Score(array $classifications, string $class): float
    {
        $precision = $this->calculatePrecision($classifications, $class);
        $recall = $this->calculateRecall($classifications, $class);

        if ($precision + $recall === 0) {
            return 0;
        }

        return 2 * ($precision * $recall) / ($precision + $recall);
    }

    private function calculateConfusionMatrix(array $classifications): array
    {
        $classes = array_unique(array_merge(
            array_column($classifications, 'actual'),
            array_column($classifications, 'predicted')
        ));

        $matrix = [];
        foreach ($classes as $actualClass) {
            $matrix[$actualClass] = [];
            foreach ($classes as $predictedClass) {
                $matrix[$actualClass][$predictedClass] = 0;
            }
        }

        foreach ($classifications as $classification) {
            $actual = $classification['actual'];
            $predicted = $classification['predicted'];
            $matrix[$actual][$predicted]++;
        }

        return $matrix;
    }

    private function calculateClassBalance(array $classifications): array
    {
        $classCounts = [];
        $total = count($classifications);

        foreach ($classifications as $classification) {
            $actual = $classification['actual'];
            if (! isset($classCounts[$actual])) {
                $classCounts[$actual] = 0;
            }
            $classCounts[$actual]++;
        }

        $balance = [];
        foreach ($classCounts as $class => $count) {
            $balance[$class] = $count / $total;
        }

        return $balance;
    }
}
