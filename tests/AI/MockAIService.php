<?php

namespace Tests\AI;

use App\Services\AIService;

/**
 * Mock AI Service for testing purposes
 * يحاكي خدمة AI الحقيقية بدون الحاجة لـ API key.
 */
class MockAIService extends AIService
{
    public function analyzeText(string $text, string $type = 'sentiment'): array
    {
        // محاكاة تحليل النص
        $sentiment = $this->extractSentiment($text);

        return [
            'result'     => "Mock analysis for: {$text}",
            'sentiment'  => $sentiment,
            'confidence' => 0.85,
        ];
    }

    public function classifyProduct(string $productDescription): string
    {
        // محاكاة تصنيف المنتج
        $categories = ['إلكترونيات', 'ملابس', 'أدوات منزلية', 'كتب', 'رياضة'];

        return $categories[array_rand($categories)];
    }

    /**
     * @param array<string, mixed>             $userPreferences
     * @param array<int, array<string, mixed>> $products
     *
     * @return array<string, mixed>
     */
    public function generateRecommendations(array $userPreferences, array $products): array
    {
        // محاكاة توليد التوصيات
        return [
            'recommendations' => [
                'Recommendation 1',
                'Recommendation 2',
                'Recommendation 3',
            ],
            'confidence' => 0.85,
            'count'      => 3,
        ];
    }

    public function analyzeImage(string $imagePath): array
    {
        // محاكاة تحليل الصور
        return [
            'result'     => 'Mock image analysis result',
            'sentiment'  => 'positive',
            'confidence' => 0.80,
            'tags'       => ['منتج', 'جودة عالية'],
        ];
    }

    private function extractSentiment(string $text): string
    {
        $positiveWords = ['ممتاز', 'رائع', 'جيد', 'مفيد', 'مثالي', 'ممتازة', 'رائعة', 'جيدة', 'مفيدة', 'مثالية'];
        $negativeWords = ['سيء', 'رديء', 'مشكلة', 'خطأ', 'فاشل', 'سيئة', 'رديئة', 'مشاكل', 'أخطاء', 'فاشلة'];

        $text = strtolower($text);
        $positiveCount = 0;
        $negativeCount = 0;

        foreach ($positiveWords as $word) {
            if (str_contains($text, $word)) {
                $positiveCount++;
            }
        }

        foreach ($negativeWords as $word) {
            if (str_contains($text, $word)) {
                $negativeCount++;
            }
        }

        if ($positiveCount > $negativeCount) {
            return 'positive';
        } elseif ($negativeCount > $positiveCount) {
            return 'negative';
        } else {
            return 'neutral';
        }
    }
}
