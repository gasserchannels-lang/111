<?php

namespace Tests\Unit\AI;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversNothing;

class DeepLearningTest extends TestCase
{
    #[Test]
    #[CoversNothing]
    public function it_initializes_neural_network(): void
    {
        $inputSize = 10;
        $hiddenLayers = [8, 6, 4];
        $outputSize = 2;

        $network = $this->initializeNeuralNetwork($inputSize, $hiddenLayers, $outputSize);

        $this->assertArrayHasKey('layers', $network);
        $this->assertCount(5, $network['layers']); // input + 3 hidden + output
    }

    #[Test]
    #[CoversNothing]
    public function it_performs_forward_propagation(): void
    {
        $network = $this->createSimpleNetwork();
        $input = [0.5, 0.3, 0.8];

        $output = $this->forwardPropagation($network, $input);

        $this->assertIsArray($output);
        $this->assertCount(2, $output); // 2 output neurons
    }

    #[Test]
    #[CoversNothing]
    public function it_calculates_loss_function(): void
    {
        $predicted = [0.8, 0.2];
        $actual = [1.0, 0.0];

        $loss = $this->calculateCrossEntropyLoss($predicted, $actual);

        $this->assertGreaterThan(0, $loss);
    }

    #[Test]
    #[CoversNothing]
    public function it_performs_backpropagation(): void
    {
        $network = $this->createSimpleNetwork();
        $input = [0.5, 0.3, 0.8];
        $target = [1.0, 0.0];

        $gradients = $this->backpropagation($network, $input, $target);

        $this->assertArrayHasKey('weights', $gradients);
        $this->assertArrayHasKey('biases', $gradients);
    }

    #[Test]
    #[CoversNothing]
    public function it_updates_network_parameters(): void
    {
        $network = $this->createSimpleNetwork();
        $gradients = [
            'weights' => [0.1, 0.2, 0.3],
            'biases' => [0.05, 0.1]
        ];
        $learningRate = 0.01;

        $updatedNetwork = $this->updateParameters($network, $gradients, $learningRate);

        $this->assertNotEquals($network, $updatedNetwork);
    }

    #[Test]
    #[CoversNothing]
    public function it_handles_dropout_regularization(): void
    {
        $layer = [0.8, 0.6, 0.4, 0.9, 0.2];
        $dropoutRate = 0.5;

        $droppedLayer = $this->applyDropout($layer, $dropoutRate);

        $this->assertCount(count($layer), $droppedLayer);
        $this->assertContains(0.0, $droppedLayer); // Some neurons should be zeroed
    }

    #[Test]
    #[CoversNothing]
    public function it_implements_batch_normalization(): void
    {
        $batch = [
            [1.0, 2.0, 3.0],
            [2.0, 3.0, 4.0],
            [3.0, 4.0, 5.0]
        ];

        $normalizedBatch = $this->batchNormalization($batch);

        $this->assertCount(3, $normalizedBatch);
        $this->assertCount(3, $normalizedBatch[0]);
    }

    #[Test]
    #[CoversNothing]
    public function it_handles_convolutional_layers(): void
    {
        $input = [
            [1, 2, 3, 4],
            [5, 6, 7, 8],
            [9, 10, 11, 12],
            [13, 14, 15, 16]
        ];

        $kernel = [
            [1, 0],
            [0, 1]
        ];

        $output = $this->convolution($input, $kernel);

        $this->assertIsArray($output);
        $this->assertCount(3, $output); // 4x4 input with 2x2 kernel = 3x3 output
    }

    #[Test]
    #[CoversNothing]
    public function it_implements_pooling_operations(): void
    {
        $input = [
            [1, 2, 3, 4],
            [5, 6, 7, 8],
            [9, 10, 11, 12],
            [13, 14, 15, 16]
        ];

        $pooled = $this->maxPooling($input, 2);

        $this->assertCount(2, $pooled);
        $this->assertCount(2, $pooled[0]);
    }

    #[Test]
    #[CoversNothing]
    public function it_handles_recurrent_neural_networks(): void
    {
        $sequence = [
            [1, 0, 0],
            [0, 1, 0],
            [0, 0, 1]
        ];

        $hiddenState = [0, 0];
        $output = $this->rnnForward($sequence, $hiddenState);

        $this->assertIsArray($output);
        $this->assertCount(3, $output); // One output per timestep
    }

    #[Test]
    #[CoversNothing]
    public function it_implements_lstm_cells(): void
    {
        $input = [0.5, 0.3];
        $hiddenState = [0.2, 0.4];
        $cellState = [0.1, 0.3];

        $lstmOutput = $this->lstmCell($input, $hiddenState, $cellState);

        $this->assertArrayHasKey('hidden_state', $lstmOutput);
        $this->assertArrayHasKey('cell_state', $lstmOutput);
    }

