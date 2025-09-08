<?php

declare(strict_types=1);

namespace Tests\Unit\Controllers;

use App\Http\Controllers\CategoryController;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\View\View;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    private CategoryController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new CategoryController();
    }

    /**
     * @test
     */
    public function it_can_display_categories_index(): void
    {
        $response = $this->controller->index();

        $this->assertInstanceOf(View::class, $response);
        $this->assertEquals('categories.index', $response->getName());
    }

    /**
     * @test
     */
    public function it_can_show_category_by_slug(): void
    {
        $category = Category::factory()->create(['slug' => 'test-category']);
        Product::factory()->count(5)->create([
            'category_id' => $category->id,
            'is_active' => true
        ]);

        $response = $this->controller->show('test-category');

        $this->assertInstanceOf(View::class, $response);
        $this->assertEquals('category-show', $response->getName());
        
        $viewData = $response->getData();
        $this->assertEquals($category->id, $viewData['category']->id);
        $this->assertCount(5, $viewData['products']);
    }

    /**
     * @test
     */
    public function it_throws_exception_for_non_existent_category(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->controller->show('non-existent-category');
    }

    /**
     * @test
     */
    public function it_only_shows_active_products_in_category(): void
    {
        $category = Category::factory()->create(['slug' => 'test-category']);
        
        // Create active and inactive products
        Product::factory()->count(3)->create([
            'category_id' => $category->id,
            'is_active' => true
        ]);
        Product::factory()->count(2)->create([
            'category_id' => $category->id,
            'is_active' => false
        ]);

        $response = $this->controller->show('test-category');
        $products = $response->getData()['products'];

        $this->assertCount(3, $products);
        foreach ($products as $product) {
            $this->assertTrue($product->is_active);
        }
    }

    /**
     * @test
     */
    public function it_orders_products_by_latest(): void
    {
        $category = Category::factory()->create(['slug' => 'test-category']);
        
        // Create products with different timestamps
        $oldProduct = Product::factory()->create([
            'category_id' => $category->id,
            'is_active' => true,
            'created_at' => now()->subDays(2)
        ]);
        $newProduct = Product::factory()->create([
            'category_id' => $category->id,
            'is_active' => true,
            'created_at' => now()
        ]);

        $response = $this->controller->show('test-category');
        $products = $response->getData()['products'];

        $this->assertEquals($newProduct->id, $products->first()->id);
        $this->assertEquals($oldProduct->id, $products->last()->id);
    }

    /**
     * @test
     */
    public function it_paginates_products_with_twelve_per_page(): void
    {
        $category = Category::factory()->create(['slug' => 'test-category']);
        Product::factory()->count(25)->create([
            'category_id' => $category->id,
            'is_active' => true
        ]);

        $response = $this->controller->show('test-category');
        $products = $response->getData()['products'];

        $this->assertCount(12, $products);
        $this->assertEquals(25, $products->total());
        $this->assertEquals(3, $products->lastPage());
    }

    /**
     * @test
     */
    public function it_returns_correct_view_data_structure(): void
    {
        $category = Category::factory()->create(['slug' => 'test-category']);
        Product::factory()->count(3)->create([
            'category_id' => $category->id,
            'is_active' => true
        ]);

        $response = $this->controller->show('test-category');
        $viewData = $response->getData();

        $this->assertArrayHasKey('category', $viewData);
        $this->assertArrayHasKey('products', $viewData);
        $this->assertInstanceOf(Category::class, $viewData['category']);
    }

    /**
     * @test
     */
    public function it_handles_empty_category_products(): void
    {
        $category = Category::factory()->create(['slug' => 'empty-category']);
        // No products created

        $response = $this->controller->show('empty-category');
        $products = $response->getData()['products'];

        $this->assertCount(0, $products);
        $this->assertEquals(0, $products->total());
    }

    /**
     * @test
     */
    public function it_finds_category_by_exact_slug_match(): void
    {
        $category1 = Category::factory()->create(['slug' => 'electronics']);
        $category2 = Category::factory()->create(['slug' => 'electronics-accessories']);

        $response = $this->controller->show('electronics');
        $viewData = $response->getData();

        $this->assertEquals($category1->id, $viewData['category']->id);
        $this->assertEquals('electronics', $viewData['category']->slug);
    }
}
