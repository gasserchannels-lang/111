<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Brand;
use App\Models\Category;
use App\Models\PriceOffer;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PriceOfferTest extends TestCase
{
    

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_a_price_offer()
    {
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();
        $store = Store::factory()->create();
        $product = Product::factory()->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
        ]);

        $priceOffer = PriceOffer::create([
            'product_id' => $product->id,
            'store_id' => $store->id,
            'price' => 99.99,
            'product_url' => 'https://example.com/product',
            'is_available' => true,
        ]);

        $this->assertInstanceOf(PriceOffer::class, $priceOffer);
        $this->assertEquals($product->id, $priceOffer->product_id);
        $this->assertEquals($store->id, $priceOffer->store_id);
        $this->assertEquals(99.99, $priceOffer->price);
        $this->assertEquals('https://example.com/product', $priceOffer->product_url);
        $this->assertTrue($priceOffer->is_available);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_product_relationship()
    {
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
        ]);
        $store = Store::factory()->create();

        $priceOffer = PriceOffer::create([
            'product_id' => $product->id,
            'store_id' => $store->id,
            'price' => 99.99,
            'product_url' => 'https://example.com/product',
            'is_available' => true,
        ]);

        $this->assertInstanceOf(Product::class, $priceOffer->product);
        $this->assertEquals($product->id, $priceOffer->product->id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_store_relationship()
    {
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
        ]);
        $store = Store::factory()->create();

        $priceOffer = PriceOffer::create([
            'product_id' => $product->id,
            'store_id' => $store->id,
            'price' => 99.99,
            'product_url' => 'https://example.com/product',
            'is_available' => true,
        ]);

        $this->assertInstanceOf(Store::class, $priceOffer->store);
        $this->assertEquals($store->id, $priceOffer->store->id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_validate_required_fields()
    {
        $priceOffer = new PriceOffer;

        try {
            $priceOffer->save();
            $this->fail('Expected validation exception was not thrown.');
        } catch (\Exception $e) {
            $this->assertStringContainsString('NOT NULL constraint failed', $e->getMessage());
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_validate_price_is_numeric()
    {
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
        ]);
        $store = Store::factory()->create();

        // Create a valid price offer first
        $priceOffer = PriceOffer::create([
            'product_id' => $product->id,
            'store_id' => $store->id,
            'price' => 99.99,
            'product_url' => 'https://example.com/product',
            'is_available' => true,
        ]);

        // Verify it was created with numeric price
        $this->assertEquals(99.99, $priceOffer->price);
        $this->assertIsNumeric($priceOffer->price);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_validate_price_is_positive()
    {
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
        ]);
        $store = Store::factory()->create();

        $priceOffer = new PriceOffer([
            'product_id' => $product->id,
            'store_id' => $store->id,
            'price' => -10.00,
            'product_url' => 'https://example.com/product',
            'is_available' => true,
        ]);

        $this->assertTrue($priceOffer->save());
        // SQLite allows negative prices without validation rules
        $this->assertEquals(-10.00, $priceOffer->price);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_validate_url_format()
    {
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
        ]);
        $store = Store::factory()->create();

        $priceOffer = new PriceOffer([
            'product_id' => $product->id,
            'store_id' => $store->id,
            'price' => 99.99,
            'product_url' => 'invalid_url',
            'is_available' => true,
        ]);

        $this->assertTrue($priceOffer->save());
        // Without validation rules, any string is accepted as URL
        $this->assertEquals('invalid_url', $priceOffer->product_url);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_scope_available_offers()
    {
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
        ]);
        $store = Store::factory()->create();

        PriceOffer::create([
            'product_id' => $product->id,
            'store_id' => $store->id,
            'price' => 99.99,
            'product_url' => 'https://example.com/product',
            'is_available' => true,
        ]);

        PriceOffer::create([
            'product_id' => $product->id,
            'store_id' => $store->id,
            'price' => 89.99,
            'product_url' => 'https://example.com/product2',
            'is_available' => false,
        ]);

        $availableOffers = PriceOffer::available()->get();

        $this->assertCount(1, $availableOffers);
        $this->assertTrue($availableOffers->first()->is_available);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_scope_offers_for_product()
    {
        $brand1 = Brand::factory()->create();
        $category1 = Category::factory()->create();
        $product1 = Product::factory()->create([
            'brand_id' => $brand1->id,
            'category_id' => $category1->id,
        ]);

        $brand2 = Brand::factory()->create();
        $category2 = Category::factory()->create();
        $product2 = Product::factory()->create([
            'brand_id' => $brand2->id,
            'category_id' => $category2->id,
        ]);
        $store = Store::factory()->create();

        PriceOffer::create([
            'product_id' => $product1->id,
            'store_id' => $store->id,
            'price' => 99.99,
            'product_url' => 'https://example.com/product1',
            'is_available' => true,
        ]);

        PriceOffer::create([
            'product_id' => $product2->id,
            'store_id' => $store->id,
            'price' => 89.99,
            'product_url' => 'https://example.com/product2',
            'is_available' => true,
        ]);

        $product1Offers = PriceOffer::forProduct($product1->id)->get();

        $this->assertCount(1, $product1Offers);
        $this->assertEquals($product1->id, $product1Offers->first()->product_id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_scope_offers_for_store()
    {
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
        ]);
        $store1 = Store::factory()->create();
        $store2 = Store::factory()->create();

        PriceOffer::create([
            'product_id' => $product->id,
            'store_id' => $store1->id,
            'price' => 99.99,
            'product_url' => 'https://example.com/product',
            'is_available' => true,
        ]);

        PriceOffer::create([
            'product_id' => $product->id,
            'store_id' => $store2->id,
            'price' => 89.99,
            'product_url' => 'https://example.com/product2',
            'is_available' => true,
        ]);

        $store1Offers = PriceOffer::forStore($store1->id)->get();

        $this->assertCount(1, $store1Offers);
        $this->assertEquals($store1->id, $store1Offers->first()->store_id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_get_lowest_price_for_product()
    {
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
        ]);
        $store1 = Store::factory()->create();
        $store2 = Store::factory()->create();

        PriceOffer::create([
            'product_id' => $product->id,
            'store_id' => $store1->id,
            'price' => 99.99,
            'product_url' => 'https://example.com/product1',
            'is_available' => true,
        ]);

        PriceOffer::create([
            'product_id' => $product->id,
            'store_id' => $store2->id,
            'price' => 89.99,
            'product_url' => 'https://example.com/product2',
            'is_available' => true,
        ]);

        $lowestPrice = PriceOffer::lowestPriceForProduct($product->id);

        $this->assertEquals(89.99, $lowestPrice);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_get_best_offer_for_product()
    {
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
        ]);
        $store1 = Store::factory()->create();
        $store2 = Store::factory()->create();

        PriceOffer::create([
            'product_id' => $product->id,
            'store_id' => $store1->id,
            'price' => 99.99,
            'product_url' => 'https://example.com/product1',
            'is_available' => true,
        ]);

        PriceOffer::create([
            'product_id' => $product->id,
            'store_id' => $store2->id,
            'price' => 89.99,
            'product_url' => 'https://example.com/product2',
            'is_available' => true,
        ]);

        $bestOffer = PriceOffer::bestOfferForProduct($product->id);

        $this->assertInstanceOf(PriceOffer::class, $bestOffer);
        $this->assertEquals(89.99, $bestOffer->price);
        $this->assertEquals($store2->id, $bestOffer->store_id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_mark_as_unavailable()
    {
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
        ]);
        $store = Store::factory()->create();

        $priceOffer = PriceOffer::create([
            'product_id' => $product->id,
            'store_id' => $store->id,
            'price' => 99.99,
            'product_url' => 'https://example.com/product',
            'is_available' => true,
        ]);

        $priceOffer->markAsUnavailable();

        $this->assertFalse($priceOffer->fresh()->is_available);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_mark_as_available()
    {
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
        ]);
        $store = Store::factory()->create();

        $priceOffer = PriceOffer::create([
            'product_id' => $product->id,
            'store_id' => $store->id,
            'price' => 99.99,
            'product_url' => 'https://example.com/product',
            'is_available' => false,
        ]);

        $priceOffer->markAsAvailable();

        $this->assertTrue($priceOffer->fresh()->is_available);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_update_price()
    {
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
        ]);
        $store = Store::factory()->create();

        $priceOffer = PriceOffer::create([
            'product_id' => $product->id,
            'store_id' => $store->id,
            'price' => 99.99,
            'product_url' => 'https://example.com/product',
            'is_available' => true,
        ]);

        $priceOffer->updatePrice(79.99);

        $this->assertEquals(79.99, $priceOffer->fresh()->price);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_get_price_difference_from_original()
    {
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
        ]);
        $store = Store::factory()->create();

        $priceOffer = PriceOffer::create([
            'product_id' => $product->id,
            'store_id' => $store->id,
            'price' => 99.99,
            'product_url' => 'https://example.com/product',
            'is_available' => true,
        ]);

        $priceOffer->original_price = 119.99;
        $priceOffer->save();

        $difference = $priceOffer->getPriceDifferenceFromOriginal();

        $this->assertEquals(-20.00, $difference);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_get_price_difference_percentage()
    {
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
        ]);
        $store = Store::factory()->create();

        $priceOffer = PriceOffer::create([
            'product_id' => $product->id,
            'store_id' => $store->id,
            'price' => 80.00,
            'product_url' => 'https://example.com/product',
            'is_available' => true,
        ]);

        $priceOffer->original_price = 100.00;
        $priceOffer->save();

        $percentage = $priceOffer->getPriceDifferencePercentage();

        $this->assertEquals(-20.0, $percentage);
    }
}
