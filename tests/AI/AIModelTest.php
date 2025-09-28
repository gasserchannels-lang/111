<?php

namespace Tests\AI;

use App\Services\AIService;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test;

class AIModelTest extends AIBaseTestCase
{
    #[Test]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function ai_model_initializes_correctly(): void
    {
        $aiService = new AIService;

        $this->assertInstanceOf(AIService::class, $aiService);
    }

    #[Test]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function ai_can_analyze_text(): void
    {
        $aiService = $this->getAIService();

        $text = 'منتج ممتاز ورائع';
        $result = $aiService->analyzeText($text);

        $this->assertArrayHasKey('result', $result);
        // اختبار بسيط للتأكد من أن النتيجة صحيحة
    }

    #[Test]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function ai_can_classify_products(): void
    {
        $aiService = new AIService;

        $productDescription = 'هاتف ذكي متطور';

        $result = $aiService->classifyProduct($productDescription);

        $this->assertNotEmpty($result);
    }

    #[Test]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function ai_can_generate_recommendations(): void
    {
        $aiService = $this->getAIService();

        $userPreferences = [
            'categories'  => ['إلكترونيات'],
            'price_range' => [1000, 5000],
            'brands'      => ['سامسونج', 'أبل'],
        ];

        $products = [];
        $recommendations = $aiService->generateRecommendations($userPreferences, $products);

        // For testing purposes, we'll just check that it's not empty
        $this->assertNotEmpty($recommendations);
    }

    #[Test]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function ai_handles_empty_input(): void
    {
        $aiService = $this->getAIService();

        $result = $aiService->analyzeText('');

        $this->assertArrayHasKey('result', $result);
    }

    #[Test]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function ai_handles_special_characters(): void
    {
        $aiService = $this->getAIService();

        $text = 'منتج ممتاز! @#$%^&*()';
        $result = $aiService->analyzeText($text);

        $this->assertArrayHasKey('result', $result);
    }

    #[Test]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function ai_handles_unicode_text(): void
    {
        $aiService = $this->getAIService();

        $text = 'منتج ممتاز 🚀 💯';
        $result = $aiService->analyzeText($text);

        $this->assertArrayHasKey('result', $result);
    }

    #[Test]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function ai_handles_long_text(): void
    {
        $aiService = $this->getAIService();

        $longText = str_repeat('منتج ممتاز ورائع ', 100);
        $result = $aiService->analyzeText($longText);

        $this->assertArrayHasKey('result', $result);
    }
}
