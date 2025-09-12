<?php

namespace Tests\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermissionSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles and permissions
        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);
        $moderatorRole = Role::create(['name' => 'moderator']);

        $createProductPermission = Permission::create(['name' => 'create-product']);
        $editProductPermission = Permission::create(['name' => 'edit-product']);
        $deleteProductPermission = Permission::create(['name' => 'delete-product']);
        $viewUsersPermission = Permission::create(['name' => 'view-users']);
        $editUsersPermission = Permission::create(['name' => 'edit-users']);

        $adminRole->givePermissionTo([$createProductPermission, $editProductPermission, $deleteProductPermission, $viewUsersPermission, $editUsersPermission]);
        $moderatorRole->givePermissionTo([$createProductPermission, $editProductPermission, $viewUsersPermission]);
        $userRole->givePermissionTo([$createProductPermission]);
    }

    /** @test */
    public function user_cannot_access_admin_panel_without_admin_role()
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $this->actingAs($user);

        $response = $this->get('/admin/dashboard');
        $this->assertEquals(403, $response->status());
    }

    /** @test */
    public function user_cannot_edit_products_without_edit_permission()
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $this->actingAs($user);

        $response = $this->putJson('/api/products/1', [
            'name' => 'Updated Product',
        ]);

        $this->assertEquals(403, $response->status());
    }

    /** @test */
    public function user_cannot_delete_products_without_delete_permission()
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $this->actingAs($user);

        $response = $this->deleteJson('/api/products/1');
        $this->assertEquals(403, $response->status());
    }

    /** @test */
    public function moderator_can_edit_products_but_cannot_delete()
    {
        $moderator = User::factory()->create();
        $moderator->assignRole('moderator');
        $this->actingAs($moderator);

        // Can edit
        $response = $this->putJson('/api/products/1', [
            'name' => 'Updated Product',
        ]);
        $this->assertNotEquals(403, $response->status());

        // Cannot delete
        $response = $this->deleteJson('/api/products/1');
        $this->assertEquals(403, $response->status());
    }

    /** @test */
    public function admin_can_perform_all_actions()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        // Can create
        $response = $this->postJson('/api/products', [
            'name' => 'New Product',
            'price' => 100,
        ]);
        $this->assertNotEquals(403, $response->status());

        // Can edit
        $response = $this->putJson('/api/products/1', [
            'name' => 'Updated Product',
        ]);
        $this->assertNotEquals(403, $response->status());

        // Can delete
        $response = $this->deleteJson('/api/products/1');
        $this->assertNotEquals(403, $response->status());
    }

    /** @test */
    public function user_cannot_access_other_users_data()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $this->actingAs($user1);

        $response = $this->getJson('/api/user/'.$user2->id);
        $this->assertEquals(403, $response->status());
    }

    /** @test */
    public function user_can_only_access_own_data()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson('/api/user/'.$user->id);
        $this->assertNotEquals(403, $response->status());
    }

    /** @test */
    public function user_cannot_escalate_privileges()
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $this->actingAs($user);

        // Try to assign admin role to self
        $response = $this->postJson('/api/user/roles', [
            'role' => 'admin',
        ]);

        $this->assertEquals(403, $response->status());
    }

    /** @test */
    public function user_cannot_modify_own_permissions()
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $this->actingAs($user);

        // Try to add admin permission
        $response = $this->postJson('/api/user/permissions', [
            'permission' => 'edit-users',
        ]);

        $this->assertEquals(403, $response->status());
    }

    /** @test */
    public function api_endpoints_respect_permissions()
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $this->actingAs($user);

        $protectedEndpoints = [
            ['method' => 'GET', 'url' => '/api/admin/users'],
            ['method' => 'POST', 'url' => '/api/admin/users'],
            ['method' => 'PUT', 'url' => '/api/admin/users/1'],
            ['method' => 'DELETE', 'url' => '/api/admin/users/1'],
        ];

        foreach ($protectedEndpoints as $endpoint) {
            $response = $this->call($endpoint['method'], $endpoint['url']);
            $this->assertEquals(403, $response->status());
        }
    }

    /** @test */
    public function permission_checks_are_enforced_at_middleware_level()
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $this->actingAs($user);

        // Test middleware protection
        $response = $this->get('/admin/dashboard');
        $this->assertEquals(403, $response->status());
    }

    /** @test */
    public function role_based_access_control_works_correctly()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $moderator = User::factory()->create();
        $moderator->assignRole('moderator');

        $user = User::factory()->create();
        $user->assignRole('user');

        // Admin can access everything
        $this->actingAs($admin);
        $response = $this->get('/admin/dashboard');
        $this->assertNotEquals(403, $response->status());

        // Moderator has limited access
        $this->actingAs($moderator);
        $response = $this->get('/admin/dashboard');
        $this->assertEquals(403, $response->status());

        // User has no admin access
        $this->actingAs($user);
        $response = $this->get('/admin/dashboard');
        $this->assertEquals(403, $response->status());
    }

    /** @test */
    public function permission_denied_returns_proper_error_message()
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $this->actingAs($user);

        $response = $this->get('/admin/dashboard');

        $this->assertEquals(403, $response->status());
        $this->assertStringContainsString('Forbidden', $response->getContent());
    }
}
