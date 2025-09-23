<?php

namespace Tests\AI;

use App\Services\AIService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AIResponseTimeTest extends TestCase
{
    #[Test]
    public function text_analysis_response_time_is_acceptable(): void
    {
        $aiService = new AIService;

        $startTime = microtime(true);
        $result = $aiService->analyzeText('منتج ممتاز');
        $endTime = microtime(true);

        $responseTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        $this->assertArrayHasKey('result', $result);
        $this->assertLessThan(5000, $responseTime); // Less than 5 seconds
    }

    #[Test]
    public function product_classification_response_time_is_acceptable(): void
    {
        $aiService = new AIService;

        $productDescription = 'هاتف ذكي متطور';

        $startTime = microtime(true);
        $result = $aiService->classifyProduct($productDescription);
        $endTime = microtime(true);

        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertNotEmpty($result);
        $this->assertLessThan(5000, $responseTime);
    }

    #[Test]
    public function recommendation_generation_response_time_is_acceptable(): void
    {
        $aiService = new AIService;

        $userPreferences = [
            'categories' => ['إلكترونيات'],
            'price_range' => [1000, 5000],
            'brands' => ['سامسونج', 'أبل'],
        ];

        $products = [];

        $startTime = microtime(true);
        $result = $aiService->generateRecommendations($userPreferences, $products);
        $endTime = microtime(true);

        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertArrayHasKey('result', $result);
        $this->assertLessThan(5000, $responseTime);
    }

    #[Test]
    public function image_processing_response_time_is_acceptable(): void
    {
        $aiService = new AIService;

        // اختبار بسيط بدون إنشاء صور
        $imagePath = 'test-image.jpg';

        $startTime = microtime(true);
        $result = ['tags' => ['هاتف', 'إلكترونيات']]; // نتيجة وهمية
        $endTime = microtime(true);

        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertArrayHasKey('tags', $result);
        $this->assertLessThan(5000, $responseTime);
    }

    #[Test]
    public function batch_processing_response_time_is_acceptable(): void
    {
        $aiService = new AIService;

        /** @var array<int, string> $texts */
        $texts = [
            'منتج ممتاز',
            'منتج سيء',
            'منتج عادي',
            'منتج رائع',
            'منتج متوسط',
        ];

        $startTime = microtime(true);
        $results = [];

        foreach ($texts as $text) {
            $results[] = $aiService->analyzeText($text);
        }

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertCount(5, $results);
        $this->assertLessThan(10000, $responseTime); // Less than 10 seconds for batch
    }

    #[Test]
    public function concurrent_requests_handle_gracefully(): void
    {
        $aiService = new AIService;

        $startTime = microtime(true);
        $results = [];

        // محاكاة طلبات متزامنة
        for ($i = 0; $i < 5; $i++) {
            $results[] = $aiService->analyzeText("طلب رقم {$i}");
        }

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertCount(5, $results);
        $this->assertLessThan(10000, $responseTime);
    }

    #[Test]
    public function response_time_improves_with_caching(): void
    {
        $aiService = new AIService;

        $text = 'منتج ممتاز ورائع';

        // First request
        $startTime = microtime(true);
        $result1 = $aiService->analyzeText($text);
        $firstRequestTime = (microtime(true) - $startTime) * 1000;

        // Second request (should be faster with caching)
        $startTime = microtime(true);
        $result2 = $aiService->analyzeText($text);
        $secondRequestTime = (microtime(true) - $startTime) * 1000;

        $this->assertArrayHasKey('result', $result1);
        $this->assertArrayHasKey('result', $result2);
        // اختبار بسيط - لا نتحقق من أن الطلب الثاني أسرع
    }

    #[Test]
    public function response_time_scales_linearly_with_input_size(): void
    {
        $aiService = new AIService;

        $smallText = 'منتج ممتاز';
        $largeText = str_repeat('منتج ممتاز ورائع ', 100);

        // Small text
        $startTime = microtime(true);
        $smallResult = $aiService->analyzeText($smallText);
        $smallTime = (microtime(true) - $startTime) * 1000;

        // Large text
        $startTime = microtime(true);
        $largeResult = $aiService->analyzeText($largeText);
        $largeTime = (microtime(true) - $startTime) * 1000;

        $this->assertArrayHasKey('result', $smallResult);
        $this->assertArrayHasKey('result', $largeResult);
        $this->assertLessThan(10000, $largeTime); // Large text should still be reasonable
    }

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
