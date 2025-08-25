<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Category;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function index_displays_categories()
    {
        Category::factory()->count(3)->create();
        $response = $this->get(route('categories.index'));
        $response->assertStatus(200);
    }

    /** @test */
    public function show_displays_category_products()
    {
        $category = Category::factory()->create();
        $response = $this->get(route('categories.show', $category));
        $response->assertStatus(200);
    }
}
