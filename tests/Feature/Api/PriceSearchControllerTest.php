<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\PriceOffer;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PriceSearchControllerTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('validationProvider')]
    public function test_best_offer_fails_with_invalid_data(array $payload, string $expectedErrorField): void
    {
        $response = $this->getJson('/api/v1/best-offer?'.http_build_query($payload));
        $response->assertStatus(422)->assertJsonValidationErrors($expectedErrorField);
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

    public function test_best_offer_returns_404_when_product_exists_but_has_no_offers_in_country(): void
    {
        $product = Product::factory()->create(['name' => 'Test Product']);
        $foreignStore = Store::factory()->create(['country_code' => 'CA']);
        PriceOffer::factory()->create(['product_id' => $product->id, 'store_id' => $foreignStore->id, 'price' => 99.99]);
        $response = $this->getJson('/api/v1/best-offer?product=Test Product&country=US');
        $response->assertStatus(404)->assertJson(['message' => 'No offers found for this product in the specified country.']);
    }

    public function test_best_offer_returns_the_cheapest_offer_successfully(): void
    {
        $product = Product::factory()->create(['name' => 'Test Product']);
        $store = Store::factory()->create(['country_code' => 'US']);
        PriceOffer::factory()->create(['product_id' => $product->id, 'store_id' => $store->id, 'price' => 120.00]);
        $bestOffer = PriceOffer::factory()->create(['product_id' => $product->id, 'store_id' => $store->id, 'price' => 99.99]);
        $response = $this->getJson('/api/v1/best-offer?product=Test Product&country=US');
        $response->assertStatus(200)->assertJsonPath('price', 99.99)->assertJsonPath('id', $bestOffer->id);
    }

    public function test_best_offer_returns_correct_status_on_database_error(): void
    {
        $response = $this->getJson('/api/v1/best-offer?product=Test Product&country=US&simulate_db_error=true');
        $response->assertStatus(500)->assertJson(['message' => 'An unexpected error occurred.']);
    }

    public function test_supported_stores_returns_stores_for_a_given_country(): void
    {
        Store::factory()->count(3)->create(['country_code' => 'US', 'is_active' => true]);
        Store::factory()->count(2)->create(['country_code' => 'CA', 'is_active' => true]);
        Store::factory()->create(['country_code' => 'US', 'is_active' => false]);
        $response = $this->getJson('/api/v1/supported-stores?country=US');
        $response->assertStatus(200)->assertJsonCount(3);
    }

    public function test_supported_stores_returns_empty_array_for_country_with_no_stores(): void
    {
        Store::factory()->count(2)->create(['country_code' => 'CA']);
        $response = $this->getJson('/api/v1/supported-stores?country=US');
        $response->assertStatus(200)->assertJsonCount(0)->assertJson([]);
    }

    public function test_it_uses_country_from_request_when_provided(): void
    {
        Store::factory()->count(2)->create(['country_code' => 'CA', 'is_active' => true]);
        $response = $this->getJson('/api/v1/supported-stores?country=CA');
        $response->assertStatus(200)->assertJsonCount(2);
    }

    public function test_it_detects_country_from_cloudflare_header(): void
    {
        Store::factory()->count(3)->create(['country_code' => 'FR', 'is_active' => true]);
        $response = $this->withHeaders(['CF-IPCountry' => 'FR'])->getJson('/api/v1/supported-stores');
        $response->assertStatus(200)->assertJsonCount(3);
    }

    public function test_it_detects_country_from_ip_api_successfully(): void
    {
        Http::fake(['ipapi.co/*' => Http::response('DE', 200)]);
        Store::factory()->count(4)->create(['country_code' => 'DE', 'is_active' => true]);
        $response = $this->getJson('/api/v1/supported-stores');
        $response->assertStatus(200)->assertJsonCount(4);
    }

    public function test_it_falls_back_to_us_when_ip_api_fails(): void
    {
        Http::fake(['ipapi.co/*' => Http::response(null, 500)]);
        Store::factory()->count(5)->create(['country_code' => 'US', 'is_active' => true]);
        $response = $this->getJson('/api/v1/supported-stores');
        $response->assertStatus(200)->assertJsonCount(5);
    }
}
