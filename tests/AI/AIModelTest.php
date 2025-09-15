<?php

namespace Tests\AI;

use App\Services\AIService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AIModelTest extends TestCase
{
    

    #[Test]
    public function ai_model_initializes_correctly()
    {
        $aiService = new AIService;
        $this->assertInstanceOf(AIService::class, $aiService);
    }

    #[Test]
    public function ai_can_analyze_text()
    {
        $aiService = new AIService;
        $text = 'هذا منتج رائع وسعره مناسب';

        $result = $aiService->analyzeText($text);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('sentiment', $result);
        $this->assertArrayHasKey('confidence', $result);
    }

    #[Test]
    public function ai_can_classify_products()
    {
        $aiService = new AIService;
        $productData = [
            'name' => 'لابتوب ديل',
            'description' => 'جهاز كمبيوتر محمول عالي الأداء',
            'price' => 5000,
        ];

        $category = $aiService->classifyProduct($productData);

        $this->assertIsString($category);
        $this->assertNotEmpty($category);
    }

    #[Test]
    public function ai_can_generate_recommendations()
    {
        $aiService = new AIService;
        $userPreferences = [
            'categories' => ['إلكترونيات', 'ملابس'],
            'price_range' => [100, 1000],
            'brands' => ['سامسونج', 'أبل'],
        ];

        $recommendations = $aiService->generateRecommendations($userPreferences);

        $this->assertIsArray($recommendations);
        $this->assertGreaterThan(0, count($recommendations));
    }

    #[Test]
    public function ai_can_process_images()
    {
        $aiService = new AIService;
        $imagePath = storage_path('app/test-image.jpg');

        // Create a test image if it doesn't exist
        if (! file_exists($imagePath)) {
            $image = imagecreate(100, 100);
            imagejpeg($image, $imagePath);
            imagedestroy($image);
        }

        $result = $aiService->processImage($imagePath);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('objects', $result);
        $this->assertArrayHasKey('tags', $result);
    }

    #[Test]
    public function ai_response_time_is_acceptable()
    {
        $aiService = new AIService;
        $text = 'تحليل هذا النص';

        $startTime = microtime(true);
        $result = $aiService->analyzeText($text);
        $endTime = microtime(true);

        $responseTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        $this->assertLessThan(5000, $responseTime); // Should be less than 5 seconds
        $this->assertIsArray($result);
    }

    #[Test]
    public function ai_accuracy_is_acceptable()
    {
        $aiService = new AIService;

        $testCases = [
            ['text' => 'منتج ممتاز', 'expected_sentiment' => 'positive'],
            ['text' => 'منتج سيء', 'expected_sentiment' => 'negative'],
            ['text' => 'منتج عادي', 'expected_sentiment' => 'neutral'],
        ];

        $correctPredictions = 0;

        foreach ($testCases as $case) {
            $result = $aiService->analyzeText($case['text']);
            if ($result['sentiment'] === $case['expected_sentiment']) {
                $correctPredictions++;
            }
        }

        $accuracy = $correctPredictions / count($testCases);
        $this->assertGreaterThan(0.7, $accuracy); // At least 70% accuracy
    }

    #[Test]
    public function ai_can_learn_from_feedback()
    {
        $aiService = new AIService;

        $initialResult = $aiService->analyzeText('منتج جيد');

        // Provide feedback
        $aiService->learnFromFeedback('منتج جيد', 'positive', true);

        $updatedResult = $aiService->analyzeText('منتج جيد');

        $this->assertIsArray($updatedResult);
        $this->assertArrayHasKey('sentiment', $updatedResult);
    }
}
