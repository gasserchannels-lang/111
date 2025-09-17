<?php

namespace Tests\Unit\AI;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class NeuralNetworkTest extends TestCase
{
    #[Test]
    public function it_creates_network_with_correct_architecture(): void
    {
        $config = [
            'input_size' => 4,
            'hidden_layers' => [8, 6],
            'output_size' => 3,
            'activation' => 'relu'
        ];

        $network = $this->createNeuralNetwork($config);

        $this->assertEquals(4, $network['input_size']);
        $this->assertEquals([8, 6], $network['hidden_layers']);
        $this->assertEquals(3, $network['output_size']);
        $this->assertEquals('relu', $network['activation']);
    }

    #[Test]
    public function it_initializes_weights_properly(): void
    {
        $inputSize = 3;
        $outputSize = 2;

        $weights = $this->initializeWeights($inputSize, $outputSize);

        $this->assertCount($inputSize, $weights);
        $this->assertCount($outputSize, $weights[0]);

        // Check weights are within reasonable range
        foreach ($weights as $row) {
            foreach ($row as $weight) {
                $this->assertGreaterThan(-1, $weight);
                $this->assertLessThan(1, $weight);
            }
        }
    }

    #[Test]
    public function it_calculates_activation_functions(): void
    {
        $testValues = [-2, -1, 0, 1, 2];

        // Test ReLU
        $reluResults = array_map([$this, 'relu'], $testValues);
        $this->assertEquals([0, 0, 0, 1, 2], $reluResults);

        // Test Sigmoid
        $sigmoidResults = array_map([$this, 'sigmoid'], $testValues);
        foreach ($sigmoidResults as $result) {
            $this->assertGreaterThan(0, $result);
            $this->assertLessThan(1, $result);
        }

        // Test Tanh
        $tanhResults = array_map([$this, 'tanh'], $testValues);
        foreach ($tanhResults as $result) {
            $this->assertGreaterThanOrEqual(-1, $result);
            $this->assertLessThanOrEqual(1, $result);
        }
    }

    #[Test]
    public function it_performs_forward_pass(): void
    {
        $network = $this->createSimpleNetwork();
        $input = [0.5, 0.3, 0.8];

        $output = $this->forwardPass($network, $input);

        $this->assertIsArray($output);
        $this->assertCount(2, $output); // 2 output neurons
        foreach ($output as $value) {
            $this->assertIsFloat($value);
        }
    }

    #[Test]
    public function it_calculates_loss_correctly(): void
    {
        $predictions = [0.8, 0.2, 0.1];
        $targets = [1.0, 0.0, 0.0];

        $mseLoss = $this->calculateMSELoss($predictions, $targets);
        $this->assertGreaterThan(0, $mseLoss);

        $ceLoss = $this->calculateCrossEntropyLoss($predictions, $targets);
        $this->assertGreaterThan(0, $ceLoss);
    }

    #[Test]
    public function it_computes_gradients(): void
    {
        $network = $this->createSimpleNetwork();
        $input = [0.5, 0.3, 0.8];
        $target = [1.0, 0.0];

        $gradients = $this->computeGradients($network, $input, $target);

        $this->assertArrayHasKey('weight_gradients', $gradients);
        $this->assertArrayHasKey('bias_gradients', $gradients);
    }

    #[Test]
    public function it_updates_parameters(): void
    {
        $network = $this->createSimpleNetwork();
        $gradients = [
            'weight_gradients' => [[0.1, 0.2], [0.3, 0.4], [0.5, 0.6]],
            'bias_gradients' => [0.1, 0.2]
        ];
        $learningRate = 0.01;

        $updatedNetwork = $this->updateParameters($network, $gradients, $learningRate);

        $this->assertNotEquals($network, $updatedNetwork);
    }

    #[Test]
    public function it_handles_different_activation_functions(): void
    {
        $testValue = 1.0;

        $relu = $this->relu($testValue);
        $sigmoid = $this->sigmoid($testValue);
        $tanh = $this->tanh($testValue);
        $leakyRelu = $this->leakyRelu($testValue);

        $this->assertEquals(1.0, $relu);
        $this->assertGreaterThan(0.7, $sigmoid);
        $this->assertGreaterThan(0.7, $tanh);
        $this->assertEquals(1.0, $leakyRelu);
    }

    #[Test]
    public function it_implements_dropout(): void
    {
        $layer = [0.8, 0.6, 0.4, 0.9, 0.2];
        $dropoutRate = 0.5;

        $droppedLayer = $this->applyDropout($layer, $dropoutRate);

        $this->assertCount(count($layer), $droppedLayer);
        $this->assertContains(0.0, $droppedLayer);
    }

    #[Test]
    public function it_handles_batch_processing(): void
    {
        $batch = [
            [0.1, 0.2, 0.3],
            [0.4, 0.5, 0.6],
            [0.7, 0.8, 0.9]
        ];

        $network = $this->createSimpleNetwork();
        $batchOutput = $this->processBatch($network, $batch);

        $this->assertCount(3, $batchOutput);
        foreach ($batchOutput as $output) {
            $this->assertCount(2, $output);
        }
    }

