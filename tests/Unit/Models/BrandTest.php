<?php

namespace Tests\Unit\Models;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_brand()
    {
        $brand = Brand::factory()->create([
            'name' => 'Test Brand',
            'description' => 'Test Description',
            'logo_url' => 'https://example.com/logo.png',
            'website_url' => 'https://example.com',
            'is_active' => true,
        ]);

        $this->assertInstanceOf(Brand::class, $brand);
        $this->assertEquals('Test Brand', $brand->name);
        $this->assertEquals('Test Description', $brand->description);
        $this->assertEquals('https://example.com/logo.png', $brand->logo_url);
        $this->assertEquals('https://example.com', $brand->website_url);
        $this->assertTrue($brand->is_active);
    }

    /** @test */
    public function it_has_products_relationship()
    {
        $brand = Brand::factory()->create();
        $product = Product::factory()->create(['brand_id' => $brand->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $brand->products);
        $this->assertCount(1, $brand->products);
        $this->assertTrue($brand->products->contains($product));
    }

    /** @test */
    public function it_can_validate_required_fields()
    {
        $brand = new Brand;

        $this->assertFalse($brand->validate());
        $this->assertArrayHasKey('name', $brand->getErrors());
    }

    /** @test */
    public function it_can_validate_name_length()
    {
        $brand = Brand::factory()->make(['name' => str_repeat('a', 256)]);

        $this->assertFalse($brand->validate());
        $this->assertArrayHasKey('name', $brand->getErrors());
    }

    /** @test */
    public function it_can_validate_website_url_format()
    {
        $brand = Brand::factory()->make(['website_url' => 'invalid-url']);

        $this->assertFalse($brand->validate());
        $this->assertArrayHasKey('website_url', $brand->getErrors());
    }

    /** @test */
    public function it_can_validate_logo_url_format()
    {
        $brand = Brand::factory()->make(['logo_url' => 'invalid-url']);

        $this->assertFalse($brand->validate());
        $this->assertArrayHasKey('logo_url', $brand->getErrors());
    }

    /** @test */
    public function it_can_scope_active_brands()
    {
        Brand::factory()->create(['is_active' => true]);
        Brand::factory()->create(['is_active' => false]);

        $activeBrands = Brand::active()->get();

        $this->assertCount(1, $activeBrands);
        $this->assertTrue($activeBrands->first()->is_active);
    }

    /** @test */
    public function it_can_search_brands_by_name()
    {
        Brand::factory()->create(['name' => 'Apple']);
        Brand::factory()->create(['name' => 'Samsung']);
        Brand::factory()->create(['name' => 'Google']);

        $results = Brand::search('Apple')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Apple', $results->first()->name);
    }

    /** @test */
    public function it_can_get_brand_with_products_count()
    {
        $brand = Brand::factory()->create();
        Product::factory()->count(3)->create(['brand_id' => $brand->id]);

        $brandWithCount = Brand::withCount('products')->find($brand->id);

        $this->assertEquals(3, $brandWithCount->products_count);
    }

    /** @test */
    public function it_can_soft_delete_brand()
    {
        $brand = Brand::factory()->create();

        $brand->delete();

        $this->assertSoftDeleted('brands', ['id' => $brand->id]);
    }

    /** @test */
    public function it_can_restore_soft_deleted_brand()
    {
        $brand = Brand::factory()->create();
        $brand->delete();

        $brand->restore();

        $this->assertDatabaseHas('brands', [
            'id' => $brand->id,
            'deleted_at' => null,
        ]);
    }
}
