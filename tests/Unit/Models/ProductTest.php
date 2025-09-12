<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Product;
use App\Models\Store;
use Tests\TestCase;

class ProductTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_a_product()
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

        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals('Test Product', $product->name);
        $this->assertTrue($product->is_active);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_brand_relationship()
    {
        $product = Product::factory()->create();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $product->brand());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_category_relationship()
    {
        $product = Product::factory()->create();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $product->category());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_store_relationship()
    {
        $product = Product::factory()->create();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $product->store());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_price_offers_relationship()
    {
        $product = Product::factory()->create();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $product->priceOffers());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_reviews_relationship()
    {
        $product = Product::factory()->create();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $product->reviews());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_wishlist_relationship()
    {
        $product = Product::factory()->create();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $product->wishlists());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_scope_active_products()
    {
        $activeProduct = Product::factory()->create(['is_active' => true]);
        $inactiveProduct = Product::factory()->create(['is_active' => false]);

        $activeProducts = Product::active()->get();

        $this->assertTrue($activeProducts->contains($activeProduct));
        $this->assertFalse($activeProducts->contains($inactiveProduct));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_scope_products_by_brand()
    {
        $brand = Brand::factory()->create();
        $product1 = Product::factory()->create(['brand_id' => $brand->id]);
        $product2 = Product::factory()->create(['brand_id' => $brand->id]);
        $product3 = Product::factory()->create();

        $brandProducts = Product::where('brand_id', $brand->id)->get();

        $this->assertTrue($brandProducts->contains($product1));
        $this->assertTrue($brandProducts->contains($product2));
        $this->assertFalse($brandProducts->contains($product3));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_scope_products_by_category()
    {
        $category = Category::factory()->create();
        $product1 = Product::factory()->create(['category_id' => $category->id]);
        $product2 = Product::factory()->create(['category_id' => $category->id]);
        $product3 = Product::factory()->create();

        $categoryProducts = Product::where('category_id', $category->id)->get();

        $this->assertTrue($categoryProducts->contains($product1));
        $this->assertTrue($categoryProducts->contains($product2));
        $this->assertFalse($categoryProducts->contains($product3));
    }
}