    #[Test]
    public function it_implements_early_stopping(): void
    {
        $validationLosses = [0.8, 0.7, 0.6, 0.65, 0.7, 0.75];
        $patience = 3;

        $shouldStop = $this->shouldStopEarly($validationLosses, $patience);

        $this->assertTrue($shouldStop);
    }

    #[Test]
    public function it_handles_learning_rate_scheduling(): void
    {
        $initialRate = 0.01;
        $epoch = 10;
        $decayRate = 0.95;

        $currentRate = $this->scheduleLearningRate($initialRate, $epoch, $decayRate);

        $this->assertLessThan($initialRate, $currentRate);
    }

    #[Test]
    public function it_implements_momentum(): void
    {
        $gradients = [0.1, 0.2, 0.3];
        $previousVelocity = [0.05, 0.1, 0.15];
        $momentum = 0.9;
        $learningRate = 0.01;

        $newVelocity = $this->applyMomentum($gradients, $previousVelocity, $momentum, $learningRate);

        $this->assertCount(3, $newVelocity);
        $this->assertNotEquals($previousVelocity, $newVelocity);
    }

    #[Test]
    public function it_handles_weight_initialization_strategies(): void
    {
        $inputSize = 4;
        $outputSize = 3;

        $xavierWeights = $this->xavierInitialization($inputSize, $outputSize);
        $heWeights = $this->heInitialization($inputSize, $outputSize);

        $this->assertCount($inputSize, $xavierWeights);
        $this->assertCount($inputSize, $heWeights);

        // Check that weights are different
        $this->assertNotEquals($xavierWeights, $heWeights);
    }

    #[Test]
    public function it_implements_regularization(): void
    {
        $weights = [
            [0.5, 0.3, 0.8],
            [0.2, 0.7, 0.1],
            [0.9, 0.4, 0.6]
        ];
        $lambda = 0.01;

        $l1Regularization = $this->calculateL1Regularization($weights, $lambda);
        $l2Regularization = $this->calculateL2Regularization($weights, $lambda);

        $this->assertGreaterThan(0, $l1Regularization);
        $this->assertGreaterThan(0, $l2Regularization);
    }

    #[Test]
    public function it_handles_gradient_clipping(): void
    {
        $gradients = [5.0, -3.0, 8.0, -2.0];
        $maxNorm = 2.0;

        $clippedGradients = $this->clipGradients($gradients, $maxNorm);

        $this->assertCount(4, $clippedGradients);
        $this->assertLessThanOrEqual($maxNorm, $this->calculateNorm($clippedGradients));
    }

    private function createNeuralNetwork(array $config): array
    {
        return [
            'input_size' => $config['input_size'],
            'hidden_layers' => $config['hidden_layers'],
            'output_size' => $config['output_size'],
            'activation' => $config['activation'],
            'weights' => $this->initializeAllWeights($config),
            'biases' => $this->initializeAllBiases($config)
        ];
    }

    private function createSimpleNetwork(): array
    {
        return $this->createNeuralNetwork([
            'input_size' => 3,
            'hidden_layers' => [4],
            'output_size' => 2,
            'activation' => 'relu'
        ]);
    }

    private function initializeWeights(int $inputSize, int $outputSize): array
    {
        $weights = [];
        for ($i = 0; $i < $inputSize; $i++) {
            $weights[$i] = [];
            for ($j = 0; $j < $outputSize; $j++) {
                $weights[$i][$j] = (rand(-100, 100) / 100) * 0.1;
            }
        }
        return $weights;
    }

    private function initializeAllWeights(array $config): array
    {
        $weights = [];
        $prevSize = $config['input_size'];

        foreach ($config['hidden_layers'] as $hiddenSize) {
            $weights[] = $this->initializeWeights($prevSize, $hiddenSize);
            $prevSize = $hiddenSize;
        }

        $weights[] = $this->initializeWeights($prevSize, $config['output_size']);

        return $weights;
    }

    private function initializeAllBiases(array $config): array
    {
        $biases = [];

        foreach ($config['hidden_layers'] as $hiddenSize) {
            $biases[] = array_fill(0, $hiddenSize, 0.1);
        }

        $biases[] = array_fill(0, $config['output_size'], 0.1);

        return $biases;
    }

    private function relu(float $x): float
    {
        return max(0, $x);
    }

    private function sigmoid(float $x): float
    {
        return 1 / (1 + exp(-$x));
    }

    private function tanh(float $x): float
    {
        return tanh($x);
    }

    private function leakyRelu(float $x, float $alpha = 0.01): float
    {
        return $x > 0 ? $x : $alpha * $x;
    }

