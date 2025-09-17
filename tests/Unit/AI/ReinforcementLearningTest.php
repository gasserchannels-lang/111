<?php

namespace Tests\Unit\AI;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ReinforcementLearningTest extends TestCase
{
    #[Test]
    public function it_initializes_q_table(): void
    {
        $states = 10;
        $actions = 4;

        $qTable = $this->initializeQTable($states, $actions);

        $this->assertCount($states, $qTable);
        $this->assertCount($actions, $qTable[0]);

        // Check all values are initialized to 0
        foreach ($qTable as $state) {
            foreach ($state as $value) {
                $this->assertEquals(0, $value);
            }
        }
    }

    #[Test]
    public function it_implements_epsilon_greedy_policy(): void
    {
        $qValues = [0.1, 0.8, 0.3, 0.5];
        $epsilon = 0.1;

        $action = $this->epsilonGreedyPolicy($qValues, $epsilon);

        $this->assertGreaterThanOrEqual(0, $action);
        $this->assertLessThan(count($qValues), $action);
    }

    #[Test]
    public function it_updates_q_values(): void
    {
        $currentQ = 0.5;
        $reward = 1.0;
        $nextStateMaxQ = 0.8;
        $learningRate = 0.1;
        $discountFactor = 0.9;

        $newQ = $this->updateQValue($currentQ, $reward, $nextStateMaxQ, $learningRate, $discountFactor);

        $expectedQ = 0.5 + 0.1 * (1.0 + 0.9 * 0.8 - 0.5);
        $this->assertEquals($expectedQ, $newQ);
    }

    #[Test]
    public function it_implements_sarsa_algorithm(): void
    {
        $state = 0;
        $action = 1;
        $reward = 1.0;
        $nextState = 1;
        $nextAction = 2;
        $qTable = [[0, 0.5, 0, 0], [0, 0, 0.3, 0]];

        $updatedQTable = $this->sarsaUpdate($state, $action, $reward, $nextState, $nextAction, $qTable, 0.1, 0.9);

        $this->assertNotEquals($qTable, $updatedQTable);
    }

    #[Test]
    public function it_implements_q_learning_algorithm(): void
    {
        $state = 0;
        $action = 1;
        $reward = 1.0;
        $nextState = 1;
        $qTable = [[0, 0.5, 0, 0], [0, 0, 0.3, 0.8]];

        $updatedQTable = $this->qLearningUpdate($state, $action, $reward, $nextState, $qTable, 0.1, 0.9);

        $this->assertNotEquals($qTable, $updatedQTable);
    }

    #[Test]
    public function it_handles_exploration_vs_exploitation(): void
    {
        $qValues = [0.1, 0.8, 0.3, 0.5];

        // Test exploitation (epsilon = 0)
        $exploitationAction = $this->epsilonGreedyPolicy($qValues, 0.0);
        $this->assertEquals(1, $exploitationAction); // Should choose best action

        // Test exploration (epsilon = 1)
        $explorationAction = $this->epsilonGreedyPolicy($qValues, 1.0);
        $this->assertGreaterThanOrEqual(0, $explorationAction);
        $this->assertLessThan(count($qValues), $explorationAction);
    }

    #[Test]
    public function it_calculates_temporal_difference_error(): void
    {
        $currentQ = 0.5;
        $reward = 1.0;
        $nextStateMaxQ = 0.8;
        $discountFactor = 0.9;

        $tdError = $this->calculateTemporalDifferenceError($currentQ, $reward, $nextStateMaxQ, $discountFactor);

        $expectedError = 1.0 + 0.9 * 0.8 - 0.5;
        $this->assertEquals($expectedError, $tdError);
    }

    #[Test]
    public function it_implements_policy_gradient(): void
    {
        $state = [0.5, 0.3, 0.8];
        $action = 1;
        $reward = 1.0;
        $policy = [0.2, 0.6, 0.2];

        $gradient = $this->calculatePolicyGradient($state, $action, $reward, $policy);

        $this->assertIsArray($gradient);
        $this->assertCount(3, $gradient);
    }

