<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function index_displays_products()
    {
        Product::factory()->create();

        $response = $this->get(route('products.index')); // استخدام اسم المسار أفضل
        $response->assertStatus(200);
    }

    /** @test */
    public function show_displays_single_product()
    {
        $product = Product::factory()->create();

        $response = $this->get(route('products.show', $product));
        $response->assertStatus(200);
    }

    /** @test */
    public function search_filters_products()
    {
        $response = $this->get(route('products.index', ['search' => 'test']));
        $response->assertStatus(200);
    }
}
