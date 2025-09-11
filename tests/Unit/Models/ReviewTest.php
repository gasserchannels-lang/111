<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_a_review()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $review = Review::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => 5,
            'title' => 'Great product!',
            'content' => 'This product exceeded my expectations.',
            'is_verified_purchase' => true,
        ]);

        $this->assertInstanceOf(Review::class, $review);
        $this->assertEquals($user->id, $review->user_id);
        $this->assertEquals($product->id, $review->product_id);
        $this->assertEquals(5, $review->rating);
        $this->assertEquals('Great product!', $review->title);
        $this->assertEquals('This product exceeded my expectations.', $review->content);
        $this->assertTrue($review->is_verified_purchase);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_user_relationship()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $review = Review::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => 5,
            'title' => 'Great product!',
            'content' => 'This product exceeded my expectations.',
            'is_verified_purchase' => true,
        ]);

        $this->assertInstanceOf(User::class, $review->user);
        $this->assertEquals($user->id, $review->user->id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_product_relationship()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $review = Review::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => 5,
            'title' => 'Great product!',
            'content' => 'This product exceeded my expectations.',
            'is_verified_purchase' => true,
        ]);

        $this->assertInstanceOf(Product::class, $review->product);
        $this->assertEquals($product->id, $review->product->id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_validate_required_fields()
    {
        $review = new Review;

        try {
            $review->save();
            $this->fail('Expected exception was not thrown');
        } catch (\Exception $e) {
            $this->assertStringContainsString('NOT NULL constraint failed', $e->getMessage());
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_review_with_factory()
    {
        $review = Review::factory()->create();

        $this->assertInstanceOf(Review::class, $review);
        $this->assertNotNull($review->user_id);
        $this->assertNotNull($review->product_id);
        $this->assertNotNull($review->rating);
        $this->assertNotNull($review->title);
        $this->assertNotNull($review->content);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_set_rating()
    {
        $review = Review::factory()->create(['rating' => 4]);

        $this->assertEquals(4, $review->rating);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_set_verified_purchase()
    {
        $review = Review::factory()->create(['is_verified_purchase' => true]);

        $this->assertTrue($review->is_verified_purchase);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_set_helpful_votes()
    {
        $review = Review::factory()->create([
            'helpful_votes' => ['user1', 'user2'],
            'helpful_count' => 2,
        ]);

        $this->assertEquals(['user1', 'user2'], $review->helpful_votes);
        $this->assertEquals(2, $review->helpful_count);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_get_review_text_attribute()
    {
        $review = Review::factory()->create([
            'content' => 'This is the review content',
        ]);

        $this->assertEquals('This is the review content', $review->getReviewTextAttribute());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_query_reviews_by_rating()
    {
        Review::factory()->create(['rating' => 5]);
        Review::factory()->create(['rating' => 3]);

        $fiveStarReviews = Review::where('rating', 5)->get();

        $this->assertCount(1, $fiveStarReviews);
        $this->assertEquals(5, $fiveStarReviews->first()->rating);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_query_reviews_by_product()
    {
        $product = Product::factory()->create();

        Review::factory()->create(['product_id' => $product->id]);
        Review::factory()->create(['product_id' => $product->id]);

        $productReviews = Review::where('product_id', $product->id)->get();

        $this->assertCount(2, $productReviews);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_query_verified_purchase_reviews()
    {
        Review::factory()->create(['is_verified_purchase' => true]);
        Review::factory()->create(['is_verified_purchase' => false]);

        $verifiedReviews = Review::where('is_verified_purchase', true)->get();

        $this->assertCount(1, $verifiedReviews);
        $this->assertTrue($verifiedReviews->first()->is_verified_purchase);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_calculate_average_rating()
    {
        $product = Product::factory()->create();

        Review::factory()->create([
            'product_id' => $product->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'product_id' => $product->id,
            'rating' => 3,
        ]);

        $averageRating = Review::where('product_id', $product->id)->avg('rating');

        $this->assertEquals(4.0, $averageRating);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_count_reviews_by_rating()
    {
        $product = Product::factory()->create();

        Review::factory()->create(['product_id' => $product->id, 'rating' => 5]);
        Review::factory()->create(['product_id' => $product->id, 'rating' => 5]);
        Review::factory()->create(['product_id' => $product->id, 'rating' => 3]);

        $fiveStarCount = Review::where('product_id', $product->id)
            ->where('rating', 5)
            ->count();

        $this->assertEquals(2, $fiveStarCount);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_get_recent_reviews()
    {
        Review::factory()->create(['created_at' => now()->subDays(5)]);
        Review::factory()->create(['created_at' => now()->subDays(1)]);

        $recentReviews = Review::where('created_at', '>', now()->subDays(2))->get();

        $this->assertCount(1, $recentReviews);
    }
}
