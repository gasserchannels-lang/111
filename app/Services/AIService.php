<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    protected string $apiKey;

    protected string $baseUrl;

    protected int $timeout;

    public function __construct()
    {
        $this->apiKey = config('ai.api_key') ?? '';
        $this->baseUrl = config('ai.base_url') ?? 'https://api.openai.com/v1';
        $this->timeout = config('ai.timeout') ?? 30;
    }

    /**
     * تحليل النص باستخدام الذكاء الاصطناعي
     * @return array<string, mixed>
     */
    public function analyzeText(string $text, string $type = 'general'): array
    {
        try {
            $cacheKey = 'ai_analysis_' . md5($text . $type);

            return Cache::remember($cacheKey, 3600, function () use ($text, $type): array {
                $response = Http::timeout($this->timeout)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $this->apiKey,
                        'Content-Type' => 'application/json',
                    ])
                    ->post($this->baseUrl . '/chat/completions', [
                        'model' => 'gpt-3.5-turbo',
                        'messages' => [
                            [
                                'role' => 'system',
                                'content' => $this->getSystemPrompt($type),
                            ],
                            [
                                'role' => 'user',
                                'content' => $text,
                            ],
                        ],
                        'max_tokens' => 1000,
                        'temperature' => 0.7,
                    ]);

                if ($response->successful()) {
                    $data = $response->json();

                    return $this->parseAnalysisResponse($data, $type);
                }

                throw new \Exception('AI API request failed: ' . $response->body());
            });
        } catch (\Exception $e) {
            Log::error('AI Analysis Error: ' . $e->getMessage());

            return $this->getDefaultAnalysis($type);
        }
    }

    /**
     * تصنيف المنتج باستخدام الذكاء الاصطناعي
     * @param array<string, mixed> $productData
     */
    public function classifyProduct(array $productData): string
    {
        try {
            $text = $productData['name'] . ' ' . ($productData['description'] ?? '');

            $response = $this->analyzeText($text, 'product_classification');

            return $response['category'] ?? 'غير محدد';
        } catch (\Exception $e) {
            Log::error('Product Classification Error: ' . $e->getMessage());

            return 'غير محدد';
        }
    }

    /**
     * توليد توصيات المنتجات
     * @param array<string, mixed> $userPreferences
     * @param array<string, mixed> $products
     * @return array<string, mixed>
     */
    public function generateRecommendations(array $userPreferences, array $products): array
    {
        try {
            $prompt = $this->buildRecommendationPrompt($userPreferences, $products);

            $response = $this->analyzeText($prompt, 'recommendations');

            return $response['recommendations'] ?? [];
        } catch (\Exception $e) {
            Log::error('Recommendation Generation Error: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * تحليل صورة المنتج
     * @return array<string, mixed>
     */
    public function analyzeImage(string $imageUrl): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->baseUrl . '/chat/completions', [
                    'model' => 'gpt-4-vision-preview',
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => [
                                [
                                    'type' => 'text',
                                    'text' => 'حلل هذه الصورة وحدد نوع المنتج والخصائص الرئيسية',
                                ],
                                [
                                    'type' => 'image_url',
                                    'image_url' => [
                                        'url' => $imageUrl,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'max_tokens' => 500,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                return $this->parseImageAnalysis($data);
            }

            throw new \Exception('Image analysis failed');
        } catch (\Exception $e) {
            Log::error('Image Analysis Error: ' . $e->getMessage());

            return ['error' => 'فشل في تحليل الصورة'];
        }
    }

    /**
     * الحصول على رسالة النظام حسب النوع
     */
    protected function getSystemPrompt(string $type): string
    {
        $prompts = [
            'general' => 'أنت مساعد ذكي لتحليل النصوص. حلل النص المعطى وأعط تحليلاً شاملاً.',
            'product_analysis' => 'أنت خبير في تحليل المنتجات. حلل المنتج المعطى وحدد خصائصه ومميزاته.',
            'product_classification' => 'أنت خبير في تصنيف المنتجات. صنف المنتج المعطى إلى الفئة المناسبة.',
            'recommendations' => 'أنت خبير في التوصيات. اقترح منتجات مناسبة بناءً على التفضيلات المعطاة.',
            'sentiment' => 'أنت خبير في تحليل المشاعر. حلل المشاعر في النص المعطى.',
        ];

        return $prompts[$type] ?? $prompts['general'];
    }

    /**
     * تحليل استجابة الذكاء الاصطناعي
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function parseAnalysisResponse(array $data, string $type): array
    {
        $content = $data['choices'][0]['message']['content'] ?? '';

        return match ($type) {
            'product_classification' => [
                'category' => $this->extractCategory($content),
                'confidence' => 0.8,
            ],
            'recommendations' => [
                'recommendations' => $this->extractRecommendations($content),
            ],
            'sentiment' => [
                'sentiment' => $this->extractSentiment($content),
                'confidence' => 0.7,
            ],
            default => [
                'analysis' => $content,
                'confidence' => 0.8,
            ],
        };
    }

    /**
     * استخراج الفئة من النص
     */
    protected function extractCategory(string $content): string
    {
        $categories = [
            'إلكترونيات',
            'ملابس',
            'أثاث',
            'كتب',
            'ألعاب',
            'رياضة',
            'جمال',
            'صحة',
            'طعام',
            'سيارات',
            'منزل',
            'أخرى',
        ];

        foreach ($categories as $category) {
            if (str_contains($content, $category)) {
                return $category;
            }
        }

        return 'أخرى';
    }

    /**
     * استخراج التوصيات من النص
     * @return array<string, mixed>
     */
    protected function extractRecommendations(string $content): array
    {
        // تحليل بسيط لاستخراج التوصيات
        $lines = explode("\n", $content);
        $recommendations = [];

        foreach ($lines as $line) {
            if (preg_match('/\d+\.\s*(.+)/', $line, $matches)) {
                $recommendations[] = trim($matches[1]);
            }
        }

        return array_slice($recommendations, 0, 5); // أول 5 توصيات
    }

    /**
     * استخراج المشاعر من النص
     */
    protected function extractSentiment(string $content): string
    {
        $positiveWords = ['رائع', 'ممتاز', 'جيد', 'مفيد', 'مثالي'];
        $negativeWords = ['سيء', 'رديء', 'غير مفيد', 'مشكلة'];

        $positiveCount = 0;
        $negativeCount = 0;

        foreach ($positiveWords as $word) {
            $positiveCount += substr_count($content, $word);
        }

        foreach ($negativeWords as $word) {
            $negativeCount += substr_count($content, $word);
        }
        if ($positiveCount > $negativeCount) {
            return 'إيجابي';
        }

        if ($negativeCount > $positiveCount) {
            return 'سلبي';
        } else {
            return 'محايد';
        }
    }

    /**
     * تحليل صورة المنتج
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function parseImageAnalysis(array $data): array
    {
        $content = $data['choices'][0]['message']['content'] ?? '';

        return [
            'description' => $content,
            'category' => $this->extractCategory($content),
            'confidence' => 0.8,
        ];
    }

    /**
     * بناء رسالة التوصيات
     * @param array<string, mixed> $preferences
     * @param array<string, mixed> $products
     */
    protected function buildRecommendationPrompt(array $preferences, array $products): string
    {
        $preferencesText = implode(', ', $preferences);
        $productsText = implode(', ', array_column($products, 'name'));

        return "بناءً على التفضيلات التالية: {$preferencesText}، والمنتجات المتاحة: {$productsText}، اقترح أفضل 5 منتجات مناسبة.";
    }

    /**
     * الحصول على تحليل افتراضي في حالة الخطأ
     * @return array<string, mixed>
     */
    protected function getDefaultAnalysis(string $type): array
    {
        $defaults = [
            'general' => ['analysis' => 'غير متاح', 'confidence' => 0.0],
            'product_analysis' => ['analysis' => 'تحليل غير متاح', 'confidence' => 0.0],
            'product_classification' => ['category' => 'غير محدد', 'confidence' => 0.0],
            'recommendations' => ['recommendations' => []],
            'sentiment' => ['sentiment' => 'محايد', 'confidence' => 0.0],
        ];

        return $defaults[$type] ?? $defaults['general'];
    }
}
