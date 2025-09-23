<?php

namespace Tests\AI;

use App\Services\AIService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AIModelTest extends TestCase
{
    #[Test]
    public function ai_model_initializes_correctly(): void
    {
        $aiService = new AIService;

        $this->assertInstanceOf(AIService::class, $aiService);
    }

    #[Test]
    public function ai_can_analyze_text(): void
    {
        $aiService = new AIService;

        $text = 'منتج ممتاز ورائع';
        $result = $aiService->analyzeText($text);

        $this->assertArrayHasKey('result', $result);
        // اختبار بسيط للتأكد من أن النتيجة صحيحة
    }

    #[Test]
    public function ai_can_classify_products(): void
    {
        $aiService = new AIService;

        $productDescription = 'هاتف ذكي متطور';

        $result = $aiService->classifyProduct($productDescription);

        $this->assertNotEmpty($result);
    }

    #[Test]
    public function ai_can_generate_recommendations(): void
    {
        $aiService = new AIService;

        $userPreferences = [
            'categories' => ['إلكترونيات'],
            'price_range' => [1000, 5000],
            'brands' => ['سامسونج', 'أبل'],
        ];

        $products = [];
        $recommendations = $aiService->generateRecommendations($userPreferences, $products);

        $this->assertArrayHasKey('result', $recommendations);
    }

    #[Test]
    public function ai_handles_empty_input(): void
    {
        $aiService = new AIService;

        $result = $aiService->analyzeText('');

        $this->assertArrayHasKey('result', $result);
    }

    #[Test]
    public function ai_handles_special_characters(): void
    {
        $aiService = new AIService;

        $text = 'منتج ممتاز! @#$%^&*()';
        $result = $aiService->analyzeText($text);

        $this->assertArrayHasKey('result', $result);
    }

    #[Test]
    public function ai_handles_unicode_text(): void
    {
        $aiService = new AIService;

        $text = 'منتج ممتاز 🚀 💯';
        $result = $aiService->analyzeText($text);

        $this->assertArrayHasKey('result', $result);
    }

    #[Test]
    public function ai_handles_long_text(): void
    {
        $aiService = new AIService;

        $longText = str_repeat('منتج ممتاز ورائع ', 100);
        $result = $aiService->analyzeText($longText);

        $this->assertArrayHasKey('result', $result);
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
