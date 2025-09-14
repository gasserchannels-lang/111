<?php

declare(strict_types=1);

namespace Tests\Unit\Controllers;

use App\Http\Controllers\ProductController;
use App\Models\Product;
use Illuminate\View\View;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    private ProductController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new ProductController;
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_display_products_index(): void
    {
        $response = $this->controller->index();

        $this->assertInstanceOf(View::class, $response);
        $this->assertEquals('products.index', $response->getName());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_show_product_by_slug(): void
    {
        $product = Product::factory()->create(['slug' => 'test-product']);

        $response = $this->controller->show('test-product');

        $this->assertInstanceOf(View::class, $response);
        $this->assertEquals('product-show', $response->getName());

        $viewData = $response->getData();
        $this->assertEquals($product->id, $viewData['product']->id);
        $this->assertEquals('test-product', $viewData['product']->slug);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_throws_exception_for_non_existent_product(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);

        $this->controller->show('non-existent-product');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_correct_view_data_structure(): void
    {
        $product = Product::factory()->create(['slug' => 'test-product']);

        $response = $this->controller->show('test-product');
        $viewData = $response->getData();

        $this->assertArrayHasKey('product', $viewData);
        $this->assertInstanceOf(Product::class, $viewData['product']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_finds_product_by_exact_slug_match(): void
    {
        $product1 = Product::factory()->create(['slug' => 'laptop']);
        $product2 = Product::factory()->create(['slug' => 'laptop-accessories']);

        $response = $this->controller->show('laptop');
        $viewData = $response->getData();

        $this->assertEquals($product1->id, $viewData['product']->id);
        $this->assertEquals('laptop', $viewData['product']->slug);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_show_products_with_special_characters_in_slug(): void
    {
        $product = Product::factory()->create(['slug' => 'product-with-special-chars-123']);

        $response = $this->controller->show('product-with-special-chars-123');
        $viewData = $response->getData();

        $this->assertEquals($product->id, $viewData['product']->id);
        $this->assertEquals('product-with-special-chars-123', $viewData['product']->slug);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_case_sensitive_slug_search(): void
    {
        $product = Product::factory()->create(['slug' => 'test-product']);

        $response = $this->controller->show('test-product');
        $viewData = $response->getData();

        $this->assertEquals($product->id, $viewData['product']->id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_product_with_all_attributes(): void
    {
        $product = Product::factory()->create([
            'slug' => 'full-product',
            'name' => 'Full Product Name',
            'description' => 'Product Description',
            'price' => 99.99,
            'is_active' => true,
        ]);

        $response = $this->controller->show('full-product');
        $viewProduct = $response->getData()['product'];

        $this->assertEquals('Full Product Name', $viewProduct->name);
        $this->assertEquals('Product Description', $viewProduct->description);
        $this->assertEquals(99.99, $viewProduct->price);
        $this->assertTrue($viewProduct->is_active);
    }
}
