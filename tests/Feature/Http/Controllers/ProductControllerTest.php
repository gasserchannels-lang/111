<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Product;
use App\Models\Store;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_display_products_index()
    {
        $response = $this->get('/products');

        $response->assertStatus(200);
        $response->assertViewIs('products.index');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_show_product_by_slug()
    {
        $currency = Currency::factory()->create();
        $store = Store::factory()->create(['currency_id' => $currency->id]);
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'slug' => 'test-product',
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'store_id' => $store->id,
            'is_active' => true,
        ]);

        $response = $this->get('/products/test-product');

        $response->assertStatus(200);
        $response->assertViewIs('product-show');
        $response->assertViewHas('product');

        $viewProduct = $response->viewData('product');
        $this->assertEquals($product->id, $viewProduct->id);
        $this->assertEquals('test-product', $viewProduct->slug);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_404_for_non_existent_product()
    {
        $response = $this->get('/products/non-existent-product');

        $response->assertStatus(404);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_404_for_inactive_product()
    {
        $currency = Currency::factory()->create();
        $store = Store::factory()->create(['currency_id' => $currency->id]);
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();

        Product::factory()->create([
            'slug' => 'inactive-product',
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'store_id' => $store->id,
            'is_active' => false,
        ]);

        $response = $this->get('/products/inactive-product');

        $response->assertStatus(404);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_loads_product_relationships()
    {
        $currency = Currency::factory()->create();
        $store = Store::factory()->create(['currency_id' => $currency->id]);
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'slug' => 'test-product-with-relations',
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'store_id' => $store->id,
            'is_active' => true,
        ]);

        $response = $this->get('/products/test-product-with-relations');

        $response->assertStatus(200);
        $viewProduct = $response->viewData('product');

        $this->assertNotNull($viewProduct->brand);
        $this->assertNotNull($viewProduct->category);
        $this->assertNotNull($viewProduct->store);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_search_products()
    {
        $currency = Currency::factory()->create();
        $store = Store::factory()->create(['currency_id' => $currency->id]);
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();

        Product::factory()->create([
            'name' => 'Test Product One',
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'store_id' => $store->id,
            'is_active' => true,
        ]);

        Product::factory()->create([
            'name' => 'Another Product',
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'store_id' => $store->id,
            'is_active' => true,
        ]);

        $response = $this->get('/products?search=Test');

        $response->assertStatus(200);
        $response->assertViewIs('products.index');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_filter_products_by_category()
    {
        $currency = Currency::factory()->create();
        $store = Store::factory()->create(['currency_id' => $currency->id]);
        $brand = Brand::factory()->create();
        $category1 = Category::factory()->create(['slug' => 'category-1']);
        $category2 = Category::factory()->create(['slug' => 'category-2']);

        Product::factory()->create([
            'category_id' => $category1->id,
            'brand_id' => $brand->id,
            'store_id' => $store->id,
            'is_active' => true,
        ]);

        Product::factory()->create([
            'category_id' => $category2->id,
            'brand_id' => $brand->id,
            'store_id' => $store->id,
            'is_active' => true,
        ]);

        $response = $this->get('/products?category=category-1');

        $response->assertStatus(200);
        $response->assertViewIs('products.index');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_filter_products_by_brand()
    {
        $currency = Currency::factory()->create();
        $store = Store::factory()->create(['currency_id' => $currency->id]);
        $brand1 = Brand::factory()->create(['slug' => 'brand-1']);
        $brand2 = Brand::factory()->create(['slug' => 'brand-2']);
        $category = Category::factory()->create();

        Product::factory()->create([
            'brand_id' => $brand1->id,
            'category_id' => $category->id,
            'store_id' => $store->id,
            'is_active' => true,
        ]);

        Product::factory()->create([
            'brand_id' => $brand2->id,
            'category_id' => $category->id,
            'store_id' => $store->id,
            'is_active' => true,
        ]);

        $response = $this->get('/products?brand=brand-1');

        $response->assertStatus(200);
        $response->assertViewIs('products.index');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_sort_products_by_price()
    {
        $currency = Currency::factory()->create();
        $store = Store::factory()->create(['currency_id' => $currency->id]);
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();

        Product::factory()->create([
            'price' => 100.00,
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'store_id' => $store->id,
            'is_active' => true,
        ]);

        Product::factory()->create([
            'price' => 50.00,
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'store_id' => $store->id,
            'is_active' => true,
        ]);

        $response = $this->get('/products?sort=price_asc');

        $response->assertStatus(200);
        $response->assertViewIs('products.index');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_sort_products_by_name()
    {
        $currency = Currency::factory()->create();
        $store = Store::factory()->create(['currency_id' => $currency->id]);
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();

        Product::factory()->create([
            'name' => 'Zebra Product',
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'store_id' => $store->id,
            'is_active' => true,
        ]);

        Product::factory()->create([
            'name' => 'Apple Product',
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'store_id' => $store->id,
            'is_active' => true,
        ]);

        $response = $this->get('/products?sort=name_asc');

        $response->assertStatus(200);
        $response->assertViewIs('products.index');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_paginates_products()
    {
        $currency = Currency::factory()->create();
        $store = Store::factory()->create(['currency_id' => $currency->id]);
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();

        Product::factory()->count(25)->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'store_id' => $store->id,
            'is_active' => true,
        ]);

        $response = $this->get('/products');

        $response->assertStatus(200);
        $response->assertViewIs('products.index');
        $response->assertViewHas('products');

        $products = $response->viewData('products');
        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $products);
    }
}
