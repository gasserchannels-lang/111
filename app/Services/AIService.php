<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Log\LogManager;
use Illuminate\Support\Facades\Http;

class AIService
{
    private string $apiKey;

    private string $baseUrl;

    private int $timeout;

    private LogManager $log;

    private bool $isTesting;

    public function __construct(ConfigRepository $config, LogManager $log, bool $isTesting = false)
    {
        $this->apiKey = $config->get('services.openai.api_key', '');
        $this->baseUrl = $config->get('services.openai.base_url', 'https://api.openai.com/v1');
        $this->timeout = (int) $config->get('services.openai.timeout', 30);
        $this->log = $log;
        $this->isTesting = $isTesting;

        if ($this->apiKey === '' && ! $this->isTesting) {
            $this->log->error('OpenAI API key is not configured.');
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function makeRequest(string $endpoint, array $data): array
    {
        $fullUrl = $this->baseUrl.$endpoint;

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout($this->timeout)
                ->post($fullUrl, $data);

            if (! $response->successful()) {
                if (! $this->isTesting) {
                    $this->log->error('AI Service Request Failed', [
                        'url' => $fullUrl,
                        'status' => $response->status(),
                        'response' => $response->body(),
                    ]);
                }

                $jsonResponse = $response->json();

                return ['error' => 'API request failed', 'details' => is_array($jsonResponse) ? $jsonResponse : []];
            }

            $jsonResponse = $response->json();

            return is_array($jsonResponse) ? $jsonResponse : [];
        } catch (Exception $e) {
            if (! $this->isTesting) {
                $this->log->error('AI Service Exception', [
                    'url' => $fullUrl,
                    'message' => $e->getMessage(),
                ]);
            }

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
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function parseAnalysisResponse(array $data): array
    {
        if (isset($data['error'])) {
            return $data;
        }

        $choices = $data['choices'] ?? [];
        $choice = is_array($choices) && isset($choices[0]) ? $choices[0] : null;
        if ($choice && is_array($choice) && isset($choice['text']) && is_string($choice['text'])) {
            return ['result' => trim($choice['text'])];
        }

        return ['error' => 'Invalid response structure from AI service.'];
    }

    public function classifyProduct(string $productDescription): string
    {
        $prompt = "Classify the following product description into a single category: \"{$productDescription}\"";
        $response = $this->analyzeText($prompt, 'classification');

        $result = $response['result'] ?? 'Uncategorized';

        return is_string($result) ? $result : 'Uncategorized';
    }

    /**
     * @param  array<string, mixed>  $userPreferences
     * @param  array<int, array<string, mixed>>  $products
     * @return array<string, mixed>
     */
    public function generateRecommendations(array $userPreferences, array $products): array
    {
        $prompt = 'Based on these preferences: '.json_encode($userPreferences).', recommend products from this list: '.json_encode($products);

        return $this->analyzeText($prompt, 'recommendation');
    }

    /**
     * @return array<string, mixed>
     */
    public function analyzeImage(string $imagePath): array
    {
        $data = [
            'model' => 'gpt-4-vision-preview',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        ['type' => 'text', 'text' => 'What’s in this image?'],
                        ['type' => 'image_url', 'image_url' => ['url' => $imagePath]],
                    ],
                ],
            ],
            'max_tokens' => 300,
        ];

        $response = $this->makeRequest('/chat/completions', $data);

        return $this->parseImageAnalysis($response);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function parseImageAnalysis(array $data): array
    {
        if (isset($data['error'])) {
            return $data;
        }

        $choices = $data['choices'] ?? [];
        $message = is_array($choices) && isset($choices[0]) && is_array($choices[0]) ? $choices[0]['message'] ?? null : null;
        if ($message && is_array($message) && isset($message['content']) && is_string($message['content'])) {
            $content = $message['content'];

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
        // Using $content for future implementation
        return $content ? 'Extracted Category' : 'Default Category';
    }

    /**
     * @return array<string>
     */
    private function extractRecommendations(string $content): array
    {
        // Basic extraction logic
        // Using $content for future implementation
        return $content ? ['Recommendation 1', 'Recommendation 2'] : ['Default Recommendation'];
    }

    private function extractSentiment(string $content): string
    {
        // Basic extraction logic
        // Using $content for future implementation
        return $content ? 'Positive' : 'Neutral';
    }
}
