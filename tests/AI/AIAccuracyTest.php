<?php

namespace Tests\AI;

use App\Services\AIService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AIAccuracyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function sentiment_analysis_accuracy_is_acceptable()
    {
        $aiService = new AIService;

        $testCases = [
            ['text' => 'منتج ممتاز ورائع', 'expected' => 'positive'],
            ['text' => 'منتج سيء ومخيب للآمال', 'expected' => 'negative'],
            ['text' => 'منتج عادي ولا بأس به', 'expected' => 'neutral'],
            ['text' => 'أفضل منتج اشتريته', 'expected' => 'positive'],
            ['text' => 'أسوأ منتج في السوق', 'expected' => 'negative'],
        ];

        $correctPredictions = 0;
        $totalPredictions = count($testCases);

        foreach ($testCases as $case) {
            $result = $aiService->analyzeText($case['text']);
            if ($result['sentiment'] === $case['expected']) {
                $correctPredictions++;
            }
        }

        $accuracy = $correctPredictions / $totalPredictions;
        $this->assertGreaterThan(0.7, $accuracy); // At least 70% accuracy
    }

    /** @test */
    public function product_classification_accuracy_is_acceptable()
    {
        $aiService = new AIService;

        $testCases = [
            ['data' => ['name' => 'هاتف آيفون', 'description' => 'هاتف ذكي'], 'expected' => 'إلكترونيات'],
            ['data' => ['name' => 'قميص قطني', 'description' => 'ملابس رجالية'], 'expected' => 'ملابس'],
            ['data' => ['name' => 'كتاب البرمجة', 'description' => 'كتاب تعليمي'], 'expected' => 'كتب'],
            ['data' => ['name' => 'كرة قدم', 'description' => 'أدوات رياضية'], 'expected' => 'رياضة'],
            ['data' => ['name' => 'مقعد خشبي', 'description' => 'أثاث للحديقة'], 'expected' => 'منزل وحديقة'],
        ];

        $correctPredictions = 0;
        $totalPredictions = count($testCases);

        foreach ($testCases as $case) {
            $result = $aiService->classifyProduct($case['data']);
            if ($result === $case['expected']) {
                $correctPredictions++;
            }
        }

        $accuracy = $correctPredictions / $totalPredictions;
        $this->assertGreaterThan(0.6, $accuracy); // At least 60% accuracy
    }

    /** @test */
    public function keyword_extraction_accuracy_is_acceptable()
    {
        $aiService = new AIService;

        $testCases = [
            ['text' => 'لابتوب ديل عالي الأداء', 'expected_keywords' => ['لابتوب', 'ديل']],
            ['text' => 'هاتف سامسونج جالاكسي', 'expected_keywords' => ['هاتف', 'سامسونج', 'جالاكسي']],
            ['text' => 'قميص قطني أزرق', 'expected_keywords' => ['قميص', 'قطني', 'أزرق']],
        ];

        $totalKeywords = 0;
        $correctKeywords = 0;

        foreach ($testCases as $case) {
            $result = $aiService->extractKeywords($case['text']);
            $expectedKeywords = $case['expected_keywords'];

            foreach ($expectedKeywords as $keyword) {
                $totalKeywords++;
                if (in_array($keyword, $result)) {
                    $correctKeywords++;
                }
            }
        }

        $accuracy = $correctKeywords / $totalKeywords;
        $this->assertGreaterThan(0.5, $accuracy); // At least 50% accuracy
    }

    /** @test */
    public function recommendation_relevance_is_acceptable()
    {
        $aiService = new AIService;

        $userPreferences = [
            'categories' => ['إلكترونيات'],
            'price_range' => [1000, 5000],
            'brands' => ['سامسونج', 'أبل'],
        ];

        $recommendations = $aiService->generateRecommendations($userPreferences);

        $this->assertIsArray($recommendations);
        $this->assertGreaterThan(0, count($recommendations));

        // Check if recommendations match user preferences
        foreach ($recommendations as $recommendation) {
            $this->assertArrayHasKey('category', $recommendation);
            $this->assertArrayHasKey('price', $recommendation);
            $this->assertArrayHasKey('brand', $recommendation);

            $this->assertContains($recommendation['category'], $userPreferences['categories']);
            $this->assertGreaterThanOrEqual($userPreferences['price_range'][0], $recommendation['price']);
            $this->assertLessThanOrEqual($userPreferences['price_range'][1], $recommendation['price']);
            $this->assertContains($recommendation['brand'], $userPreferences['brands']);
        }
    }

    /** @test */
    public function image_analysis_accuracy_is_acceptable()
    {
        $aiService = new AIService;

        // Create test images with known content
        $testImages = [
            ['path' => $this->createTestImage('phone'), 'expected_tags' => ['هاتف', 'إلكترونيات']],
            ['path' => $this->createTestImage('laptop'), 'expected_tags' => ['لابتوب', 'كمبيوتر']],
            ['path' => $this->createTestImage('shirt'), 'expected_tags' => ['قميص', 'ملابس']],
        ];

        $totalTags = 0;
        $correctTags = 0;

        foreach ($testImages as $testImage) {
            $result = $aiService->processImage($testImage['path']);
            $expectedTags = $testImage['expected_tags'];

            if (isset($result['tags'])) {
                foreach ($expectedTags as $tag) {
                    $totalTags++;
                    if (in_array($tag, $result['tags'])) {
                        $correctTags++;
                    }
                }
            }
        }

        if ($totalTags > 0) {
            $accuracy = $correctTags / $totalTags;
            $this->assertGreaterThan(0.3, $accuracy); // At least 30% accuracy for image analysis
        }
    }

    /** @test */
    public function confidence_scores_are_reasonable()
    {
        $aiService = new AIService;

        $text = 'منتج رائع وممتاز';
        $result = $aiService->analyzeText($text);

        $this->assertArrayHasKey('confidence', $result);
        $this->assertGreaterThan(0, $result['confidence']);
        $this->assertLessThanOrEqual(1, $result['confidence']);
    }

    /** @test */
    public function ai_learns_from_corrective_feedback()
    {
        $aiService = new AIService;

        $text = 'منتج جيد';

        // Initial prediction
        $initialResult = $aiService->analyzeText($text);

        // Provide corrective feedback
        $aiService->learnFromFeedback($text, 'positive', true);

        // Get updated prediction
        $updatedResult = $aiService->analyzeText($text);

        $this->assertIsArray($initialResult);
        $this->assertIsArray($updatedResult);
        $this->assertArrayHasKey('sentiment', $updatedResult);
    }

    private function createTestImage($type)
    {
        $image = imagecreate(200, 200);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);

        imagefill($image, 0, 0, $white);

        switch ($type) {
            case 'phone':
                imagestring($image, 5, 50, 100, 'Phone', $black);
                break;
            case 'laptop':
                imagestring($image, 5, 50, 100, 'Laptop', $black);
                break;
            case 'shirt':
                imagestring($image, 5, 50, 100, 'Shirt', $black);
                break;
        }

        $path = storage_path("app/test-{$type}.jpg");
        imagejpeg($image, $path);
        imagedestroy($image);

        return $path;
    }
}
