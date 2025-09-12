<?php

namespace Tests\AI;

use App\Services\AIService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AIResponseTimeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function text_analysis_response_time_is_acceptable()
    {
        $aiService = new AIService;
        $text = 'هذا منتج رائع وسعره مناسب للجميع';

        $startTime = microtime(true);
        $result = $aiService->analyzeText($text);
        $endTime = microtime(true);

        $responseTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        $this->assertLessThan(3000, $responseTime); // Should be less than 3 seconds
        $this->assertIsArray($result);
    }

    /** @test */
    public function product_classification_response_time_is_acceptable()
    {
        $aiService = new AIService;
        $productData = [
            'name' => 'لابتوب ديل',
            'description' => 'جهاز كمبيوتر محمول عالي الأداء',
            'price' => 5000,
        ];

        $startTime = microtime(true);
        $result = $aiService->classifyProduct($productData);
        $endTime = microtime(true);

        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(2000, $responseTime); // Should be less than 2 seconds
        $this->assertIsString($result);
    }

    /** @test */
    public function recommendation_generation_response_time_is_acceptable()
    {
        $aiService = new AIService;
        $userPreferences = [
            'categories' => ['إلكترونيات', 'ملابس'],
            'price_range' => [100, 1000],
            'brands' => ['سامسونج', 'أبل'],
        ];

        $startTime = microtime(true);
        $result = $aiService->generateRecommendations($userPreferences);
        $endTime = microtime(true);

        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(5000, $responseTime); // Should be less than 5 seconds
        $this->assertIsArray($result);
    }

    /** @test */
    public function image_processing_response_time_is_acceptable()
    {
        $aiService = new AIService;

        // Create a test image
        $image = imagecreate(200, 200);
        $white = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $white);

        $imagePath = storage_path('app/test-response-time.jpg');
        imagejpeg($image, $imagePath);
        imagedestroy($image);

        $startTime = microtime(true);
        $result = $aiService->processImage($imagePath);
        $endTime = microtime(true);

        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(10000, $responseTime); // Should be less than 10 seconds
        $this->assertIsArray($result);
    }

    /** @test */
    public function batch_processing_response_time_is_acceptable()
    {
        $aiService = new AIService;
        $texts = [
            'منتج ممتاز',
            'منتج سيء',
            'منتج عادي',
            'منتج رائع',
            'منتج مخيب للآمال',
        ];

        $startTime = microtime(true);
        $results = [];

        foreach ($texts as $text) {
            $results[] = $aiService->analyzeText($text);
        }

        $endTime = microtime(true);

        $totalResponseTime = ($endTime - $startTime) * 1000;
        $averageResponseTime = $totalResponseTime / count($texts);

        $this->assertLessThan(2000, $averageResponseTime); // Average should be less than 2 seconds
        $this->assertCount(5, $results);
    }

    /** @test */
    public function concurrent_requests_handle_gracefully()
    {
        $aiService = new AIService;
        $text = 'اختبار الطلبات المتزامنة';

        $startTime = microtime(true);

        // Simulate concurrent requests
        $promises = [];
        for ($i = 0; $i < 5; $i++) {
            $promises[] = $aiService->analyzeText($text);
        }

        $endTime = microtime(true);

        $totalResponseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(10000, $totalResponseTime); // Total should be less than 10 seconds
        $this->assertCount(5, $promises);
    }

    /** @test */
    public function response_time_improves_with_caching()
    {
        $aiService = new AIService;
        $text = 'نص للاختبار مع التخزين المؤقت';

        // First request (should be slow)
        $startTime = microtime(true);
        $result1 = $aiService->analyzeText($text);
        $firstRequestTime = (microtime(true) - $startTime) * 1000;

        // Second request (should be fast with caching)
        $startTime = microtime(true);
        $result2 = $aiService->analyzeText($text);
        $secondRequestTime = (microtime(true) - $startTime) * 1000;

        $this->assertLessThan($firstRequestTime, $secondRequestTime);
        $this->assertEquals($result1, $result2);
    }

    /** @test */
    public function response_time_under_load_is_acceptable()
    {
        $aiService = new AIService;
        $texts = array_fill(0, 10, 'اختبار تحت الضغط');

        $startTime = microtime(true);

        $results = [];
        foreach ($texts as $text) {
            $results[] = $aiService->analyzeText($text);
        }

        $endTime = microtime(true);

        $totalTime = ($endTime - $startTime) * 1000;
        $averageTime = $totalTime / count($texts);

        $this->assertLessThan(3000, $averageTime); // Average should be less than 3 seconds
        $this->assertCount(10, $results);
    }
}
