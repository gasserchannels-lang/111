<?php

declare(strict_types=1);

namespace Tests\Unit\Controllers;

use App\Http\Controllers\BrandController;
use App\Models\Brand;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Tests\TestCase;

class BrandControllerTest extends TestCase
{
    use RefreshDatabase;

    private BrandController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new BrandController();
    }

    /**
     * @test
     */
    public function it_can_display_brands_index(): void
    {
        Brand::factory()->count(5)->create();

        $response = $this->controller->index();

        $this->assertInstanceOf(View::class, $response);
        $this->assertEquals('brands.index', $response->getName());

        $brands = $response->getData()['brands'];
        $this->assertCount(5, $brands);
    }

    /**
     * @test
     */
    public function it_paginates_brands_with_twenty_per_page(): void
    {
        Brand::factory()->count(25)->create();

        $response = $this->controller->index();
        $brands = $response->getData()['brands'];

        $this->assertCount(20, $brands);
        $this->assertEquals(25, $brands->total());
        $this->assertEquals(2, $brands->lastPage());
    }

    /**
     * @test
     */
    public function it_loads_products_relationship_in_index(): void
    {
        $brand = Brand::factory()->create();
        Product::factory()->count(3)->create(['brand_id' => $brand->id]);

        $response = $this->controller->index();
        $brands = $response->getData()['brands'];

        $this->assertTrue($brands->first()->relationLoaded('products'));
    }

    /**
     * @test
     */
    public function it_can_show_create_form(): void
    {
        $response = $this->controller->create();

        $this->assertInstanceOf(View::class, $response);
        $this->assertEquals('brands.create', $response->getName());
    }

    /**
     * @test
     */
    public function it_can_store_new_brand(): void
    {
        $request = Request::create('/brands', 'POST', [
            'name' => 'Test Brand',
            'slug' => 'test-brand',
            'description' => 'Test Description',
            'logo_url' => 'https://example.com/logo.png',
            'website_url' => 'https://example.com',
            'is_active' => true,
        ]);

        $response = $this->controller->store($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(route('brands.index'), $response->getTargetUrl());

        $this->assertDatabaseHas('brands', [
            'name' => 'Test Brand',
            'slug' => 'test-brand',
        ]);
    }

    /**
     * @test
     */
    public function it_validates_required_fields_when_storing(): void
    {
        $request = Request::create('/brands', 'POST', []);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->controller->store($request);
    }

    /**
     * @test
     */
    public function it_validates_unique_name_when_storing(): void
    {
        Brand::factory()->create(['name' => 'Existing Brand']);

        $request = Request::create('/brands', 'POST', [
            'name' => 'Existing Brand',
            'slug' => 'new-slug',
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->controller->store($request);
    }

    /**
     * @test
     */
    public function it_validates_unique_slug_when_storing(): void
    {
        Brand::factory()->create(['slug' => 'existing-slug']);

        $request = Request::create('/brands', 'POST', [
            'name' => 'New Brand',
            'slug' => 'existing-slug',
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->controller->store($request);
    }

    /**
     * @test
     */
    public function it_can_show_brand_with_products(): void
    {
        $brand = Brand::factory()->create();
        Product::factory()->count(3)->create(['brand_id' => $brand->id]);

        $response = $this->controller->show($brand);

        $this->assertInstanceOf(View::class, $response);
        $this->assertEquals('brands.show', $response->getName());

        $viewBrand = $response->getData()['brand'];
        $this->assertEquals($brand->id, $viewBrand->id);
        $this->assertTrue($viewBrand->relationLoaded('products'));
        $this->assertCount(3, $viewBrand->products);
    }

    /**
     * @test
     */
    public function it_can_show_edit_form(): void
    {
        $brand = Brand::factory()->create();

        $response = $this->controller->edit($brand);

        $this->assertInstanceOf(View::class, $response);
        $this->assertEquals('brands.edit', $response->getName());

        $viewBrand = $response->getData()['brand'];
        $this->assertEquals($brand->id, $viewBrand->id);
    }

    /**
     * @test
     */
    public function it_can_update_brand(): void
    {
        $brand = Brand::factory()->create([
            'name' => 'Old Name',
            'slug' => 'old-slug',
        ]);

        $request = Request::create('/brands/' . $brand->id, 'PUT', [
            'name' => 'Updated Name',
            'slug' => 'updated-slug',
            'description' => 'Updated Description',
            'is_active' => false,
        ]);

        $response = $this->controller->update($request, $brand);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(route('brands.index'), $response->getTargetUrl());

        $brand->refresh();
        $this->assertEquals('Updated Name', $brand->name);
        $this->assertEquals('updated-slug', $brand->slug);
        $this->assertFalse($brand->is_active);
    }

    /**
     * @test
     */
    public function it_validates_unique_name_when_updating_excluding_current_brand(): void
    {
        $brand1 = Brand::factory()->create(['name' => 'Brand One']);
        $brand2 = Brand::factory()->create(['name' => 'Brand Two']);

        // Should allow updating brand2 to keep its own name
        $request = Request::create('/brands/' . $brand2->id, 'PUT', [
            'name' => 'Brand Two',
            'slug' => 'brand-two-updated',
        ]);

        $response = $this->controller->update($request, $brand2);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    /**
     * @test
     */
    public function it_validates_unique_slug_when_updating_excluding_current_brand(): void
    {
        $brand1 = Brand::factory()->create(['slug' => 'brand-one']);
        $brand2 = Brand::factory()->create(['slug' => 'brand-two']);

        // Should allow updating brand2 to keep its own slug
        $request = Request::create('/brands/' . $brand2->id, 'PUT', [
            'name' => 'Updated Brand Two',
            'slug' => 'brand-two',
        ]);

        $response = $this->controller->update($request, $brand2);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    /**
     * @test
     */
    public function it_can_delete_brand(): void
    {
        $brand = Brand::factory()->create();

        $response = $this->controller->destroy($brand);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(route('brands.index'), $response->getTargetUrl());

        $this->assertDatabaseMissing('brands', ['id' => $brand->id]);
    }

    /**
     * @test
     */
    public function it_force_deletes_brand(): void
    {
        $brand = Brand::factory()->create();
        $brandId = $brand->id;

        $this->controller->destroy($brand);

        // Verify the brand is completely removed from database
        $this->assertDatabaseMissing('brands', ['id' => $brandId]);

        // Verify it's not in soft deleted records either
        $this->assertNull(Brand::withTrashed()->find($brandId));
    }

    /**
     * @test
     */
    public function it_validates_url_formats(): void
    {
        $request = Request::create('/brands', 'POST', [
            'name' => 'Test Brand',
            'slug' => 'test-brand',
            'logo_url' => 'invalid-url',
            'website_url' => 'also-invalid',
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->controller->store($request);
    }

    /**
     * @test
     */
    public function it_handles_optional_fields(): void
    {
        $request = Request::create('/brands', 'POST', [
            'name' => 'Minimal Brand',
            'slug' => 'minimal-brand',
        ]);

        $response = $this->controller->store($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);

        $this->assertDatabaseHas('brands', [
            'name' => 'Minimal Brand',
            'slug' => 'minimal-brand',
            'description' => null,
            'logo_url' => null,
            'website_url' => null,
        ]);
    }
}
