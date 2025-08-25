<?php

namespace Tests\Unit\Models;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\PriceOffer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function product_belongs_to_a_category()
    {
        $product = Product::factory()->create();

        $this->assertInstanceOf(Category::class, $product->category);
    }

    /** @test */
    public function product_belongs_to_a_brand()
    {
        $product = Product::factory()->create();

        $this->assertInstanceOf(Brand::class, $product->brand);
    }

    /** @test */
    public function product_has_many_price_offers()
    {
        $product = Product::factory()
            ->has(PriceOffer::factory()->count(3))
            ->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $product->priceOffers);
        $this->assertCount(3, $product->priceOffers);
    }
}
