<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BrandControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function index_displays_brands(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        Brand::factory()->count(3)->create();

        $response = $this->get('/brands');

        $response->assertSuccessful();
        $response->assertViewIs('brands.index');
        $response->assertViewHas('brands');
    }

    #[Test]
    public function create_displays_form(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $response = $this->get('/brands/create');

        $response->assertSuccessful();
        $response->assertViewIs('brands.create');
    }

    #[Test]
    public function store_creates_brand(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $brandData = [
            'name' => 'Test Brand',
            'slug' => 'test-brand',
            'description' => 'Test description',
            'is_active' => true,
        ];

        $response = $this->post('/brands', $brandData);

        $response->assertRedirect('/brands');
        $this->assertDatabaseHas('brands', [
            'name' => 'Test Brand',
            'slug' => 'test-brand',
            'description' => 'Test description',
            'is_active' => 1
        ]);
    }

    #[Test]
    public function store_validates_required_fields(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $response = $this->post('/brands', []);

        $response->assertSessionHasErrors(['name', 'slug']);
    }

    #[Test]
    public function show_displays_brand(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $brand = Brand::factory()->create();
        Product::factory()->count(2)->create(['brand_id' => $brand->id]);

        $response = $this->get("/brands/{$brand->id}");

        $response->assertSuccessful();
        $response->assertViewIs('brands.show');
        $response->assertViewHas('brand', $brand);
    }

    #[Test]
    public function edit_displays_form(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $brand = Brand::factory()->create();

        $response = $this->get("/brands/{$brand->id}/edit");

        $response->assertSuccessful();
        $response->assertViewIs('brands.edit');
        $response->assertViewHas('brand', $brand);
    }

    #[Test]
    public function update_modifies_brand(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $brand = Brand::factory()->create();
        $updateData = [
            'name' => 'Updated Brand',
            'slug' => 'updated-brand',
            'description' => 'Updated description',
            'is_active' => false,
        ];

        $response = $this->put("/brands/{$brand->id}", $updateData);

        $response->assertRedirect('/brands');
        $this->assertDatabaseHas('brands', [
            'id' => $brand->id,
            'name' => 'Updated Brand',
            'slug' => 'updated-brand',
            'description' => 'Updated description',
            'is_active' => 0
        ]);
    }

    #[Test]
    public function destroy_deletes_brand(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $brand = Brand::factory()->create();

        $response = $this->delete("/brands/{$brand->id}");

        $response->assertRedirect('/brands');
        $this->assertDatabaseMissing('brands', ['id' => $brand->id]);
    }

    #[Test]
    public function index_requires_authentication(): void
    {
        $response = $this->get('/brands');

        $response->assertRedirect('/login');
    }

    #[Test]
    public function create_requires_authentication(): void
    {
        $response = $this->get('/brands/create');

        $response->assertRedirect('/login');
    }

    #[Test]
    public function store_requires_authentication(): void
    {
        $response = $this->post('/brands', []);

        $response->assertRedirect('/login');
    }

    #[Test]
    public function show_requires_authentication(): void
    {
        $brand = Brand::factory()->create();

        $response = $this->get("/brands/{$brand->id}");

        $response->assertRedirect('/login');
    }

    #[Test]
    public function edit_requires_authentication(): void
    {
        $brand = Brand::factory()->create();

        $response = $this->get("/brands/{$brand->id}/edit");

        $response->assertRedirect('/login');
    }

    #[Test]
    public function update_requires_authentication(): void
    {
        $brand = Brand::factory()->create();

        $response = $this->put("/brands/{$brand->id}", []);

        $response->assertRedirect('/login');
    }

    #[Test]
    public function destroy_requires_authentication(): void
    {
        $brand = Brand::factory()->create();

        $response = $this->delete("/brands/{$brand->id}");

        $response->assertRedirect('/login');
    }
}
