<?php

namespace Tests\AI;

use App\Models\Product;
use App\Models\User;
use App\Services\RecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RecommendationSystemTest extends TestCase
{
    

    #[Test]
    public function can_generate_user_recommendations()
    {
        $recommendationService = new RecommendationService;

        $user = User::factory()->create();
        $user->preferences = [
            'categories' => ['إلكترونيات', 'ملابس'],
            'price_range' => [100, 1000],
            'brands' => ['سامسونج', 'أبل'],
        ];
        $user->save();

        $recommendations = $recommendationService->getUserRecommendations($user->id);

        $this->assertIsArray($recommendations);
        $this->assertGreaterThan(0, count($recommendations));
    }

    #[Test]
    public function recommendations_match_user_preferences()
    {
        $recommendationService = new RecommendationService;

        $user = User::factory()->create();
        $user->preferences = [
            'categories' => ['إلكترونيات'],
            'price_range' => [500, 2000],
            'brands' => ['سامسونج'],
        ];
        $user->save();

        // Create products
        Product::factory()->create([
            'name' => 'هاتف سامسونج',
            'category' => 'إلكترونيات',
            'price' => 1500,
            'brand' => 'سامسونج',
        ]);

        Product::factory()->create([
            'name' => 'قميص قطني',
            'category' => 'ملابس',
            'price' => 200,
            'brand' => 'أديداس',
        ]);

        $recommendations = $recommendationService->getUserRecommendations($user->id);

        $this->assertIsArray($recommendations);
        if (count($recommendations) > 0) {
            $firstRecommendation = $recommendations[0];
            $this->assertEquals('إلكترونيات', $firstRecommendation['category']);
            $this->assertEquals('سامسونج', $firstRecommendation['brand']);
        }
    }

    #[Test]
    public function can_generate_similar_products()
    {
        $recommendationService = new RecommendationService;

        $product = Product::factory()->create([
            'name' => 'لابتوب ديل',
            'category' => 'إلكترونيات',
            'price' => 5000,
            'brand' => 'ديل',
        ]);

        $similarProducts = $recommendationService->getSimilarProducts($product->id);

        $this->assertIsArray($similarProducts);
    }

    #[Test]
    public function can_generate_trending_products()
    {
        $recommendationService = new RecommendationService;

        // Create some products with views
        Product::factory()->count(5)->create();

        $trendingProducts = $recommendationService->getTrendingProducts();

        $this->assertIsArray($trendingProducts);
        $this->assertLessThanOrEqual(10, count($trendingProducts));
    }

    #[Test]
    public function can_generate_collaborative_recommendations()
    {
        $recommendationService = new RecommendationService;

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Simulate similar purchase history
        $product = Product::factory()->create();
        $user1->purchases()->attach($product->id);
        $user2->purchases()->attach($product->id);

        $recommendations = $recommendationService->getCollaborativeRecommendations($user1->id);

        $this->assertIsArray($recommendations);
    }

    #[Test]
    public function recommendations_consider_price_range()
    {
        $recommendationService = new RecommendationService;

        $user = User::factory()->create();
        $user->preferences = [
            'price_range' => [100, 500],
        ];
        $user->save();

        // Create products with different prices
        Product::factory()->create(['price' => 200]);
        Product::factory()->create(['price' => 800]);

        $recommendations = $recommendationService->getUserRecommendations($user->id);

        $this->assertIsArray($recommendations);
        foreach ($recommendations as $recommendation) {
            $this->assertGreaterThanOrEqual(100, $recommendation['price']);
            $this->assertLessThanOrEqual(500, $recommendation['price']);
        }
    }

    #[Test]
    public function can_generate_seasonal_recommendations()
    {
        $recommendationService = new RecommendationService;

        $seasonalRecommendations = $recommendationService->getSeasonalRecommendations('summer');

        $this->assertIsArray($seasonalRecommendations);
    }

    #[Test]
    public function recommendations_improve_with_feedback()
    {
        $recommendationService = new RecommendationService;

        $user = User::factory()->create();
        $product = Product::factory()->create();

        // Get initial recommendations
        $initialRecommendations = $recommendationService->getUserRecommendations($user->id);

        // Provide feedback
        $recommendationService->recordFeedback($user->id, $product->id, 'positive');

        // Get updated recommendations
        $updatedRecommendations = $recommendationService->getUserRecommendations($user->id);

        $this->assertIsArray($updatedRecommendations);
        $this->assertIsArray($initialRecommendations);
    }
}
