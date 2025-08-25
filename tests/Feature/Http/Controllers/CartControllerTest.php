<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function index_displays_cart()
    {
        $response = $this->actingAs($this->user)->get(route('cart.index'));
        $response->assertStatus(200);
    }

    /** @test */
    public function add_item_to_cart()
    {
        $product = Product::factory()->create();
        $response = $this->actingAs($this->user)->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 2
        ]);
        $response->assertRedirect();
    }

    /** @test */
    public function remove_item_from_cart()
    {
        // يفترض أن لديك منطق لإضافة عنصر ثم حذفه
        $product = Product::factory()->create();
        $this->actingAs($this->user)->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1]);

        $response = $this->actingAs($this->user)->delete(route('cart.remove', $product->id));
        $response->assertRedirect();
    }
}
