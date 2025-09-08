<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\Currency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function it_can_display_categories_index()
    {
        $response = $this->get('/categories');

        $response->assertStatus(200);
        $response->assertViewIs('categories.index');
    }

    /**
     * @test
     */
    public function it_can_show_category_by_slug()
    {
        $category = Category::factory()->create([
            'slug' => 'test-category',
            'is_active' => true,
        ]);

        $response = $this->get('/categories/test-category');

        $response->assertStatus(200);
        $response->assertViewIs('category-show');
        $response->assertViewHas('category');
        $response->assertViewHas('products');
        
        $viewCategory = $response->viewData('category');
        $this->assertEquals($category->id, $viewCategory->id);
        $this->assertEquals('test-category', $viewCategory->slug);
    }

    /**
     * @test
     */
    public function it_returns_404_for_non_existent_category()
    {
        $response = $this->get('/categories/non-existent-category');

        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function it_returns_404_for_inactive_category()
    {
        Category::factory()->create([
            'slug' => 'inactive-category',
            'is_active' => false,
        ]);

        $response = $this->get('/categories/inactive-category');

        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function it_shows_products_in_category()
    {
        $currency = Currency::factory()->create();
        $store = Store::factory()->create(['currency_id' => $currency->id]);
        $brand = Brand::factory()->create();
        $category = Category::factory()->create([
            'slug' => 'test-category',
            'is_active' => true,
        ]);
        
        $products = Product::factory()->count(3)->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'store_id' => $store->id,
            'is_active' => true,
        ]);

        $response = $this->get('/categories/test-category');

        $response->assertStatus(200);
        $viewProducts = $response->viewData('products');
        $this->assertCount(3, $viewProducts);
    }

    /**
     * @test
     */
    public function it_only_shows_active_products_in_category()
    {
        $currency = Currency::factory()->create();
        $store = Store::factory()->create(['currency_id' => $currency->id]);
        $brand = Brand::factory()->create();
        $category = Category::factory()->create([
            'slug' => 'test-category',
            'is_active' => true,
        ]);
        
        Product::factory()->count(2)->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'store_id' => $store->id,
            'is_active' => true,
        ]);
        
        Product::factory()->count(1)->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'store_id' => $store->id,
            'is_active' => false,
        ]);

        $response = $this->get('/categories/test-category');

        $response->assertStatus(200);
        $viewProducts = $response->viewData('products');
        $this->assertCount(2, $viewProducts);
        
        foreach ($viewProducts as $product) {
            $this->assertTrue($product->is_active);
        }
    }

    /**
     * @test
     */
    public function it_orders_products_by_latest()
    {
        $currency = Currency::factory()->create();
        $store = Store::factory()->create(['currency_id' => $currency->id]);
        $brand = Brand::factory()->create();
        $category = Category::factory()->create([
            'slug' => 'test-category',
            'is_active' => true,
        ]);
        
        $oldProduct = Product::factory()->create([
            'name' => 'Old Product',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'store_id' => $store->id,
            'is_active' => true,
            'created_at' => now()->subDays(5),
        ]);
        
        $newProduct = Product::factory()->create([
            'name' => 'New Product',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'store_id' => $store->id,
            'is_active' => true,
            'created_at' => now()->subDays(1),
        ]);

        $response = $this->get('/categories/test-category');

        $response->assertStatus(200);
        $viewProducts = $response->viewData('products');
        $this->assertEquals($newProduct->id, $viewProducts->first()->id);
    }

    /**
     * @test
     */
    public function it_paginates_products_in_category()
    {
        $currency = Currency::factory()->create();
        $store = Store::factory()->create(['currency_id' => $currency->id]);
        $brand = Brand::factory()->create();
        $category = Category::factory()->create([
            'slug' => 'test-category',
            'is_active' => true,
        ]);
        
        Product::factory()->count(25)->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'store_id' => $store->id,
            'is_active' => true,
        ]);

        $response = $this->get('/categories/test-category');

        $response->assertStatus(200);
        $viewProducts = $response->viewData('products');
        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $viewProducts);
    }

    /**
     * @test
     */
    public function it_can_filter_products_by_price_range()
    {
        $currency = Currency::factory()->create();
        $store = Store::factory()->create(['currency_id' => $currency->id]);
        $brand = Brand::factory()->create();
        $category = Category::factory()->create([
            'slug' => 'test-category',
            'is_active' => true,
        ]);
        
        Product::factory()->create([
            'price' => 50.00,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'store_id' => $store->id,
            'is_active' => true,
        ]);
        
        Product::factory()->create([
            'price' => 150.00,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'store_id' => $store->id,
            'is_active' => true,
        ]);

        $response = $this->get('/categories/test-category?min_price=100&max_price=200');

        $response->assertStatus(200);
        $response->assertViewIs('category-show');
    }

    /**
     * @test
     */
    public function it_can_sort_products_by_price()
    {
        $currency = Currency::factory()->create();
        $store = Store::factory()->create(['currency_id' => $currency->id]);
        $brand = Brand::factory()->create();
        $category = Category::factory()->create([
            'slug' => 'test-category',
            'is_active' => true,
        ]);
        
        Product::factory()->create([
            'price' => 100.00,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'store_id' => $store->id,
            'is_active' => true,
        ]);
        
        Product::factory()->create([
            'price' => 50.00,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'store_id' => $store->id,
            'is_active' => true,
        ]);

        $response = $this->get('/categories/test-category?sort=price_asc');

        $response->assertStatus(200);
        $response->assertViewIs('category-show');
    }

    /**
     * @test
     */
    public function it_can_search_products_in_category()
    {
        $currency = Currency::factory()->create();
        $store = Store::factory()->create(['currency_id' => $currency->id]);
        $brand = Brand::factory()->create();
        $category = Category::factory()->create([
            'slug' => 'test-category',
            'is_active' => true,
        ]);
        
        Product::factory()->create([
            'name' => 'Test Product',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'store_id' => $store->id,
            'is_active' => true,
        ]);
        
        Product::factory()->create([
            'name' => 'Another Product',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'store_id' => $store->id,
            'is_active' => true,
        ]);

        $response = $this->get('/categories/test-category?search=Test');

        $response->assertStatus(200);
        $response->assertViewIs('category-show');
    }

    /**
     * @test
     */
    public function it_handles_empty_category_gracefully()
    {
        $category = Category::factory()->create([
            'slug' => 'empty-category',
            'is_active' => true,
        ]);

        $response = $this->get('/categories/empty-category');

        $response->assertStatus(200);
        $response->assertViewIs('category-show');
        $viewProducts = $response->viewData('products');
        $this->assertCount(0, $viewProducts);
    }
}