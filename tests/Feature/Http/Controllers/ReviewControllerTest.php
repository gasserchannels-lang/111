<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\Review;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReviewControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function store_creates_product_review()
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

    /** @test */
    public function store_validates_required_fields()
    {
        $response = $this->actingAs($this->user)->post(route('reviews.store'), []);
        $response->assertSessionHasErrors(['product_id', 'title', 'content', 'rating']);
    }

    /** @test */
    public function store_validates_rating_range()
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

    /** @test */
    public function destroy_deletes_user_review()
    {
        $review = Review::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->delete(route('reviews.destroy', $review));

        $response->assertRedirect();
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    /** @test */
    public function user_cannot_delete_other_users_review()
    {
        $otherUser = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)->delete(route('reviews.destroy', $review));

        $response->assertForbidden();
        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
    }
}