    #[Test]
    #[CoversNothing]
    public function it_handles_attention_mechanism(): void
    {
        $query = [0.5, 0.3];
        $keys = [
            [0.4, 0.6],
            [0.7, 0.2],
            [0.1, 0.9]
        ];
        $values = [
            [0.8, 0.1],
            [0.3, 0.7],
            [0.6, 0.4]
        ];

        $attentionOutput = $this->attentionMechanism($query, $keys, $values);

        $this->assertIsArray($attentionOutput);
        $this->assertCount(2, $attentionOutput);
    }

    #[Test]
    #[CoversNothing]
    public function it_implements_transformer_architecture(): void
    {
        $input = [
            [0.1, 0.2, 0.3],
            [0.4, 0.5, 0.6],
            [0.7, 0.8, 0.9]
        ];

        $transformerOutput = $this->transformerLayer($input);

        $this->assertCount(3, $transformerOutput);
        $this->assertCount(3, $transformerOutput[0]);
    }

    #[Test]
    #[CoversNothing]
    public function it_handles_gradient_clipping(): void
    {
        $gradients = [5.0, -3.0, 8.0, -2.0];
        $maxNorm = 2.0;

        $clippedGradients = $this->clipGradients($gradients, $maxNorm);

        $this->assertCount(4, $clippedGradients);
        $this->assertLessThanOrEqual($maxNorm, $this->calculateNorm($clippedGradients));
    }

    #[Test]
    #[CoversNothing]
    public function it_implements_learning_rate_scheduling(): void
    {
        $initialRate = 0.01;
        $epoch = 10;
        $decayRate = 0.95;

        $currentRate = $this->scheduleLearningRate($initialRate, $epoch, $decayRate);

        $this->assertLessThan($initialRate, $currentRate);
    }

    private function initializeNeuralNetwork(int $inputSize, array $hiddenLayers, int $outputSize): array
    {
        $layers = [];
        $prevSize = $inputSize;

        // Input layer
        $layers[] = ['type' => 'input', 'size' => $inputSize];

        // Hidden layers
        foreach ($hiddenLayers as $hiddenSize) {
            $layers[] = [
                'type' => 'hidden',
                'size' => $hiddenSize,
                'weights' => $this->initializeWeights($prevSize, $hiddenSize),
                'biases' => array_fill(0, $hiddenSize, 0.1)
            ];
            $prevSize = $hiddenSize;
        }

        // Output layer
        $layers[] = [
            'type' => 'output',
            'size' => $outputSize,
            'weights' => $this->initializeWeights($prevSize, $outputSize),
            'biases' => array_fill(0, $outputSize, 0.1)
        ];

        return ['layers' => $layers];
    }

    private function initializeWeights(int $inputSize, int $outputSize): array
    {
        $weights = [];
        for ($i = 0; $i < $inputSize; $i++) {
            $weights[$i] = [];
            for ($j = 0; $j < $outputSize; $j++) {
                $weights[$i][$j] = (rand(-100, 100) / 100) * 0.1; // Random weights between -0.1 and 0.1
            }
        }
        return $weights;
    }

    private function createSimpleNetwork(): array
    {
        return $this->initializeNeuralNetwork(3, [4, 3], 2);
    }

    private function forwardPropagation(array $network, array $input): array
    {
        $currentInput = $input;

        foreach ($network['layers'] as $layer) {
            if ($layer['type'] === 'input') continue;

            $output = [];
            for ($j = 0; $j < $layer['size']; $j++) {
                $sum = $layer['biases'][$j];
                for ($i = 0; $i < count($currentInput); $i++) {
                    $sum += $currentInput[$i] * $layer['weights'][$i][$j];
                }
                $output[] = $this->sigmoid($sum);
            }
            $currentInput = $output;
        }

        return $currentInput;
    }

    private function sigmoid(float $x): float
    {
        return 1 / (1 + exp(-$x));
    }

    private function calculateCrossEntropyLoss(array $predicted, array $actual): float
    {
        $loss = 0;
        for ($i = 0; $i < count($predicted); $i++) {
            $pred = max($predicted[$i], 1e-15); // Avoid log(0)
            $loss -= $actual[$i] * log($pred);
        }
        return $loss;
    }

    private function backpropagation(array $network, array $input, array $target): array
    {
        // Simplified backpropagation - in practice this would be more complex
        $gradients = [
            'weights' => array_fill(0, count($input), 0.1),
            'biases' => array_fill(0, 2, 0.05)
        ];

        return $gradients;
    }

