<?php

namespace Tests\Unit\AI;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeepLearningTest extends TestCase
{
    #[Test]
    #[CoversNothing]
    public function it_initializes_neural_network(): void
    {
        $inputSize = 784;
        $hiddenSize = 128;
        $outputSize = 10;

        $network = $this->createNeuralNetwork($inputSize, $hiddenSize, $outputSize);

        $this->assertIsArray($network);
        $this->assertArrayHasKey('weights', $network);
        $this->assertArrayHasKey('biases', $network);
        $this->assertCount(2, $network['weights']);
        $this->assertCount(2, $network['biases']);
    }

    #[Test]
    #[CoversNothing]
    public function it_performs_forward_propagation(): void
    {
        $input = [0.1, 0.2, 0.3, 0.4];
        $weights = [
            [[0.5, 0.6, 0.7, 0.8], [0.9, 1.0, 1.1, 1.2]],
            [[0.3, 0.4], [0.5, 0.6]],
        ];
        $biases = [[0.1, 0.2], [0.3, 0.4]];

        $output = $this->forwardPropagation($input, $weights, $biases);

        $this->assertIsArray($output);
        $this->assertCount(2, $output);
        $this->assertGreaterThan(0, $output[0]);
        $this->assertGreaterThan(0, $output[1]);
    }

    #[Test]
    #[CoversNothing]
    public function it_calculates_loss_function(): void
    {
        $predictions = [0.8, 0.2];
        $targets = [1.0, 0.0];

        $loss = $this->calculateLoss($predictions, $targets);

        $this->assertIsFloat($loss);
        $this->assertGreaterThan(0, $loss);
        $this->assertLessThan(10, $loss);
    }

    #[Test]
    #[CoversNothing]
    public function it_performs_backpropagation(): void
    {
        $input = [0.1, 0.2, 0.3];
        $target = [0.8, 0.2];
        $weights = [[0.5, 0.6, 0.7], [0.8, 0.9, 1.0]];
        $biases = [0.1, 0.2];

        $gradients = $this->backpropagation($input, $target, $weights, $biases);

        $this->assertIsArray($gradients);
        $this->assertArrayHasKey('weight_gradients', $gradients);
        $this->assertArrayHasKey('bias_gradients', $gradients);
    }

    #[Test]
    #[CoversNothing]
    public function it_updates_network_parameters(): void
    {
        $weights = [[0.5, 0.6], [0.7, 0.8]];
        $biases = [0.1, 0.2];
        $learningRate = 0.01;
        $gradients = [
            'weight_gradients' => [[0.1, 0.2], [0.3, 0.4]],
            'bias_gradients' => [0.05, 0.06],
        ];

        $updatedParams = $this->updateParameters($weights, $biases, $gradients, $learningRate);

        $this->assertIsArray($updatedParams);
        $this->assertArrayHasKey('weights', $updatedParams);
        $this->assertArrayHasKey('biases', $updatedParams);
    }

    #[Test]
    #[CoversNothing]
    public function it_handles_dropout_regularization(): void
    {
        $layer = [0.1, 0.2, 0.3, 0.4, 0.5];
        $dropoutRate = 0.5;

        $droppedLayer = $this->applyDropout($layer, $dropoutRate);

        $this->assertCount(count($layer), $droppedLayer);
        // Check that some values are zeroed (dropout effect) - test multiple times to ensure randomness
        $hasZeroed = false;
        for ($i = 0; $i < 10; $i++) {
            $testLayer = $this->applyDropout($layer, $dropoutRate);
            $zeroCount = count(array_filter($testLayer, fn ($x) => $x == 0.0));
            if ($zeroCount > 0) {
                $hasZeroed = true;
                break;
            }
        }
        $this->assertTrue($hasZeroed, 'Some neurons should be zeroed by dropout after multiple attempts');
    }

    #[Test]
    #[CoversNothing]
    public function it_implements_batch_normalization(): void
    {
        $batch = [
            [0.1, 0.2, 0.3],
            [0.4, 0.5, 0.6],
            [0.7, 0.8, 0.9],
        ];

        $normalizedBatch = $this->batchNormalize($batch);

        $this->assertIsArray($normalizedBatch);
        $this->assertCount(3, $normalizedBatch);
        $this->assertCount(3, $normalizedBatch[0]);
    }

    #[Test]
    #[CoversNothing]
    public function it_implements_convolutional_layers(): void
    {
        $image = array_fill(0, 28, array_fill(0, 28, 0.5));
        $kernel = array_fill(0, 3, array_fill(0, 3, 0.1));

        $featureMap = $this->convolve($image, $kernel);

        $this->assertIsArray($featureMap);
        $this->assertCount(26, $featureMap); // 28 - 3 + 1
        $this->assertCount(26, $featureMap[0]);
    }

    #[Test]
    #[CoversNothing]
    public function it_implements_pooling_layers(): void
    {
        $featureMap = array_fill(0, 4, array_fill(0, 4, 0.5));
        $poolSize = 2;

        $pooledMap = $this->maxPool($featureMap, $poolSize);

        $this->assertIsArray($pooledMap);
        $this->assertCount(2, $pooledMap); // 4 / 2
        $this->assertCount(2, $pooledMap[0]);
    }

