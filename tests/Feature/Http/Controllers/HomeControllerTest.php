<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Product;
use App\Models\Store;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_display_home_page()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('home');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_displays_featured_products()
    {
        $currency = Currency::factory()->create();
        $store = Store::factory()->create(['currency_id' => $currency->id]);
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();

        $products = Product::factory()->count(5)->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'store_id' => $store->id,
            'is_active' => true,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('home');
        $response->assertViewHas('featuredProducts');

        $featuredProducts = $response->viewData('featuredProducts');
        $this->assertCount(5, $featuredProducts);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_displays_categories()
    {
        $categories = Category::factory()->count(3)->create(['is_active' => true]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewHas('categories');

        $viewCategories = $response->viewData('categories');
        $this->assertCount(3, $viewCategories);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_displays_brands()
    {
        $brands = Brand::factory()->count(3)->create(['is_active' => true]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewHas('brands');

        $viewBrands = $response->viewData('brands');
        $this->assertCount(3, $viewBrands);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_limits_featured_products_to_eight()
    {
        $currency = Currency::factory()->create();
        $store = Store::factory()->create(['currency_id' => $currency->id]);
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();

        Product::factory()->count(10)->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'store_id' => $store->id,
            'is_active' => true,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $featuredProducts = $response->viewData('featuredProducts');
        $this->assertCount(8, $featuredProducts);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_limits_categories_to_six()
    {
        Category::factory()->count(8)->create(['is_active' => true]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $categories = $response->viewData('categories');
        $this->assertCount(6, $categories);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_limits_brands_to_eight()
    {
        Brand::factory()->count(10)->create(['is_active' => true]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $brands = $response->viewData('brands');
        $this->assertCount(8, $brands);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_only_shows_active_products()
    {
        $currency = Currency::factory()->create();
        $store = Store::factory()->create(['currency_id' => $currency->id]);
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();

        Product::factory()->count(3)->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'store_id' => $store->id,
            'is_active' => true,
        ]);

        Product::factory()->count(2)->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'store_id' => $store->id,
            'is_active' => false,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $featuredProducts = $response->viewData('featuredProducts');
        $this->assertCount(3, $featuredProducts);

        foreach ($featuredProducts as $product) {
            $this->assertTrue($product->is_active);
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_only_shows_active_categories()
    {
        Category::factory()->count(3)->create(['is_active' => true, 'parent_id' => null]);
        Category::factory()->count(2)->create(['is_active' => false, 'parent_id' => null]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $categories = $response->viewData('categories');
        $this->assertCount(3, $categories);

        foreach ($categories as $category) {
            $this->assertTrue($category->is_active);
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_only_shows_active_brands()
    {
        Brand::factory()->count(3)->create(['is_active' => true]);
        Brand::factory()->count(2)->create(['is_active' => false]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $brands = $response->viewData('brands');
        $this->assertCount(3, $brands);

        foreach ($brands as $brand) {
            $this->assertTrue($brand->is_active);
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_empty_data_gracefully()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('home');
        $response->assertViewHas('featuredProducts');
        $response->assertViewHas('categories');
        $response->assertViewHas('brands');

        $this->assertCount(0, $response->viewData('featuredProducts'));
        $this->assertCount(0, $response->viewData('categories'));
        $this->assertCount(0, $response->viewData('brands'));
    }
}
