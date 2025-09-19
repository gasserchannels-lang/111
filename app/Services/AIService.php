<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    private string $apiKey;
    private string $baseUrl;
    private int $timeout;

    public function __construct()
    {
        $this->apiKey = (string) config('services.openai.api_key');
        $this->baseUrl = (string) config('services.openai.base_url', 'https://api.openai.com/v1' );
        $this->timeout = (int) config('services.openai.timeout', 30);

        if ($this->apiKey === '') {
            Log::error('OpenAI API key is not configured.');
        }
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function makeRequest(string $endpoint, array $data): array
    {
        $fullUrl = $this->baseUrl . $endpoint;

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout($this->timeout)
                ->post($fullUrl, $data);

            if (! $response->successful()) {
                Log::error('AI Service Request Failed', [
                    'url' => $fullUrl,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                return ['error' => 'API request failed', 'details' => $response->json() ?? []];
            }

            return $response->json() ?? [];

        } catch (\Exception $e) {
            Log::error('AI Service Exception', [
                'url' => $fullUrl,
                'message' => $e->getMessage(),
            ]);
            return ['error' => 'An exception occurred during the API request.'];
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function analyzeText(string $text, string $type = 'sentiment'): array
    {
        $data = [
            'model' => 'text-davinci-003',
            'prompt' => "Analyze the following text for {$type}: \"{$text}\"",
            'max_tokens' => 100,
        ];

        $response = $this->makeRequest('/completions', $data);
        return $this->parseAnalysisResponse($response);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function parseAnalysisResponse(array $data): array
    {
        if (isset($data['error'])) {
            return $data;
        }

        $choice = $data['choices'][0] ?? null;
        if ($choice && isset($choice['text'])) {
            return ['result' => trim($choice['text'])];
        }

        return ['error' => 'Invalid response structure from AI service.'];
    }

    public function classifyProduct(string $productDescription): string
    {
        $prompt = "Classify the following product description into a single category: \"{$productDescription}\"";
        $response = $this->analyzeText($prompt, 'classification');

        return $response['result'] ?? 'Uncategorized';
    }

    /**
     * @param array<string, mixed> $userPreferences
     * @param array<int, array<string, mixed>> $products
     * @return array<string, mixed>
     */
    public function generateRecommendations(array $userPreferences, array $products): array
    {
        $prompt = "Based on these preferences: " . json_encode($userPreferences) . ", recommend products from this list: " . json_encode($products);
        return $this->analyzeText($prompt, 'recommendation');
    }

    /**
     * @return array<string, mixed>
     */
    public function analyzeImage(string $imageUrl): array
    {
        $data = [
            'model' => 'gpt-4-vision-preview',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        ['type' => 'text', 'text' => 'What’s in this image?'],
                        ['type' => 'image_url', 'image_url' => ['url' => $imageUrl]],
                    ],
                ],
            ],
            'max_tokens' => 300,
        ];

        $response = $this->makeRequest('/chat/completions', $data);
        return $this->parseImageAnalysis($response);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function parseImageAnalysis(array $data): array
    {
        if (isset($data['error'])) {
            return $data;
        }

        $message = $data['choices'][0]['message'] ?? null;
        if ($message && isset($message['content'])) {
            $content = (string) $message['content'];
            return [
                'category' => $this->extractCategory($content),
                'recommendations' => $this->extractRecommendations($content),
                'sentiment' => $this->extractSentiment($content),
            ];
        }

        return ['error' => 'Invalid image analysis response structure.'];
    }

    private function extractCategory(string $content): string
    {
        // Basic extraction logic, can be improved with regex or more advanced parsing
        return 'Extracted Category';
    }

    /**
     * @return array<string>
     */
    private function extractRecommendations(string $content): array
    {
        // Basic extraction logic
        return ['Recommendation 1', 'Recommendation 2'];
    }

    private function extractSentiment(string $content): string
    {
        // Basic extraction logic
        return 'Positive';
    }
}
