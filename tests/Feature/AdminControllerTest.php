<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Set the database connection for testing
        config(['database.default' => 'testing']);

        // Manually run migrations to ensure a clean slate, and force it to avoid interactive prompts.
        Artisan::call('migrate:fresh', [
            '--force' => true,
            '--database' => 'testing',
            '--env' => 'testing',
        ]);

        $this->adminUser = User::factory()->create(['is_admin' => true]);
        $this->regularUser = User::factory()->create(['is_admin' => false]);
    }

    #[Test]
    public function dashboard_requires_authentication(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/login');
    }

    #[Test]
    public function dashboard_requires_admin_privileges(): void
    {
        $this->actingAs($this->regularUser);

        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/');
    }

    #[Test]
    public function dashboard_displays_for_admin(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get('/admin/dashboard');

        $response->assertSuccessful();
        $response->assertViewIs('admin.dashboard');
        $response->assertViewHas('stats');
        $response->assertViewHas('recentUsers');
        $response->assertViewHas('recentProducts');
    }

    #[Test]
    public function users_requires_authentication(): void
    {
        $response = $this->get('/admin/users');

        $response->assertRedirect('/login');
    }

    #[Test]
    public function users_requires_admin_privileges(): void
    {
        $this->actingAs($this->regularUser);

        $response = $this->get('/admin/users');

        $response->assertRedirect('/');
    }

    #[Test]
    public function users_displays_for_admin(): void
    {
        $this->actingAs($this->adminUser);
        User::factory()->count(5)->create();

        $response = $this->get('/admin/users');

        $response->assertSuccessful();
        $response->assertViewIs('admin.users');
        $response->assertViewHas('users');
    }

    #[Test]
    public function products_requires_authentication(): void
    {
        $response = $this->get('/admin/products');

        $response->assertRedirect('/login');
    }

    #[Test]
    public function products_requires_admin_privileges(): void
    {
        $this->actingAs($this->regularUser);

        $response = $this->get('/admin/products');

        $response->assertRedirect('/');
    }

    #[Test]
    public function products_displays_for_admin(): void
    {
        $this->actingAs($this->adminUser);
        Product::factory()->count(5)->create();

        $response = $this->get('/admin/products');

        $response->assertSuccessful();
        $response->assertViewIs('admin.products');
        $response->assertViewHas('products');
    }

    #[Test]
    public function brands_requires_authentication(): void
    {
        $response = $this->get('/admin/brands');

        $response->assertRedirect('/login');
    }

    #[Test]
    public function brands_requires_admin_privileges(): void
    {
        $this->actingAs($this->regularUser);

        $response = $this->get('/admin/brands');

        $response->assertRedirect('/');
    }

    #[Test]
    public function brands_displays_for_admin(): void
    {
        $this->actingAs($this->adminUser);
        Brand::factory()->count(5)->create();

        $response = $this->get('/admin/brands');

        $response->assertSuccessful();
        $response->assertViewIs('admin.brands');
        $response->assertViewHas('brands');
    }

    #[Test]
    public function categories_requires_authentication(): void
    {
        $response = $this->get('/admin/categories');

        $response->assertRedirect('/login');
    }

    #[Test]
    public function categories_requires_admin_privileges(): void
    {
        $this->actingAs($this->regularUser);

        $response = $this->get('/admin/categories');

        $response->assertRedirect('/');
    }

    #[Test]
    public function categories_displays_for_admin(): void
    {
        $this->actingAs($this->adminUser);
        Category::factory()->count(5)->create();

        $response = $this->get('/admin/categories');

        $response->assertSuccessful();
        $response->assertViewIs('admin.categories');
        $response->assertViewHas('categories');
    }

    #[Test]
    public function stores_requires_authentication(): void
    {
        $response = $this->get('/admin/stores');

        $response->assertRedirect('/login');
    }

    #[Test]
    public function stores_requires_admin_privileges(): void
    {
        $this->actingAs($this->regularUser);

        $response = $this->get('/admin/stores');

        $response->assertRedirect('/');
    }

    #[Test]
    public function stores_displays_for_admin(): void
    {
        $this->actingAs($this->adminUser);
        Store::factory()->count(5)->create();

        $response = $this->get('/admin/stores');

        $response->assertSuccessful();
        $response->assertViewIs('admin.stores');
        $response->assertViewHas('stores');
    }

    #[Test]
    public function toggle_user_admin_requires_authentication(): void
    {
        $user = User::factory()->create();

        $this->startSession();

        $response = $this->post("/admin/users/{$user->id}/toggle-admin", [
            '_token' => csrf_token(),
        ]);

        $response->assertRedirect('/login');
    }

    #[Test]
    public function toggle_user_admin_requires_admin_privileges(): void
    {
        $this->actingAs($this->regularUser);
        $user = User::factory()->create();

        $this->startSession();

        $response = $this->post("/admin/users/{$user->id}/toggle-admin", [
            '_token' => csrf_token(),
        ]);

        $response->assertRedirect('/');
    }

    #[Test]
    public function toggle_user_admin_works_for_admin(): void
    {
        $this->actingAs($this->adminUser);
        $user = User::factory()->create(['is_admin' => false]);

        $this->startSession();

        $response = $this->post("/admin/users/{$user->id}/toggle-admin", [
            '_token' => csrf_token(),
        ]);

        $response->assertRedirect();
        $this->assertTrue($user->fresh()->is_admin);
    }
}