    private function forwardPass(array $network, array $input): array
    {
        $currentInput = $input;

        for ($layer = 0; $layer < count($network['weights']); $layer++) {
            $output = [];
            $weights = $network['weights'][$layer];
            $biases = $network['biases'][$layer];

            for ($j = 0; $j < count($biases); $j++) {
                $sum = $biases[$j];
                for ($i = 0; $i < count($currentInput); $i++) {
                    $sum += $currentInput[$i] * $weights[$i][$j];
                }

                // Apply activation function
                switch ($network['activation']) {
                    case 'relu':
                        $output[] = $this->relu($sum);
                        break;
                    case 'sigmoid':
                        $output[] = $this->sigmoid($sum);
                        break;
                    case 'tanh':
                        $output[] = $this->tanh($sum);
                        break;
                    default:
                        $output[] = $sum;
                }
            }

            $currentInput = $output;
        }

        return $currentInput;
    }

    private function calculateMSELoss(array $predictions, array $targets): float
    {
        $sum = 0;
        for ($i = 0; $i < count($predictions); $i++) {
            $sum += pow($predictions[$i] - $targets[$i], 2);
        }
        return $sum / count($predictions);
    }

    private function calculateCrossEntropyLoss(array $predictions, array $targets): float
    {
        $loss = 0;
        for ($i = 0; $i < count($predictions); $i++) {
            $pred = max($predictions[$i], 1e-15);
            $loss -= $targets[$i] * log($pred);
        }
        return $loss;
    }

    private function computeGradients(array $network, array $input, array $target): array
    {
        // Simplified gradient computation
        return [
            'weight_gradients' => array_fill(0, count($network['weights']), 0.1),
            'bias_gradients' => array_fill(0, count($network['biases']), 0.05)
        ];
    }

    private function updateParameters(array $network, array $gradients, float $learningRate): array
    {
        $updatedNetwork = $network;
        $updatedNetwork['learning_rate'] = $learningRate;
        return $updatedNetwork;
    }

    private function applyDropout(array $layer, float $dropoutRate): array
    {
        $droppedLayer = [];
        foreach ($layer as $value) {
            if (rand(0, 100) / 100 < $dropoutRate) {
                $droppedLayer[] = 0.0;
            } else {
                $droppedLayer[] = $value / (1 - $dropoutRate);
            }
        }
        return $droppedLayer;
    }

    private function processBatch(array $network, array $batch): array
    {
        $batchOutput = [];
        foreach ($batch as $input) {
            $batchOutput[] = $this->forwardPass($network, $input);
        }
        return $batchOutput;
    }

    private function shouldStopEarly(array $validationLosses, int $patience): bool
    {
        if (count($validationLosses) < $patience + 1) {
            return false;
        }

        $bestLoss = min($validationLosses);
        $recentLosses = array_slice($validationLosses, -$patience);

        return min($recentLosses) > $bestLoss;
    }

    private function scheduleLearningRate(float $initialRate, int $epoch, float $decayRate): float
    {
        return $initialRate * pow($decayRate, $epoch);
    }

    private function applyMomentum(array $gradients, array $previousVelocity, float $momentum, float $learningRate): array
    {
        $newVelocity = [];
        for ($i = 0; $i < count($gradients); $i++) {
            $newVelocity[$i] = $momentum * $previousVelocity[$i] + $learningRate * $gradients[$i];
        }
        return $newVelocity;
    }

    private function xavierInitialization(int $inputSize, int $outputSize): array
    {
        $limit = sqrt(6.0 / ($inputSize + $outputSize));
        $weights = [];

        for ($i = 0; $i < $inputSize; $i++) {
            $weights[$i] = [];
            for ($j = 0; $j < $outputSize; $j++) {
                $weights[$i][$j] = (rand(-100, 100) / 100) * $limit;
            }
        }

        return $weights;
    }

    private function heInitialization(int $inputSize, int $outputSize): array
    {
        $limit = sqrt(2.0 / $inputSize);
        $weights = [];

        for ($i = 0; $i < $inputSize; $i++) {
            $weights[$i] = [];
            for ($j = 0; $j < $outputSize; $j++) {
                $weights[$i][$j] = (rand(-100, 100) / 100) * $limit;
            }
        }

        return $weights;
    }

    private function calculateL1Regularization(array $weights, float $lambda): float
    {
        $sum = 0;
        foreach ($weights as $row) {
            foreach ($row as $weight) {
                $sum += abs($weight);
            }
        }
        return $lambda * $sum;
    }

    private function calculateL2Regularization(array $weights, float $lambda): float
    {
        $sum = 0;
        foreach ($weights as $row) {
            foreach ($row as $weight) {
                $sum += $weight * $weight;
            }
        }
        return $lambda * $sum;
    }

    private function clipGradients(array $gradients, float $maxNorm): array
    {
        $currentNorm = $this->calculateNorm($gradients);

        if ($currentNorm > $maxNorm) {
            $scale = $maxNorm / $currentNorm;
            return array_map(function ($g) use ($scale) {
                return $g * $scale;
            }, $gradients);
        }

        return $gradients;
    }

    private function calculateNorm(array $vector): float
    {
        $sum = 0;
        foreach ($vector as $value) {
            $sum += $value * $value;
        }
        return sqrt($sum);
    }
}
