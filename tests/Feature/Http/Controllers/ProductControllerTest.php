<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function index_can_be_accessed(): void
    {
        $response = $this->get('/products');
        $response->assertSuccessful();
    }

    #[Test]
    public function can_create_product_with_factory(): void
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'brand_id' => $brand->id]);
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }
}
