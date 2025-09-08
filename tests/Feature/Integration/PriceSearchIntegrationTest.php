<?php

declare(strict_types=1);

namespace Tests\Feature\Integration;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Product;
use App\Models\PriceOffer;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PriceSearchIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function it_can_search_prices_with_full_workflow()
    {
        // Create test data
        $currency = Currency::factory()->create(['code' => 'USD', 'name' => 'US Dollar']);
        $store1 = Store::factory()->create(['name' => 'Store 1', 'currency_id' => $currency->id]);
        $store2 = Store::factory()->create(['name' => 'Store 2', 'currency_id' => $currency->id]);
        $brand = Brand::factory()->create(['name' => 'Apple']);
        $category = Category::factory()->create(['name' => 'Electronics']);
        
        $product = Product::factory()->create([
            'name' => 'iPhone 15',
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'store_id' => $store1->id,
            'is_active' => true,
        ]);

        // Create price offers
        $offer1 = PriceOffer::factory()->create([
            'product_id' => $product->id,
            'store_id' => $store1->id,
            'price' => 999.99,
            'is_available' => true,
        ]);

        $offer2 = PriceOffer::factory()->create([
            'product_id' => $product->id,
            'store_id' => $store2->id,
            'price' => 899.99,
            'is_available' => true,
        ]);

        // Test search functionality
        $response = $this->getJson('/api/price-search?q=iPhone 15');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'description',
                    'slug',
                    'brand',
                    'category',
                    'price_offers' => [
                        '*' => [
                            'id',
                            'price',
                            'url',
                            'store',
                            'is_available',
                        ]
                    ]
                ]
            ],
            'total',
            'query'
        ]);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        
        // Verify the product has price offers
        $product = $data[0];
        $this->assertCount(2, $product['price_offers']);
        
        // Verify prices are available
        $prices = array_column($product['price_offers'], 'price');
        $this->assertNotEmpty($prices);
        $this->assertCount(2, $prices);
    }

    /**
     * @test
     */
    public function it_can_handle_user_wishlist_integration()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $currency = Currency::factory()->create();
        $store = Store::factory()->create(['currency_id' => $currency->id]);
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();
        
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'store_id' => $store->id,
            'is_active' => true,
        ]);

        $priceOffer = PriceOffer::factory()->create([
            'product_id' => $product->id,
            'store_id' => $store->id,
            'price' => 100.00,
            'is_available' => true,
        ]);

        // Add to wishlist
        $wishlistResponse = $this->postJson('/wishlist', ['product_id' => $product->id]);
        $wishlistResponse->assertStatus(200);

        // Verify wishlist
        $wishlistIndexResponse = $this->getJson('/wishlist');
        $wishlistIndexResponse->assertStatus(200);
        $wishlistIndexResponse->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'product',
                    'created_at',
                ]
            ]
        ]);
    }

    /**
     * @test
     */
    public function it_can_handle_price_alerts_integration()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $currency = Currency::factory()->create();
        $store = Store::factory()->create(['currency_id' => $currency->id]);
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();
        
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'store_id' => $store->id,
            'is_active' => true,
        ]);

        $priceOffer = PriceOffer::factory()->create([
            'product_id' => $product->id,
            'store_id' => $store->id,
            'price' => 200.00,
            'is_available' => true,
        ]);

        // Create price alert
        $alertData = [
            'product_id' => $product->id,
            'target_price' => 150.00,
            'is_active' => true,
        ];

        $alertResponse = $this->postJson('/price-alerts', $alertData);
        $alertResponse->assertStatus(201);

        // Verify alert was created
        $this->assertDatabaseHas('price_alerts', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'target_price' => 150.00,
        ]);
    }

    /**
     * @test
     */
    public function it_can_handle_multi_language_integration()
    {
        $currency = Currency::factory()->create();
        $store = Store::factory()->create(['currency_id' => $currency->id]);
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();
        
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'store_id' => $store->id,
            'is_active' => true,
        ]);

        $priceOffer = PriceOffer::factory()->create([
            'product_id' => $product->id,
            'store_id' => $store->id,
            'price' => 100.00,
            'is_available' => true,
        ]);

        // Test with different locales
        $response = $this->withHeaders(['Accept-Language' => 'ar'])
            ->getJson('/api/price-search?q=Test');

        $response->assertStatus(200);
        // Note: Locale testing may require additional middleware setup
    }
}
