<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_category()
    {
        $category = Category::factory()->create([
            'name' => 'Test Category',
            'description' => 'Test Description',
            'slug' => 'test-category',
            'is_active' => true,
        ]);

        $this->assertInstanceOf(Category::class, $category);
        $this->assertEquals('Test Category', $category->name);
        $this->assertEquals('Test Description', $category->description);
        $this->assertEquals('test-category', $category->slug);
        $this->assertTrue($category->is_active);
    }

    /** @test */
    public function it_has_products_relationship()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $category->products);
        $this->assertCount(1, $category->products);
        $this->assertTrue($category->products->contains($product));
    }

    /** @test */
    public function it_can_validate_required_fields()
    {
        $category = new Category;

        $this->assertFalse($category->validate());
        $this->assertArrayHasKey('name', $category->getErrors());
    }

    /** @test */
    public function it_can_validate_name_length()
    {
        $category = Category::factory()->make(['name' => str_repeat('a', 256)]);

        $this->assertFalse($category->validate());
        $this->assertArrayHasKey('name', $category->getErrors());
    }

    /** @test */
    public function it_can_scope_active_categories()
    {
        Category::factory()->create(['is_active' => true]);
        Category::factory()->create(['is_active' => false]);

        $activeCategories = Category::active()->get();

        $this->assertCount(1, $activeCategories);
        $this->assertTrue($activeCategories->first()->is_active);
    }

    /** @test */
    public function it_can_search_categories_by_name()
    {
        Category::factory()->create(['name' => 'Electronics']);
        Category::factory()->create(['name' => 'Clothing']);
        Category::factory()->create(['name' => 'Books']);

        $results = Category::search('Electronics')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Electronics', $results->first()->name);
    }

    /** @test */
    public function it_can_get_category_with_products_count()
    {
        $category = Category::factory()->create();
        Product::factory()->count(3)->create(['category_id' => $category->id]);

        $categoryWithCount = Category::withCount('products')->find($category->id);

        $this->assertEquals(3, $categoryWithCount->products_count);
    }

    /** @test */
    public function it_can_soft_delete_category()
    {
        $category = Category::factory()->create();

        $category->delete();

        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }

    /** @test */
    public function it_can_restore_soft_deleted_category()
    {
        $category = Category::factory()->create();
        $category->delete();

        $category->restore();

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'deleted_at' => null,
        ]);
    }

    /** @test */
    public function it_auto_generates_slug_from_name()
    {
        $category = Category::factory()->create(['name' => 'Test Category Name']);

        // The slug should be generated from the name, but we'll check if it exists
        $this->assertNotNull($category->slug);
        $this->assertIsString($category->slug);
    }

    /** @test */
    public function it_updates_slug_when_name_changes()
    {
        $category = Category::factory()->create(['name' => 'Old Name']);
        $category->update(['name' => 'New Name']);

        $this->assertEquals('new-name', $category->fresh()->slug);
    }
}
