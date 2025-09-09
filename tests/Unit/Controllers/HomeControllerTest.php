<?php

declare(strict_types=1);

namespace Tests\Unit\Controllers;

use App\Http\Controllers\HomeController;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\View\View;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    private HomeController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new HomeController();
    }

    /**
     * @test
     */
    public function it_can_display_home_page(): void
    {
        // Create test data
        $products = Product::factory()->count(10)->create(['is_active' => true]);
        $categories = Category::factory()->count(8)->create(['is_active' => true]);
        $brands = Brand::factory()->count(10)->create(['is_active' => true]);

        $response = $this->controller->index();

        $this->assertInstanceOf(View::class, $response);
        $this->assertEquals('welcome', $response->getName());
    }

    /**
     * @test
     */
    public function it_returns_featured_products(): void
    {
        // Create active and inactive products
        Product::factory()->count(10)->create(['is_active' => true]);
        Product::factory()->count(5)->create(['is_active' => false]);

        $response = $this->controller->index();
        $featuredProducts = $response->getData()['featuredProducts'];

        $this->assertCount(8, $featuredProducts);
        foreach ($featuredProducts as $product) {
            $this->assertTrue($product->is_active);
        }
    }

    /**
     * @test
     */
    public function it_returns_active_categories_with_product_count(): void
    {
        // Create categories with products
        $activeCategory = Category::factory()->create(['is_active' => true]);
        $inactiveCategory = Category::factory()->create(['is_active' => false]);

        Product::factory()->count(5)->create(['category_id' => $activeCategory->id]);
        Product::factory()->count(3)->create(['category_id' => $inactiveCategory->id]);

        $response = $this->controller->index();
        $categories = $response->getData()['categories'];

        $this->assertCount(1, $categories);
        $this->assertTrue($categories->first()->is_active);
        $this->assertEquals(5, $categories->first()->products_count);
    }

    /**
     * @test
     */
    public function it_returns_active_brands_with_product_count(): void
    {
        // Create brands with products
        $activeBrand = Brand::factory()->create(['is_active' => true]);
        $inactiveBrand = Brand::factory()->create(['is_active' => false]);

        Product::factory()->count(7)->create(['brand_id' => $activeBrand->id]);
        Product::factory()->count(4)->create(['brand_id' => $inactiveBrand->id]);

        $response = $this->controller->index();
        $brands = $response->getData()['brands'];

        $this->assertCount(1, $brands);
        $this->assertTrue($brands->first()->is_active);
        $this->assertEquals(7, $brands->first()->products_count);
    }

    /**
     * @test
     */
    public function it_limits_featured_products_to_eight(): void
    {
        Product::factory()->count(15)->create(['is_active' => true]);

        $response = $this->controller->index();
        $featuredProducts = $response->getData()['featuredProducts'];

        $this->assertCount(8, $featuredProducts);
    }

    /**
     * @test
     */
    public function it_limits_categories_to_six(): void
    {
        Category::factory()->count(10)->create(['is_active' => true]);

        $response = $this->controller->index();
        $categories = $response->getData()['categories'];

        $this->assertCount(6, $categories);
    }

    /**
     * @test
     */
    public function it_limits_brands_to_eight(): void
    {
        Brand::factory()->count(12)->create(['is_active' => true]);

        $response = $this->controller->index();
        $brands = $response->getData()['brands'];

        $this->assertCount(8, $brands);
    }

    /**
     * @test
     */
    public function it_orders_categories_by_products_count_desc(): void
    {
        $category1 = Category::factory()->create(['is_active' => true]);
        $category2 = Category::factory()->create(['is_active' => true]);

        Product::factory()->count(3)->create(['category_id' => $category1->id]);
        Product::factory()->count(7)->create(['category_id' => $category2->id]);

        $response = $this->controller->index();
        $categories = $response->getData()['categories'];

        $this->assertEquals(7, $categories->first()->products_count);
        $this->assertEquals(3, $categories->last()->products_count);
    }

    /**
     * @test
     */
    public function it_orders_brands_by_products_count_desc(): void
    {
        $brand1 = Brand::factory()->create(['is_active' => true]);
        $brand2 = Brand::factory()->create(['is_active' => true]);

        Product::factory()->count(4)->create(['brand_id' => $brand1->id]);
        Product::factory()->count(9)->create(['brand_id' => $brand2->id]);

        $response = $this->controller->index();
        $brands = $response->getData()['brands'];

        $this->assertEquals(9, $brands->first()->products_count);
        $this->assertEquals(4, $brands->last()->products_count);
    }

    /**
     * @test
     */
    public function it_loads_category_and_brand_relationships_for_products(): void
    {
        Product::factory()->count(3)->create(['is_active' => true]);

        $response = $this->controller->index();
        $featuredProducts = $response->getData()['featuredProducts'];

        foreach ($featuredProducts as $product) {
            $this->assertTrue($product->relationLoaded('category'));
            $this->assertTrue($product->relationLoaded('brand'));
        }
    }
}
