<?php

namespace Tests\Unit\Models;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function product_belongs_to_category()
    {
        $product = Product::factory()->create();
        $this->assertInstanceOf(Category::class, $product->category);
    }

    /** @test */
    public function product_belongs_to_brand()
    {
        $product = Product::factory()->create();
        $this->assertInstanceOf(Brand::class, $product->brand);
    }
}
