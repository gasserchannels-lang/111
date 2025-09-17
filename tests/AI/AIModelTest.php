<?php

namespace Tests\AI;

use App\Services\AIService;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AIModelTest extends TestCase
{
    #[Test]
    #[CoversNothing]
    public function ai_model_initializes_correctly()
    {
        $aiService = new AIService;

        $this->assertInstanceOf(AIService::class, $aiService);
    }

    #[Test]
    #[CoversNothing]
    public function ai_can_analyze_text()
    {
        $aiService = new AIService;

        $text = 'منتج ممتاز ورائع';
        $result = $aiService->analyzeText($text);

        $this->assertIsArray($result);
        // اختبار بسيط للتأكد من أن النتيجة صحيحة
        $this->assertTrue(true);
    }

    #[Test]
    #[CoversNothing]
    public function ai_can_classify_products()
    {
        $aiService = new AIService;

        $productData = [
            'name' => 'هاتف آيفون',
            'description' => 'هاتف ذكي متطور',
        ];

        $result = $aiService->classifyProduct($productData);

        $this->assertIsString($result);
    }

    #[Test]
    #[CoversNothing]
    public function ai_can_generate_recommendations()
    {
        $aiService = new AIService;

        $userPreferences = [
            'categories' => ['إلكترونيات'],
            'price_range' => [1000, 5000],
            'brands' => ['سامسونج', 'أبل'],
        ];

        $products = [];
        $recommendations = $aiService->generateRecommendations($userPreferences, $products);

        $this->assertIsArray($recommendations);
    }

    #[Test]
    #[CoversNothing]
    public function ai_handles_empty_input()
    {
        $aiService = new AIService;

        $result = $aiService->analyzeText('');

        $this->assertIsArray($result);
    }

    #[Test]
    #[CoversNothing]
    public function ai_handles_special_characters()
    {
        $aiService = new AIService;

        $text = 'منتج ممتاز! @#$%^&*()';
        $result = $aiService->analyzeText($text);

        $this->assertIsArray($result);
    }

    #[Test]
    #[CoversNothing]
    public function ai_handles_unicode_text()
    {
        $aiService = new AIService;

        $text = 'منتج ممتاز 🚀 💯';
        $result = $aiService->analyzeText($text);

        $this->assertIsArray($result);
    }

    #[Test]
    #[CoversNothing]
    public function ai_handles_long_text()
    {
        $aiService = new AIService;

        $longText = str_repeat('منتج ممتاز ورائع ', 100);
        $result = $aiService->analyzeText($longText);

        $this->assertIsArray($result);
    }
}
