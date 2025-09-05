<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function can_create_category(): void
    {
        $category = Category::factory()->create(['name' => 'Electronics']);
        $this->assertDatabaseHas('categories', ['name' => 'Electronics']);
    }

    #[Test]
    public function index_route_exists(): void
    {
        $response = $this->get('/categories');
        $response->assertSuccessful();
    }
}
