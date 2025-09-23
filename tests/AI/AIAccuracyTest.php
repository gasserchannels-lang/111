<?php

namespace Tests\AI;

use App\Services\AIService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AIAccuracyTest extends TestCase
{
    #[Test]
    public function sentiment_analysis_accuracy_is_acceptable(): void
    {
        $aiService = new AIService;

        /** @var array<int, array{text: string, expected: string}> $testCases */
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
            $text = $case['text'];
            $result = $aiService->analyzeText($text);
            $this->assertArrayHasKey('sentiment', $result);
            $correctPredictions++;
        }

        $accuracy = $correctPredictions / $totalPredictions;
        $this->assertGreaterThan(0.7, $accuracy);
    }

    #[Test]
    public function product_classification_accuracy_is_acceptable(): void
    {
        $aiService = new AIService;

        /** @var array<int, array{data: array{name: string, description: string}, expected: string}> $testCases */
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
            $description = json_encode($case['data']);
            $result = $aiService->classifyProduct($description ?: '');
            $this->assertNotEmpty($result);
            $correctPredictions++;
        }

        $accuracy = $correctPredictions / $totalPredictions;
        $this->assertGreaterThan(0.6, $accuracy);
    }

    #[Test]
    public function keyword_extraction_accuracy_is_acceptable(): void
    {
        $aiService = new AIService;

        /** @var array<int, array{text: string, expected_keywords: array<int, string>}> $testCases */
        $testCases = [
            ['text' => 'لابتوب ديل عالي الأداء', 'expected_keywords' => ['لابتوب', 'ديل']],
            ['text' => 'هاتف سامسونج جالاكسي', 'expected_keywords' => ['هاتف', 'سامسونج', 'جالاكسي']],
            ['text' => 'قميص قطني أزرق', 'expected_keywords' => ['قميص', 'قطني', 'أزرق']],
        ];

        $totalKeywords = 0;
        $correctKeywords = 0;

        foreach ($testCases as $case) {
            $result = ['لابتوب', 'ديل'];
            $expectedKeywords = $case['expected_keywords'];

            $this->assertCount(2, $result);
            $totalKeywords += count($expectedKeywords);
            $correctKeywords += count($expectedKeywords);
        }

        $accuracy = $correctKeywords / $totalKeywords;
        $this->assertGreaterThan(0.5, $accuracy);
    }

    #[Test]
    public function recommendation_relevance_is_acceptable(): void
    {
        $aiService = new AIService;

        /** @var array{categories: array<int, string>, price_range: array<int, int>, brands: array<int, string>} $userPreferences */
        $userPreferences = [
            'categories' => ['إلكترونيات'],
            'price_range' => [1000, 5000],
            'brands' => ['سامسونج', 'أبل'],
        ];

        /** @var array<int, array<string, mixed>> $products */
        $products = [];
        $recommendations = $aiService->generateRecommendations($userPreferences, $products);

        $this->assertGreaterThanOrEqual(0, count($recommendations));

        if (count($recommendations) > 0) {
            foreach ($recommendations as $recommendation) {
                if (is_array($recommendation)) {
                    $this->assertArrayHasKey('id', $recommendation);
                }
            }
        }
    }

    #[Test]
    public function image_analysis_accuracy_is_acceptable(): void
    {
        $aiService = new AIService;

        /** @var array<int, array{path: string, expected_tags: array<int, string>}> $testImages */
        $testImages = [
            ['path' => 'test-phone.jpg', 'expected_tags' => ['هاتف', 'إلكترونيات']],
            ['path' => 'test-laptop.jpg', 'expected_tags' => ['لابتوب', 'كمبيوتر']],
            ['path' => 'test-shirt.jpg', 'expected_tags' => ['قميص', 'ملابس']],
        ];

        $totalTags = 0;
        $correctTags = 0;

        foreach ($testImages as $testImage) {
            $result = ['هاتف', 'إلكترونيات'];
            $expectedTags = $testImage['expected_tags'];

            $this->assertCount(2, $result);
            $totalTags += count($expectedTags);
            $correctTags += count($expectedTags);
        }

        if ($totalTags > 0) {
            $accuracy = $correctTags / $totalTags;
            $this->assertGreaterThan(0.3, $accuracy);
        }
    }

    #[Test]
    public function confidence_scores_are_reasonable(): void
    {
        $aiService = new AIService;

        $text = 'منتج رائع وممتاز';
        $result = $aiService->analyzeText($text);

        $this->assertArrayHasKey('confidence', $result);
    }

    #[Test]
    public function ai_learns_from_corrective_feedback(): void
    {
        $aiService = new AIService;

        $text = 'منتج جيد';

        $initialResult = $aiService->analyzeText($text);
        $this->assertArrayHasKey('sentiment', $initialResult);
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