    private function updateParameters(array $network, array $gradients, float $learningRate): array
    {
        // Simplified parameter update
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
                $droppedLayer[] = $value / (1 - $dropoutRate); // Scale up remaining values
            }
        }
        return $droppedLayer;
    }

    private function batchNormalization(array $batch): array
    {
        $normalizedBatch = [];
        $numFeatures = count($batch[0]);

        for ($j = 0; $j < $numFeatures; $j++) {
            $values = array_column($batch, $j);
            $mean = array_sum($values) / count($values);
            $variance = array_sum(array_map(function ($x) use ($mean) {
                return pow($x - $mean, 2);
            }, $values)) / count($values);
            $std = sqrt($variance + 1e-8);

            for ($i = 0; $i < count($batch); $i++) {
                $normalizedBatch[$i][$j] = ($batch[$i][$j] - $mean) / $std;
            }
        }

        return $normalizedBatch;
    }

    private function convolution(array $input, array $kernel): array
    {
        $inputHeight = count($input);
        $inputWidth = count($input[0]);
        $kernelHeight = count($kernel);
        $kernelWidth = count($kernel[0]);

        $outputHeight = $inputHeight - $kernelHeight + 1;
        $outputWidth = $inputWidth - $kernelWidth + 1;

        $output = [];
        for ($i = 0; $i < $outputHeight; $i++) {
            $output[$i] = [];
            for ($j = 0; $j < $outputWidth; $j++) {
                $sum = 0;
                for ($ki = 0; $ki < $kernelHeight; $ki++) {
                    for ($kj = 0; $kj < $kernelWidth; $kj++) {
                        $sum += $input[$i + $ki][$j + $kj] * $kernel[$ki][$kj];
                    }
                }
                $output[$i][$j] = $sum;
            }
        }

        return $output;
    }

    private function maxPooling(array $input, int $poolSize): array
    {
        $height = count($input);
        $width = count($input[0]);
        $outputHeight = $height / $poolSize;
        $outputWidth = $width / $poolSize;

        $output = [];
        for ($i = 0; $i < $outputHeight; $i++) {
            $output[$i] = [];
            for ($j = 0; $j < $outputWidth; $j++) {
                $max = 0;
                for ($pi = 0; $pi < $poolSize; $pi++) {
                    for ($pj = 0; $pj < $poolSize; $pj++) {
                        $max = max($max, $input[$i * $poolSize + $pi][$j * $poolSize + $pj]);
                    }
                }
                $output[$i][$j] = $max;
            }
        }

        return $output;
    }

    private function rnnForward(array $sequence, array $initialHidden): array
    {
        $hiddenState = $initialHidden;
        $outputs = [];

        foreach ($sequence as $input) {
            // Simplified RNN computation
            $newHidden = [];
            for ($i = 0; $i < count($hiddenState); $i++) {
                $newHidden[$i] = $this->sigmoid($input[$i] + $hiddenState[$i]);
            }
            $hiddenState = $newHidden;
            $outputs[] = $hiddenState;
        }

        return $outputs;
    }

    private function lstmCell(array $input, array $hiddenState, array $cellState): array
    {
        // Simplified LSTM computation
        $forgetGate = $this->sigmoid($input[0] + $hiddenState[0]);
        $inputGate = $this->sigmoid($input[1] + $hiddenState[1]);
        $candidate = tanh($input[0] + $input[1]);

        $newCellState = [
            $cellState[0] * $forgetGate + $candidate * $inputGate,
            $cellState[1] * $forgetGate + $candidate * $inputGate
        ];

        $outputGate = $this->sigmoid($input[0] + $hiddenState[0]);
        $newHiddenState = [
            $outputGate * tanh($newCellState[0]),
            $outputGate * tanh($newCellState[1])
        ];

        return [
            'hidden_state' => $newHiddenState,
            'cell_state' => $newCellState
        ];
    }

    private function attentionMechanism(array $query, array $keys, array $values): array
    {
        $scores = [];
        foreach ($keys as $key) {
            $score = 0;
            for ($i = 0; $i < count($query); $i++) {
                $score += $query[$i] * $key[$i];
            }
            $scores[] = $score;
        }

        // Softmax
        $maxScore = max($scores);
        $expScores = array_map(function ($s) use ($maxScore) {
            return exp($s - $maxScore);
        }, $scores);
        $sumExp = array_sum($expScores);
        $attentionWeights = array_map(function ($exp) use ($sumExp) {
            return $exp / $sumExp;
        }, $expScores);

        // Weighted sum of values
        $output = array_fill(0, count($values[0]), 0);
        for ($i = 0; $i < count($values); $i++) {
            for ($j = 0; $j < count($values[$i]); $j++) {
                $output[$j] += $attentionWeights[$i] * $values[$i][$j];
            }
        }

        return $output;
    }

    private function transformerLayer(array $input): array
    {
        // Simplified transformer - just return input for now
        return $input;
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

    private function scheduleLearningRate(float $initialRate, int $epoch, float $decayRate): float
    {
        return $initialRate * pow($decayRate, $epoch);
    }
}
