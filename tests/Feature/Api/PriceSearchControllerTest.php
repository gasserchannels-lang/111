<?php

namespace Tests\Feature\Api;

use App\Models\PriceOffer;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PriceSearchControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_best_offer_returns_422_for_missing_product()
    {
        $response = $this->getJson('/api/v1/best-offer?country=US');
        $response->assertStatus(422);
    }

    public function test_best_offer_returns_404_for_product_not_found()
    {
        $response = $this->getJson('/api/v1/best-offer?product=Unknown Product&country=US');
        $response->assertStatus(404);
    }

    public function test_best_offer_returns_best_offer_successfully()
    {
        $product = Product::factory()->create(['name' => 'Test Product']);
        $store = Store::factory()->create(['country_code' => 'US']);
        PriceOffer::factory()->create([
            'product_id' => $product->id,
            'store_id' => $store->id,
            'price' => 99.99,
        ]);

        $response = $this->getJson('/api/v1/best-offer?product=Test Product&country=US');

        $response->assertStatus(200)
            ->assertJsonFragment(['price' => '99.99']);
    }

    public function test_supported_stores_returns_stores_for_a_given_country()
    {
        Store::factory()->count(3)->create(['country_code' => 'US']);
        Store::factory()->count(2)->create(['country_code' => 'CA']);

        $response = $this->getJson('/api/v1/supported-stores?country=US');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }
}
