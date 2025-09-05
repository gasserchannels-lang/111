<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReviewControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    #[Test]
    public function store_creates_product_review(): void
    {
        $product = Product::factory()->create();

        $reviewData = [
            'product_id' => $product->id,
            'title' => 'Great Product Title',
            'content' => 'This is a great product with excellent quality!',
            'rating' => 5,
        ];

        $response = $this->actingAs($this->user)->post(route('reviews.store'), $reviewData);

        $response->assertRedirect();
        $this->assertDatabaseHas('reviews', [
            'user_id' => $this->user->id,
            'product_id' => $product->id,
            'title' => 'Great Product Title',
            'rating' => 5,
        ]);
    }

    #[Test]
    public function store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)->post(route('reviews.store'), []);
        $response->assertSessionHasErrors(['product_id', 'title', 'content', 'rating']);
    }

    #[Test]
    public function store_validates_rating_range(): void
    {
        $product = Product::factory()->create();

        $reviewData = [
            'product_id' => $product->id,
            'title' => 'Test Review',
            'content' => 'Test content',
            'rating' => 6, // Invalid rating
        ];

        $response = $this->actingAs($this->user)->post(route('reviews.store'), $reviewData);
        $response->assertSessionHasErrors(['rating']);
    }

    #[Test]
    public function destroy_deletes_user_review(): void
    {
        $review = Review::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->delete(route('reviews.destroy', $review));

        $response->assertRedirect();
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    #[Test]
    public function user_cannot_delete_other_users_review(): void
    {
        $otherUser = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)->delete(route('reviews.destroy', $review));

        $response->assertForbidden();
        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
    }
}
