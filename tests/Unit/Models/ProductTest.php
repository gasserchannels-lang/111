<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function product_belongs_to_category(): void
    {
        $product = Product::factory()->create();
        $this->assertInstanceOf(Category::class, $product->category);
    }

    #[Test]
    public function product_belongs_to_brand(): void
    {
        $product = Product::factory()->create();
        $this->assertInstanceOf(Brand::class, $product->brand);
    }
}
