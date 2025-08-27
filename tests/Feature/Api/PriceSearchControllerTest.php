<?php

namespace Tests\Feature\Api;

use App\Models\PriceOffer;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PriceSearchControllerTest extends TestCase
{
    use RefreshDatabase;

    // region Best Offer Tests

    /**
     * @dataProvider validationProvider
     */
    public function test_best_offer_fails_with_invalid_data(array $payload, string $expectedErrorField)
    {
        // ✅ *** تم تصحيح المسار هنا ***
        $response = $this->getJson('/api/v1/best-offer?'.http_build_query($payload ));

        $response->assertStatus(422)
            ->assertJsonValidationErrors($expectedErrorField);
    }

    public static function validationProvider(): array
    {
        return [
            'product is missing' => [['country' => 'US'], 'product'],
            'product is too short' => [['product' => 'a', 'country' => 'US'], 'product'],
            'product is too long' => [['product' => str_repeat('a', 256), 'country' => 'US'], 'product'],
            'country is not 2 chars' => [['product' => 'Test', 'country' => 'USA'], 'country'],
        ];
    }

    public function test_best_offer_returns_404_when_product_exists_but_has_no_offers_in_country()
    {
        $product = Product::factory()->create(['name' => 'Test Product']);
        $foreignStore = Store::factory()->create(['country_code' => 'CA']);
        PriceOffer::factory()->create([
            'product_id' => $product->id,
            'store_id' => $foreignStore->id,
            'price' => 99.99,
        ]);

        // ✅ *** تم تصحيح المسار هنا ***
        $response = $this->getJson('/api/v1/best-offer?product=Test Product&country=US');

        $response->assertStatus(404)
            ->assertJson(['message' => 'No offers found for this product in the specified country.']);
    }

    public function test_best_offer_returns_the_cheapest_offer_successfully()
    {
        $product = Product::factory()->create(['name' => 'Test Product']);
        $store = Store::factory()->create(['country_code' => 'US']);

        PriceOffer::factory()->create([
            'product_id' => $product->id,
            'store_id' => $store->id,
            'price' => 120.00,
        ]);
        $bestOffer = PriceOffer::factory()->create([
            'product_id' => $product->id,
            'store_id' => $store->id,
            'price' => 99.99,
        ]);

        // ✅ *** تم تصحيح المسار هنا ***
        $response = $this->getJson('/api/v1/best-offer?product=Test Product&country=US');

        $response->assertStatus(200)
            ->assertJsonFragment(['price' => '99.99'])
            ->assertJsonPath('id', $bestOffer->id);
    }

    public function test_best_offer_returns_correct_status_on_database_error()
    {
        $this->app->instance('db', \Mockery::mock(\Illuminate\Database\DatabaseManager::class, function ($mock) {
            $mock->shouldReceive('connection')->andThrow(new \Exception('Database connection failed'));
        }));

        // ✅ *** تم تصحيح المسار هنا ***
        $response = $this->getJson('/api/v1/best-offer?product=Test Product&country=US');

        $response->assertStatus(404);
    }

    // endregion

    // ... (باقي الكود يبقى كما هو) ...
}
