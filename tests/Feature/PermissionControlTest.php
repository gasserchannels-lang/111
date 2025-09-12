<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermissionControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles and permissions
        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);

        $createProductPermission = Permission::create(['name' => 'create-product']);
        $editProductPermission = Permission::create(['name' => 'edit-product']);
        $deleteProductPermission = Permission::create(['name' => 'delete-product']);

        $adminRole->givePermissionTo([$createProductPermission, $editProductPermission, $deleteProductPermission]);
        $userRole->givePermissionTo([$createProductPermission]);
    }

    /** @test */
    public function admin_can_access_admin_panel()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $response = $this->get('/admin/dashboard');
        $response->assertStatus(200);
    }

    /** @test */
    public function regular_user_cannot_access_admin_panel()
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $this->actingAs($user);

        $response = $this->get('/admin/dashboard');
        $response->assertStatus(403);
    }

    /** @test */
    public function user_with_permission_can_create_product()
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $this->actingAs($user);

        $response = $this->postJson('/api/products', [
            'name' => 'Test Product',
            'price' => 100.00,
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function user_without_permission_cannot_edit_product()
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $this->actingAs($user);

        $response = $this->putJson('/api/products/1', [
            'name' => 'Updated Product',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_delete_any_product()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $response = $this->deleteJson('/api/products/1');
        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_only_access_own_data()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $this->actingAs($user1);

        $response = $this->getJson('/api/user/'.$user2->id.'/orders');
        $response->assertStatus(403);
    }
}
