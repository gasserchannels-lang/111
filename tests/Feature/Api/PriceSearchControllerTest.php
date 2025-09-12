<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Currency;
use App\Models\PriceOffer;
use App\Models\Product;
use App\Models\Store;
use Tests\TestCase;

class PriceSearchControllerTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_search_prices_by_product_name()
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
            'price' => 99.99,
            'is_available' => true,
        ]);

        $response = $this->getJson('/api/price-search?q=Test Product');

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
                        ],
                    ],
                ],
            ],
            'total',
            'query',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_empty_results_for_non_existent_product()
    {
        $response = $this->getJson('/api/price-search?q=NonExistentProduct');

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [],
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_empty_search_query()
    {
        $response = $this->getJson('/api/price-search?q=');

        $response->assertStatus(400);
        $response->assertJson([
            'data' => [],
            'message' => 'Search query is required',
        ]);
    }
}
