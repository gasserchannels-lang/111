<?php

namespace Tests\Unit\AI;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MachineLearningAccuracyTest extends TestCase
{
    #[Test]
    #[CoversNothing]
    public function it_validates_model_accuracy(): void
    {
        $predictions = [
            ['actual' => 1, 'predicted' => 1],
            ['actual' => 0, 'predicted' => 0],
            ['actual' => 1, 'predicted' => 1],
            ['actual' => 0, 'predicted' => 1],
            ['actual' => 1, 'predicted' => 0],
        ];

        $accuracy = $this->calculateAccuracy($predictions);
        $this->assertEquals(0.6, $accuracy); // 3 out of 5 correct
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_precision_score(): void
    {
        $predictions = [
            ['actual' => 1, 'predicted' => 1],
            ['actual' => 0, 'predicted' => 1],
            ['actual' => 1, 'predicted' => 1],
            ['actual' => 0, 'predicted' => 0],
            ['actual' => 1, 'predicted' => 1],
        ];

        $precision = $this->calculatePrecision($predictions);
        $this->assertEquals(0.75, $precision); // 3 out of 4 positive predictions correct
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_recall_score(): void
    {
        $predictions = [
            ['actual' => 1, 'predicted' => 1],
            ['actual' => 1, 'predicted' => 0],
            ['actual' => 1, 'predicted' => 1],
            ['actual' => 0, 'predicted' => 0],
            ['actual' => 1, 'predicted' => 1],
        ];

        $recall = $this->calculateRecall($predictions);
        $this->assertEquals(0.75, $recall); // 3 out of 4 actual positives predicted correctly
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_f1_score(): void
    {
        $predictions = [
            ['actual' => 1, 'predicted' => 1],
            ['actual' => 0, 'predicted' => 1],
            ['actual' => 1, 'predicted' => 1],
            ['actual' => 0, 'predicted' => 0],
            ['actual' => 1, 'predicted' => 1],
        ];

        $f1Score = $this->calculateF1Score($predictions);
        $this->assertEquals(6 / 7, $f1Score); // Harmonic mean of precision and recall (6/7 = 0.857142857...)
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_confusion_matrix(): void
    {
        $predictions = [
            ['actual' => 1, 'predicted' => 1],
            ['actual' => 0, 'predicted' => 0],
            ['actual' => 1, 'predicted' => 0],
            ['actual' => 0, 'predicted' => 1],
            ['actual' => 1, 'predicted' => 1],
        ];

        $confusionMatrix = $this->calculateConfusionMatrix($predictions);
        $this->assertEquals(2, $confusionMatrix['true_positive']);
        $this->assertEquals(1, $confusionMatrix['true_negative']);
        $this->assertEquals(1, $confusionMatrix['false_positive']);
        $this->assertEquals(1, $confusionMatrix['false_negative']);
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_roc_auc_score(): void
    {
        $predictions = [
            ['actual' => 1, 'predicted' => 0.9],
            ['actual' => 0, 'predicted' => 0.1],
            ['actual' => 1, 'predicted' => 0.8],
            ['actual' => 0, 'predicted' => 0.2],
            ['actual' => 1, 'predicted' => 0.7],
        ];

        $auc = $this->calculateROCAUC($predictions);
        $this->assertGreaterThan(0.5, $auc);
        $this->assertLessThanOrEqual(1.0, $auc);
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_mean_squared_error(): void
    {
        $predictions = [
            ['actual' => 100.0, 'predicted' => 95.0],
            ['actual' => 200.0, 'predicted' => 210.0],
            ['actual' => 150.0, 'predicted' => 145.0],
            ['actual' => 300.0, 'predicted' => 290.0],
        ];

        $mse = $this->calculateMSE($predictions);
        $this->assertEquals(62.5, $mse); // Average of squared differences
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_mean_absolute_error(): void
    {
        $predictions = [
            ['actual' => 100.0, 'predicted' => 95.0],
            ['actual' => 200.0, 'predicted' => 210.0],
            ['actual' => 150.0, 'predicted' => 145.0],
            ['actual' => 300.0, 'predicted' => 290.0],
        ];

        $mae = $this->calculateMAE($predictions);
        $this->assertEquals(7.5, $mae); // Average of absolute differences
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_r2_score(): void
    {
        $predictions = [
            ['actual' => 100.0, 'predicted' => 95.0],
            ['actual' => 200.0, 'predicted' => 210.0],
            ['actual' => 150.0, 'predicted' => 145.0],
            ['actual' => 300.0, 'predicted' => 290.0],
        ];

        $r2 = $this->calculateR2Score($predictions);
        $this->assertGreaterThan(0.8, $r2); // Good fit
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_cross_validation_score(): void
    {
        $data = [
            ['features' => [1, 2, 3], 'target' => 1],
            ['features' => [2, 3, 4], 'target' => 0],
            ['features' => [3, 4, 5], 'target' => 1],
            ['features' => [4, 5, 6], 'target' => 0],
            ['features' => [5, 6, 7], 'target' => 1],
        ];

        $cvScores = $this->performCrossValidation($data, 3);
        $this->assertCount(3, $cvScores);
        $this->assertGreaterThan(0, $cvScores[0]);
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_learning_curve(): void
    {
        $trainingSizes = [10, 20, 30, 40, 50];
        $trainingScores = [0.6, 0.7, 0.75, 0.8, 0.82];
        $validationScores = [0.5, 0.65, 0.7, 0.75, 0.78];

        $learningCurve = $this->calculateLearningCurve($trainingSizes, $trainingScores, $validationScores);
        $this->assertArrayHasKey('training_scores', $learningCurve);
        $this->assertArrayHasKey('validation_scores', $learningCurve);
        $this->assertArrayHasKey('gap', $learningCurve);
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_feature_importance(): void
    {
        $features = ['price', 'rating', 'reviews', 'category', 'brand'];
        $importance = [0.4, 0.3, 0.15, 0.1, 0.05];

        $featureImportance = $this->calculateFeatureImportance($features, $importance);
        $this->assertCount(5, $featureImportance);
        $this->assertEquals('price', $featureImportance[0]['feature']);
        $this->assertEquals(0.4, $featureImportance[0]['importance']);
    }

    #[Test]
    #[CoversNothing]
    public function it_validates_model_comparison(): void
    {
        $models = [
            'Random Forest' => ['accuracy' => 0.85, 'precision' => 0.82, 'recall' => 0.88],
            'SVM' => ['accuracy' => 0.83, 'precision' => 0.85, 'recall' => 0.81],
            'Logistic Regression' => ['accuracy' => 0.80, 'precision' => 0.78, 'recall' => 0.82],
        ];

        $bestModel = $this->compareModels($models);
        $this->assertEquals('Random Forest', $bestModel['name']);
    }

    private function calculateAccuracy(array $predictions): float
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

    private function calculatePrecision(array $predictions): float
    {
        $truePositives = 0;
        $falsePositives = 0;

        foreach ($predictions as $prediction) {
            if ($prediction['predicted'] === 1) {
                if ($prediction['actual'] === 1) {
                    $truePositives++;
                } else {
                    $falsePositives++;
                }
            }
        }

        $totalPositivePredictions = $truePositives + $falsePositives;

        return $totalPositivePredictions > 0 ? $truePositives / $totalPositivePredictions : 0;
    }

    private function calculateRecall(array $predictions): float
    {
        $truePositives = 0;
        $falseNegatives = 0;

        foreach ($predictions as $prediction) {
            if ($prediction['actual'] === 1) {
                if ($prediction['predicted'] === 1) {
                    $truePositives++;
                } else {
                    $falseNegatives++;
                }
            }
        }

        $totalActualPositives = $truePositives + $falseNegatives;

        return $totalActualPositives > 0 ? $truePositives / $totalActualPositives : 0;
    }

    private function calculateF1Score(array $predictions): float
    {
        $precision = $this->calculatePrecision($predictions);
        $recall = $this->calculateRecall($predictions);

        if ($precision + $recall === 0) {
            return 0;
        }

        return 2 * ($precision * $recall) / ($precision + $recall);
    }

    private function calculateConfusionMatrix(array $predictions): array
    {
        $truePositives = 0;
        $trueNegatives = 0;
        $falsePositives = 0;
        $falseNegatives = 0;

        foreach ($predictions as $prediction) {
            $actual = $prediction['actual'];
            $predicted = $prediction['predicted'];

            if ($actual === 1 && $predicted === 1) {
                $truePositives++;
            } elseif ($actual === 0 && $predicted === 0) {
                $trueNegatives++;
            } elseif ($actual === 0 && $predicted === 1) {
                $falsePositives++;
            } elseif ($actual === 1 && $predicted === 0) {
                $falseNegatives++;
            }
        }

        return [
            'true_positive' => $truePositives,
            'true_negative' => $trueNegatives,
            'false_positive' => $falsePositives,
            'false_negative' => $falseNegatives,
        ];
    }

    private function calculateROCAUC(array $predictions): float
    {
        // Proper ROC AUC calculation using trapezoidal rule
        $sortedPredictions = $predictions;
        usort($sortedPredictions, function ($a, $b) {
            return $b['predicted'] <=> $a['predicted'];
        });

        $truePositives = 0;
        $falsePositives = 0;
        $auc = 0;
        $previousTruePositives = 0;
        $previousFalsePositives = 0;

        foreach ($sortedPredictions as $prediction) {
            if ($prediction['actual'] === 1) {
                $truePositives++;
            } else {
                $falsePositives++;
            }
        }

        $totalPositives = $truePositives;
        $totalNegatives = $falsePositives;

        if ($totalPositives === 0 || $totalNegatives === 0) {
            return 0.5; // Random classifier
        }

        $truePositives = 0;
        $falsePositives = 0;

        foreach ($sortedPredictions as $prediction) {
            if ($prediction['actual'] === 1) {
                $truePositives++;
            } else {
                $falsePositives++;
                $auc += $truePositives * (1 / $totalNegatives);
            }
        }

        return $auc / $totalPositives;
    }

    private function calculateMSE(array $predictions): float
    {
        $sumSquaredErrors = 0;
        $count = count($predictions);

        foreach ($predictions as $prediction) {
            $error = $prediction['actual'] - $prediction['predicted'];
            $sumSquaredErrors += $error * $error;
        }

        return $sumSquaredErrors / $count;
    }

    private function calculateMAE(array $predictions): float
    {
        $sumAbsoluteErrors = 0;
        $count = count($predictions);

        foreach ($predictions as $prediction) {
            $error = abs($prediction['actual'] - $prediction['predicted']);
            $sumAbsoluteErrors += $error;
        }

        return $sumAbsoluteErrors / $count;
    }

    private function calculateR2Score(array $predictions): float
    {
        $actualValues = array_column($predictions, 'actual');
        $predictedValues = array_column($predictions, 'predicted');

        $actualMean = array_sum($actualValues) / count($actualValues);

        $ssRes = 0; // Sum of squares of residuals
        $ssTot = 0; // Total sum of squares

        for ($i = 0; $i < count($actualValues); $i++) {
            $ssRes += pow($actualValues[$i] - $predictedValues[$i], 2);
            $ssTot += pow($actualValues[$i] - $actualMean, 2);
        }

        return 1 - ($ssRes / $ssTot);
    }

    private function performCrossValidation(array $data, int $folds): array
    {
        $scores = [];
        $foldSize = (int) (count($data) / $folds);

        for ($i = 0; $i < $folds; $i++) {
            $start = $i * $foldSize;
            $end = ($i + 1) * $foldSize;

            $testData = array_slice($data, $start, $end - $start);
            $trainData = array_merge(
                array_slice($data, 0, $start),
                array_slice($data, $end)
            );

            // Simulate model training and testing
            $score = $this->simulateModelTraining($trainData, $testData);
            $scores[] = $score;
        }

        return $scores;
    }

    private function simulateModelTraining(array $trainData, array $testData): float
    {
        // Simplified simulation - in reality, this would train a model
        return 0.7 + (rand(0, 30) / 100); // Random score between 0.7 and 1.0
    }

    private function calculateLearningCurve(array $trainingSizes, array $trainingScores, array $validationScores): array
    {
        $gap = [];
        for ($i = 0; $i < count($trainingSizes); $i++) {
            $gap[] = $trainingScores[$i] - $validationScores[$i];
        }

        return [
            'training_scores' => $trainingScores,
            'validation_scores' => $validationScores,
            'gap' => $gap,
        ];
    }

    private function calculateFeatureImportance(array $features, array $importance): array
    {
        $featureImportance = [];
        for ($i = 0; $i < count($features); $i++) {
            $featureImportance[] = [
                'feature' => $features[$i],
                'importance' => $importance[$i],
            ];
        }

        // Sort by importance descending
        usort($featureImportance, function ($a, $b) {
            return $b['importance'] <=> $a['importance'];
        });

        return $featureImportance;
    }

    private function compareModels(array $models): array
    {
        $bestModel = null;
        $bestScore = 0;

        foreach ($models as $name => $metrics) {
            // Weighted average of metrics
            $score = ($metrics['accuracy'] * 0.4) + ($metrics['precision'] * 0.3) + ($metrics['recall'] * 0.3);

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestModel = [
                    'name' => $name,
                    'score' => $score,
                    'metrics' => $metrics,
                ];
            }
        }

        return $bestModel;
    }
}
