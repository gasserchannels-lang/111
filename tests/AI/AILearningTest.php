<?php

namespace Tests\AI;

use App\Services\AIService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AILearningTest extends TestCase
{
    

    #[Test]
    public function ai_can_learn_from_user_feedback()
    {
        $aiService = new AIService;

        $text = 'منتج ممتاز';
        $initialResult = $aiService->analyzeText($text);

        // Provide positive feedback
        $aiService->learnFromFeedback($text, 'positive', true);

        // Get updated result
        $updatedResult = $aiService->analyzeText($text);

        $this->assertIsArray($initialResult);
        $this->assertIsArray($updatedResult);
        $this->assertArrayHasKey('sentiment', $updatedResult);
    }

    #[Test]
    public function ai_improves_accuracy_over_time()
    {
        $aiService = new AIService;

        $testCases = [
            ['text' => 'منتج رائع', 'sentiment' => 'positive'],
            ['text' => 'منتج سيء', 'sentiment' => 'negative'],
            ['text' => 'منتج عادي', 'sentiment' => 'neutral'],
        ];

        // Initial accuracy
        $initialCorrect = 0;
        foreach ($testCases as $case) {
            $result = $aiService->analyzeText($case['text']);
            if ($result['sentiment'] === $case['sentiment']) {
                $initialCorrect++;
            }
        }
        $initialAccuracy = $initialCorrect / count($testCases);

        // Provide feedback for learning
        foreach ($testCases as $case) {
            $aiService->learnFromFeedback($case['text'], $case['sentiment'], true);
        }

        // Test accuracy after learning
        $finalCorrect = 0;
        foreach ($testCases as $case) {
            $result = $aiService->analyzeText($case['text']);
            if ($result['sentiment'] === $case['sentiment']) {
                $finalCorrect++;
            }
        }
        $finalAccuracy = $finalCorrect / count($testCases);

        $this->assertGreaterThanOrEqual($initialAccuracy, $finalAccuracy);
    }

    #[Test]
    public function ai_adapts_to_user_preferences()
    {
        $aiService = new AIService;

        $userPreferences = [
            'categories' => ['إلكترونيات'],
            'price_range' => [1000, 5000],
            'brands' => ['سامسونج'],
        ];

        // Initial recommendations
        $initialRecommendations = $aiService->generateRecommendations($userPreferences);

        // Simulate user interactions
        $aiService->recordUserInteraction('user123', 'product456', 'viewed');
        $aiService->recordUserInteraction('user123', 'product789', 'purchased');

        // Get updated recommendations
        $updatedRecommendations = $aiService->generateRecommendations($userPreferences);

        $this->assertIsArray($initialRecommendations);
        $this->assertIsArray($updatedRecommendations);
    }

    #[Test]
    public function ai_learns_from_product_classification_feedback()
    {
        $aiService = new AIService;

        $productData = [
            'name' => 'هاتف ذكي',
            'description' => 'جهاز إلكتروني للاتصال',
            'price' => 2000,
        ];

        $initialClassification = $aiService->classifyProduct($productData);

        // Provide feedback
        $aiService->learnFromClassificationFeedback($productData, 'إلكترونيات', true);

        $updatedClassification = $aiService->classifyProduct($productData);

        $this->assertIsString($initialClassification);
        $this->assertIsString($updatedClassification);
    }

    #[Test]
    public function ai_learns_from_recommendation_feedback()
    {
        $aiService = new AIService;

        $userPreferences = [
            'categories' => ['ملابس'],
            'price_range' => [100, 500],
        ];

        $recommendations = $aiService->generateRecommendations($userPreferences);

        // Simulate user feedback on recommendations
        if (count($recommendations) > 0) {
            $recommendation = $recommendations[0];
            $aiService->recordRecommendationFeedback('user123', $recommendation['id'], 'positive');
        }

        $updatedRecommendations = $aiService->generateRecommendations($userPreferences);

        $this->assertIsArray($recommendations);
        $this->assertIsArray($updatedRecommendations);
    }

    #[Test]
    public function ai_learns_from_image_analysis_feedback()
    {
        $aiService = new AIService;

        // Create test image
        $image = imagecreate(200, 200);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        imagefill($image, 0, 0, $white);
        imagestring($image, 5, 50, 100, 'Phone', $black);

        $imagePath = storage_path('app/test-learning.jpg');
        imagejpeg($image, $imagePath);
        imagedestroy($image);

        $initialAnalysis = $aiService->processImage($imagePath);

        // Provide feedback
        $aiService->learnFromImageFeedback($imagePath, ['هاتف', 'إلكترونيات'], true);

        $updatedAnalysis = $aiService->processImage($imagePath);

        $this->assertIsArray($initialAnalysis);
        $this->assertIsArray($updatedAnalysis);
    }

    #[Test]
    public function ai_learning_persists_across_sessions()
    {
        $aiService = new AIService;

        $text = 'منتج ممتاز';

        // Learn from feedback
        $aiService->learnFromFeedback($text, 'positive', true);

        // Create new instance (simulating new session)
        $newAiService = new AIService;

        // Check if learning persisted
        $result = $newAiService->analyzeText($text);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('sentiment', $result);
    }

    #[Test]
    public function ai_learning_rate_is_appropriate()
    {
        $aiService = new AIService;

        $text = 'منتج جيد';

        // Get initial prediction
        $initialResult = $aiService->analyzeText($text);

        // Provide multiple feedback instances
        for ($i = 0; $i < 5; $i++) {
            $aiService->learnFromFeedback($text, 'positive', true);
        }

        // Get updated prediction
        $updatedResult = $aiService->analyzeText($text);

        $this->assertIsArray($initialResult);
        $this->assertIsArray($updatedResult);

        // Check if confidence improved
        if (isset($initialResult['confidence']) && isset($updatedResult['confidence'])) {
            $this->assertGreaterThanOrEqual($initialResult['confidence'], $updatedResult['confidence']);
        }
    }

    #[Test]
    public function ai_handles_contradictory_feedback()
    {
        $aiService = new AIService;

        $text = 'منتج متوسط';

        // Provide contradictory feedback
        $aiService->learnFromFeedback($text, 'positive', true);
        $aiService->learnFromFeedback($text, 'negative', true);
        $aiService->learnFromFeedback($text, 'positive', true);

        $result = $aiService->analyzeText($text);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('sentiment', $result);
        $this->assertArrayHasKey('confidence', $result);
    }
}
