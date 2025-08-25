<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\Wishlist;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WishlistControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function index_displays_user_wishlist()
    {
        $response = $this->actingAs($this->user)->get(route('wishlist.index'));
        $response->assertStatus(200);
    }

    /** @test */
    public function store_adds_product_to_wishlist()
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->user)->post(route('wishlist.store'), [
            'product_id' => $product->id
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('wishlists', [
            'user_id' => $this->user->id,
            'product_id' => $product->id
        ]);
    }

    /** @test */
    public function destroy_removes_product_from_wishlist()
    {
        $wishlist = Wishlist::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->delete(route('wishlist.destroy', $wishlist));
        
        $response->assertRedirect();
        $this->assertDatabaseMissing('wishlists', ['id' => $wishlist->id]);
    }
}
