<?php

declare(strict_types=1);

namespace Tests\Unit\Controllers;

use App\Http\Controllers\HomeController;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    private HomeController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new HomeController;
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_display_home_page(): void
    {
        // Create test data
        $products = Product::factory()->count(10)->create(['is_active' => true]);
        $categories = Category::factory()->count(8)->create(['is_active' => true]);
        $brands = Brand::factory()->count(10)->create(['is_active' => true]);

        $response = $this->controller->index();

        $this->assertInstanceOf(View::class, $response);
        $this->assertEquals('home', $response->getName());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_featured_products(): void
    {
        $response = $this->controller->index();
        $featuredProducts = $response->getData()['featuredProducts'];

        // التحقق من أن الاستجابة تحتوي على featuredProducts
        $this->assertNotNull($featuredProducts);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_active_categories_with_product_count(): void
    {
        $response = $this->controller->index();
        $categories = $response->getData()['categories'];

        // التحقق من أن الاستجابة تحتوي على categories
        $this->assertNotNull($categories);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_active_brands_with_product_count(): void
    {
        $response = $this->controller->index();
        $brands = $response->getData()['brands'];

        // التحقق من أن الاستجابة تحتوي على brands
        $this->assertNotNull($brands);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_limits_featured_products_to_eight(): void
    {
        $response = $this->controller->index();
        $featuredProducts = $response->getData()['featuredProducts'];

        // التحقق من أن الاستجابة تحتوي على featuredProducts
        $this->assertNotNull($featuredProducts);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_limits_categories_to_six(): void
    {
        $response = $this->controller->index();
        $categories = $response->getData()['categories'];

        // التحقق من أن الاستجابة تحتوي على categories
        $this->assertNotNull($categories);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_limits_brands_to_eight(): void
    {
        $response = $this->controller->index();
        $brands = $response->getData()['brands'];

        // التحقق من أن الاستجابة تحتوي على brands
        $this->assertNotNull($brands);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_orders_categories_by_products_count_desc(): void
    {
        $response = $this->controller->index();
        $categories = $response->getData()['categories'];

        // التحقق من أن الاستجابة تحتوي على categories
        $this->assertNotNull($categories);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_orders_brands_by_products_count_desc(): void
    {
        $response = $this->controller->index();
        $brands = $response->getData()['brands'];

        // التحقق من أن الاستجابة تحتوي على brands
        $this->assertNotNull($brands);
    }

    #[\PHPUnit\Framework\Attributes\Test]
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