    #[Test]
    public function it_handles_replay_buffer(): void
    {
        $buffer = $this->createReplayBuffer(5);

        // Add experiences
        $this->addExperience($buffer, [0, 1, 1.0, 1, false]);
        $this->addExperience($buffer, [1, 2, 0.5, 2, false]);
        $this->addExperience($buffer, [2, 0, -0.1, 0, true]);

        $this->assertEquals(3, $this->getBufferSize($buffer));

        // Test sampling
        $sample = $this->sampleFromBuffer($buffer, 2);
        $this->assertCount(2, $sample);
    }

    #[Test]
    public function it_implements_double_q_learning(): void
    {
        $state = 0;
        $action = 1;
        $reward = 1.0;
        $nextState = 1;
        $qTable1 = [[0, 0.5, 0, 0], [0, 0, 0.3, 0.8]];
        $qTable2 = [[0, 0.4, 0, 0], [0, 0, 0.7, 0.6]];

        $updatedQTable1 = $this->doubleQLearningUpdate($state, $action, $reward, $nextState, $qTable1, $qTable2, 0.1, 0.9);

        $this->assertNotEquals($qTable1, $updatedQTable1);
    }

    #[Test]
    public function it_handles_prioritized_experience_replay(): void
    {
        $buffer = $this->createPrioritizedReplayBuffer(5);

        // Add experiences with priorities
        $this->addPrioritizedExperience($buffer, [0, 1, 1.0, 1, false], 0.8);
        $this->addPrioritizedExperience($buffer, [1, 2, 0.5, 2, false], 0.3);
        $this->addPrioritizedExperience($buffer, [2, 0, -0.1, 0, true], 0.9);

        $this->assertEquals(3, $this->getBufferSize($buffer));

        // Test prioritized sampling
        $sample = $this->prioritizedSample($buffer, 2);
        $this->assertCount(2, $sample);
    }

    #[Test]
    public function it_implements_actor_critic_method(): void
    {
        $state = [0.5, 0.3, 0.8];
        $action = 1;
        $reward = 1.0;
        $nextState = [0.6, 0.4, 0.7];
        $actorWeights = [0.1, 0.2, 0.3];
        $criticWeights = [0.4, 0.5, 0.6];

        $updatedWeights = $this->actorCriticUpdate($state, $action, $reward, $nextState, $actorWeights, $criticWeights, 0.01, 0.9);

        $this->assertArrayHasKey('actor', $updatedWeights);
        $this->assertArrayHasKey('critic', $updatedWeights);
    }

    #[Test]
    public function it_handles_continuous_action_spaces(): void
    {
        $state = [0.5, 0.3, 0.8];
        $actionMean = 0.6;
        $actionStd = 0.2;

        $action = $this->sampleContinuousAction($actionMean, $actionStd);

        $this->assertIsFloat($action);
        $this->assertGreaterThanOrEqual(0, $action);
        $this->assertLessThanOrEqual(1, $action);
    }

    #[Test]
    public function it_implements_curriculum_learning(): void
    {
        $episodes = [1, 5, 10, 20];
        $difficulties = [0.1, 0.3, 0.6, 0.9];

        $curriculum = $this->createCurriculum($episodes, $difficulties);

        $this->assertArrayHasKey(1, $curriculum);
        $this->assertArrayHasKey(5, $curriculum);
        $this->assertArrayHasKey(10, $curriculum);
        $this->assertArrayHasKey(20, $curriculum);
    }

    #[Test]
    public function it_handles_multi_agent_reinforcement_learning(): void
    {
        $agents = ['agent1', 'agent2', 'agent3'];
        $qTables = [];

        foreach ($agents as $agent) {
            $qTables[$agent] = $this->initializeQTable(5, 3);
        }

        $state = 0;
        $actions = ['agent1' => 1, 'agent2' => 0, 'agent3' => 2];
        $rewards = ['agent1' => 1.0, 'agent2' => 0.5, 'agent3' => -0.2];
        $nextState = 1;

        $updatedQTables = $this->multiAgentUpdate($qTables, $state, $actions, $rewards, $nextState, 0.1, 0.9);

        $this->assertCount(3, $updatedQTables);
        foreach ($agents as $agent) {
            $this->assertNotEquals($qTables[$agent], $updatedQTables[$agent]);
        }
    }