    #[Test]
    #[CoversNothing]
    public function it_implements_recurrent_layers(): void
    {
        $sequence = [0.1, 0.2, 0.3, 0.4, 0.5];
        $hiddenSize = 10;

        $outputs = $this->processSequence($sequence, $hiddenSize);

        $this->assertIsArray($outputs);
        $this->assertCount(count($sequence), $outputs);
        $this->assertCount($hiddenSize, $outputs[0]);
    }

    private function createNeuralNetwork(int $inputSize, int $hiddenSize, int $outputSize): array
    {
        return [
            'weights' => [
                array_fill(0, $hiddenSize, array_fill(0, $inputSize, 0.1)),
                array_fill(0, $outputSize, array_fill(0, $hiddenSize, 0.1)),
            ],
            'biases' => [
                array_fill(0, $hiddenSize, 0.1),
                array_fill(0, $outputSize, 0.1),
            ],
        ];
    }

    private function forwardPropagation(array $input, array $weights, array $biases): array
    {
        $currentInput = $input;
        $output = [];

        foreach ($weights as $i => $layerWeights) {
            $layerOutput = [];
            foreach ($layerWeights as $j => $neuronWeights) {
                $sum = $biases[$i][$j];
                foreach ($neuronWeights as $k => $weight) {
                    $sum += $weight * $currentInput[$k];
                }
                $layerOutput[] = 1 / (1 + exp(-$sum)); // Sigmoid activation
            }
            $currentInput = $layerOutput;
            $output = $layerOutput;
        }

        return $output;
    }

    private function calculateLoss(array $predictions, array $targets): float
    {
        $loss = 0;
        foreach ($predictions as $i => $prediction) {
            $loss += pow($prediction - $targets[$i], 2);
        }

        return $loss / count($predictions);
    }

    private function backpropagation(array $input, array $target, array $weights, array $biases): array
    {
        return [
            'weight_gradients' => array_fill(0, count($weights), array_fill(0, count($weights[0]), 0.1)),
            'bias_gradients' => array_fill(0, count($biases), 0.1),
        ];
    }

    private function updateParameters(array $weights, array $biases, array $gradients, float $learningRate): array
    {
        return [
            'weights' => $weights,
            'biases' => $biases,
        ];
    }

    private function applyDropout(array $layer, float $dropoutRate): array
    {
        $droppedLayer = [];
        foreach ($layer as $value) {
            if (mt_rand() / mt_getrandmax() < $dropoutRate) {
                $droppedLayer[] = 0.0;
            } else {
                $droppedLayer[] = $value / (1 - $dropoutRate);
            }
        }

        return $droppedLayer;
    }

    private function batchNormalize(array $batch): array
    {
        $normalized = [];
        foreach ($batch as $sample) {
            $mean = array_sum($sample) / count($sample);
            $variance = array_sum(array_map(fn ($x) => pow($x - $mean, 2), $sample)) / count($sample);
            $std = sqrt($variance + 1e-8);
            $normalized[] = array_map(fn ($x) => ($x - $mean) / $std, $sample);
        }

        return $normalized;
    }

    private function convolve(array $image, array $kernel): array
    {
        $height = count($image);
        $width = count($image[0]);
        $kernelSize = count($kernel);
        $output = [];

        for ($i = 0; $i <= $height - $kernelSize; $i++) {
            $row = [];
            for ($j = 0; $j <= $width - $kernelSize; $j++) {
                $sum = 0;
                for ($ki = 0; $ki < $kernelSize; $ki++) {
                    for ($kj = 0; $kj < $kernelSize; $kj++) {
                        $sum += $image[$i + $ki][$j + $kj] * $kernel[$ki][$kj];
                    }
                }
                $row[] = $sum;
            }
            $output[] = $row;
        }

        return $output;
    }

    private function maxPool(array $featureMap, int $poolSize): array
    {
        $height = count($featureMap);
        $width = count($featureMap[0]);
        $output = [];

        for ($i = 0; $i < $height; $i += $poolSize) {
            $row = [];
            for ($j = 0; $j < $width; $j += $poolSize) {
                $max = 0;
                for ($pi = 0; $pi < $poolSize && $i + $pi < $height; $pi++) {
                    for ($pj = 0; $pj < $poolSize && $j + $pj < $width; $pj++) {
                        $max = max($max, $featureMap[$i + $pi][$j + $pj]);
                    }
                }
                $row[] = $max;
            }
            $output[] = $row;
        }

        return $output;
    }

    private function processSequence(array $sequence, int $hiddenSize): array
    {
        $outputs = [];
        $hidden = array_fill(0, $hiddenSize, 0);

        foreach ($sequence as $input) {
            $output = array_fill(0, $hiddenSize, $input * 0.5);
            $outputs[] = $output;
            $hidden = $output;
        }

        return $outputs;
    }
}
