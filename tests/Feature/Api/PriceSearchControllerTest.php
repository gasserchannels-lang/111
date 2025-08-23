<?php

namespace Tests\Feature\Api;

use App\Models\Currency;
use App\Models\Language;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PriceSearchControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create necessary base data for tests
        Language::factory()->create(['code' => 'us', 'is_default' => true]);
        Currency::factory()->create(['code' => 'USD', 'is_default' => true]);
    }

    public function test_best_offer_returns_422_for_invalid_validation()
    {
        $response = $this->getJson('/api/v1/best-offer');
        $response->assertStatus(422)->assertJsonValidationErrors('product');
    }

    public function test_best_offer_returns_404_when_no_offers_found()
    {
        $response = $this->getJson('/api/v1/best-offer?product=nonexistent');
        $response->assertStatus(404);
    }

    public function test_best_offer_returns_best_offer_successfully()
    {
        $product = Product::factory()->create(['name' => 'Test iPhone']);
        $store = Store::factory()->create(['country_code' => 'US']);
        $store->priceOffers()->create([
            'product_id' => $product->id,
            'price' => 999.99,
            'currency' => 'USD',
            'product_url' => 'http://example.com',
        ] );

        $response = $this->getJson('/api/v1/best-offer?product=iPhone&country=US');

        $response->assertStatus(200)
            ->assertJsonFragment(['price' => '999.99']);
    }

    public function test_supported_stores_returns_stores_for_a_given_country()
    {
        Store::factory()->create(['name' => 'Amazon US', 'country_code' => 'US']);
        Store::factory()->create(['name' => 'BestBuy US', 'country_code' => 'US']);
        Store::factory()->create(['name' => 'Amazon CA', 'country_code' => 'CA']);

        $response = $this->getJson('/api/v1/supported-stores?country=US');

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['name' => 'Amazon US'])
            ->assertJsonMissing(['name' => 'Amazon CA']);
    }
}
