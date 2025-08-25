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
            'rating' => 5,
            'review_text' => 'Great product!'
        ];

        $response = $this->actingAs($this->user)->post(route('reviews.store'), $reviewData);

        $response->assertRedirect();
        $this->assertDatabaseHas('reviews', [
            'user_id' => $this->user->id,
            'product_id' => $product->id,
            'rating' => 5
        ]);
    }

    /** @test */
    public function store_validates_required_fields()
    {
        $response = $this->actingAs($this->user)->post(route('reviews.store'), []);
        $response->assertSessionHasErrors(['product_id', 'rating']);
    }

    /** @test */
    public function destroy_deletes_user_review()
    {
        $review = Review::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->delete(route('reviews.destroy', $review));
        
        $response->assertRedirect();
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }
}