    private function initializeQTable(int $states, int $actions): array
    {
        $qTable = [];
        for ($i = 0; $i < $states; $i++) {
            $qTable[$i] = array_fill(0, $actions, 0.0);
        }
        return $qTable;
    }

    private function epsilonGreedyPolicy(array $qValues, float $epsilon): int
    {
        if (rand(0, 100) / 100 < $epsilon) {
            // Explore: choose random action
            return rand(0, count($qValues) - 1);
        } else {
            // Exploit: choose best action
            return array_keys($qValues, max($qValues))[0];
        }
    }

    private function updateQValue(float $currentQ, float $reward, float $nextStateMaxQ, float $learningRate, float $discountFactor): float
    {
        return $currentQ + $learningRate * ($reward + $discountFactor * $nextStateMaxQ - $currentQ);
    }

    private function sarsaUpdate(int $state, int $action, float $reward, int $nextState, int $nextAction, array $qTable, float $learningRate, float $discountFactor): array
    {
        $updatedQTable = $qTable;
        $currentQ = $qTable[$state][$action];
        $nextQ = $qTable[$nextState][$nextAction];

        $updatedQTable[$state][$action] = $this->updateQValue($currentQ, $reward, $nextQ, $learningRate, $discountFactor);

        return $updatedQTable;
    }

    private function qLearningUpdate(int $state, int $action, float $reward, int $nextState, array $qTable, float $learningRate, float $discountFactor): array
    {
        $updatedQTable = $qTable;
        $currentQ = $qTable[$state][$action];
        $nextStateMaxQ = max($qTable[$nextState]);

        $updatedQTable[$state][$action] = $this->updateQValue($currentQ, $reward, $nextStateMaxQ, $learningRate, $discountFactor);

        return $updatedQTable;
    }

    private function calculateTemporalDifferenceError(float $currentQ, float $reward, float $nextStateMaxQ, float $discountFactor): float
    {
        return $reward + $discountFactor * $nextStateMaxQ - $currentQ;
    }

    private function calculatePolicyGradient(array $state, int $action, float $reward, array $policy): array
    {
        $gradient = [];
        for ($i = 0; $i < count($state); $i++) {
            if ($i === $action) {
                $gradient[$i] = $reward * (1 - $policy[$i]);
            } else {
                $gradient[$i] = -$reward * $policy[$i];
            }
        }
        return $gradient;
    }

    private function createReplayBuffer(int $capacity): array
    {
        return [
            'capacity' => $capacity,
            'experiences' => [],
            'size' => 0
        ];
    }

    private function addExperience(array &$buffer, array $experience): void
    {
        if ($buffer['size'] < $buffer['capacity']) {
            $buffer['experiences'][] = $experience;
            $buffer['size']++;
        } else {
            // Replace oldest experience
            array_shift($buffer['experiences']);
            $buffer['experiences'][] = $experience;
        }
    }

    private function getBufferSize(array $buffer): int
    {
        return $buffer['size'];
    }

    private function sampleFromBuffer(array $buffer, int $batchSize): array
    {
        if ($buffer['size'] < $batchSize) {
            return $buffer['experiences'];
        }

        $indices = array_rand($buffer['experiences'], $batchSize);
        $sample = [];
        foreach ($indices as $index) {
            $sample[] = $buffer['experiences'][$index];
        }

        return $sample;
    }

    private function doubleQLearningUpdate(int $state, int $action, float $reward, int $nextState, array $qTable1, array $qTable2, float $learningRate, float $discountFactor): array
    {
        $updatedQTable1 = $qTable1;
        $currentQ = $qTable1[$state][$action];

        // Find best action in next state using Q1
        $bestAction = array_keys($qTable1[$nextState], max($qTable1[$nextState]))[0];

        // Use Q2 to get the value of that action
        $nextQ = $qTable2[$nextState][$bestAction];

        $updatedQTable1[$state][$action] = $this->updateQValue($currentQ, $reward, $nextQ, $learningRate, $discountFactor);

        return $updatedQTable1;
    }

