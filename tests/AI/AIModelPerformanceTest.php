<?php

namespace Tests\AI;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AIModelPerformanceTest extends TestCase
{
    

    #[Test]
    public function ai_model_responds_within_acceptable_time()
    {
        $startTime = microtime(true);

        // محاكاة استدعاء نموذج الذكاء الاصطناعي
        $response = $this->postJson('/api/ai/analyze', [
            'text' => 'Test product analysis request',
            'type' => 'product_analysis',
        ]);

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000; // بالميلي ثانية

        // يجب أن يكون وقت الاستجابة أقل من 5 ثوان
        $this->assertLessThan(5000, $responseTime);
        $this->assertEquals(200, $response->status());

        Log::info("AI Model Response Time: {$responseTime}ms");
    }

    #[Test]
    public function ai_model_handles_large_inputs()
    {
        $largeText = str_repeat('This is a test product description. ', 1000);

        $response = $this->postJson('/api/ai/analyze', [
            'text' => $largeText,
            'type' => 'product_analysis',
        ]);

        $this->assertEquals(200, $response->status());
        $this->assertArrayHasKey('analysis', $response->json());
    }

    #[Test]
    public function ai_model_memory_usage_is_reasonable()
    {
        $initialMemory = memory_get_usage(true);

        // تشغيل عدة طلبات متتالية
        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/ai/analyze', [
                'text' => "Test request number {$i}",
                'type' => 'product_analysis',
            ]);
        }

        $finalMemory = memory_get_usage(true);
        $memoryIncrease = $finalMemory - $initialMemory;

        // يجب أن تكون زيادة الذاكرة أقل من 50MB
        $this->assertLessThan(50 * 1024 * 1024, $memoryIncrease);

        Log::info('AI Memory Usage: '.($memoryIncrease / 1024 / 1024).'MB');
    }

    #[Test]
    public function ai_model_accuracy_is_acceptable()
    {
        $testCases = [
            ['input' => 'This is a great product', 'expected_sentiment' => 'positive'],
            ['input' => 'This product is terrible', 'expected_sentiment' => 'negative'],
            ['input' => 'The product is okay', 'expected_sentiment' => 'neutral'],
        ];

        $correctPredictions = 0;

        foreach ($testCases as $case) {
            $response = $this->postJson('/api/ai/analyze', [
                'text' => $case['input'],
                'type' => 'sentiment_analysis',
            ]);

            if ($response->status() === 200) {
                $result = $response->json();
                if (isset($result['sentiment']) &&
                    $result['sentiment'] === $case['expected_sentiment']) {
                    $correctPredictions++;
                }
            }
        }

        $accuracy = ($correctPredictions / count($testCases)) * 100;

        // يجب أن تكون الدقة أعلى من 70%
        $this->assertGreaterThan(70, $accuracy);

        Log::info("AI Accuracy: {$accuracy}%");
    }
}