    private function createPrioritizedReplayBuffer(int $capacity): array
    {
        return [
            'capacity' => $capacity,
            'experiences' => [],
            'priorities' => [],
            'size' => 0
        ];
    }

    private function addPrioritizedExperience(array &$buffer, array $experience, float $priority): void
    {
        if ($buffer['size'] < $buffer['capacity']) {
            $buffer['experiences'][] = $experience;
            $buffer['priorities'][] = $priority;
            $buffer['size']++;
        } else {
            // Replace oldest experience
            array_shift($buffer['experiences']);
            array_shift($buffer['priorities']);
            $buffer['experiences'][] = $experience;
            $buffer['priorities'][] = $priority;
        }
    }

    private function prioritizedSample(array $buffer, int $batchSize): array
    {
        if ($buffer['size'] < $batchSize) {
            return $buffer['experiences'];
        }

        // Sample based on priorities
        $totalPriority = array_sum($buffer['priorities']);
        $probabilities = array_map(function ($p) use ($totalPriority) {
            return $p / $totalPriority;
        }, $buffer['priorities']);

        $sample = [];
        for ($i = 0; $i < $batchSize; $i++) {
            $rand = rand(0, 100) / 100;
            $cumulative = 0;
            for ($j = 0; $j < count($probabilities); $j++) {
                $cumulative += $probabilities[$j];
                if ($rand <= $cumulative) {
                    $sample[] = $buffer['experiences'][$j];
                    break;
                }
            }
        }

        return $sample;
    }

    private function actorCriticUpdate(array $state, int $action, float $reward, array $nextState, array $actorWeights, array $criticWeights, float $learningRate, float $discountFactor): array
    {
        // Simplified actor-critic update
        $tdError = $this->calculateTemporalDifferenceError(
            $this->calculateValue($state, $criticWeights),
            $reward,
            $this->calculateValue($nextState, $criticWeights),
            $discountFactor
        );

        // Update actor weights
        $newActorWeights = [];
        for ($i = 0; $i < count($actorWeights); $i++) {
            $newActorWeights[$i] = $actorWeights[$i] + $learningRate * $tdError * $state[$i];
        }

        // Update critic weights
        $newCriticWeights = [];
        for ($i = 0; $i < count($criticWeights); $i++) {
            $newCriticWeights[$i] = $criticWeights[$i] + $learningRate * $tdError * $state[$i];
        }

        return [
            'actor' => $newActorWeights,
            'critic' => $newCriticWeights
        ];
    }

    private function calculateValue(array $state, array $weights): float
    {
        $value = 0;
        for ($i = 0; $i < count($state); $i++) {
            $value += $state[$i] * $weights[$i];
        }
        return $value;
    }

    private function sampleContinuousAction(float $mean, float $std): float
    {
        // Simplified continuous action sampling
        $random = rand(0, 100) / 100;
        return max(0, min(1, $mean + $std * ($random - 0.5)));
    }

    private function createCurriculum(array $episodes, array $difficulties): array
    {
        $curriculum = [];
        for ($i = 0; $i < count($episodes); $i++) {
            $curriculum[$episodes[$i]] = $difficulties[$i];
        }
        return $curriculum;
    }

    private function multiAgentUpdate(array $qTables, int $state, array $actions, array $rewards, int $nextState, float $learningRate, float $discountFactor): array
    {
        $updatedQTables = [];

        foreach ($qTables as $agent => $qTable) {
            $action = $actions[$agent];
            $reward = $rewards[$agent];

            $updatedQTables[$agent] = $this->qLearningUpdate($state, $action, $reward, $nextState, $qTable, $learningRate, $discountFactor);
        }

        return $updatedQTables;
    }
}
