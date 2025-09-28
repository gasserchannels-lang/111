<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @runTestsInSeparateProcesses
 */
class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpDatabase();
    }

    protected function tearDown(): void
    {
        $this->tearDownDatabase();
        parent::tearDown();
    }

    /** @test */
    public function it_can_display_user_profile()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/profile');

        $response->assertStatus(200)
            ->assertViewIs('user.profile')
            ->assertViewHas('user', $user);
    }

    /** @test */
    public function it_requires_authentication_to_view_profile()
    {
        $response = $this->get('/profile');

        $response->assertStatus(302)
            ->assertRedirect('/login');
    }

    /** @test */
    public function it_can_update_user_profile()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->put('/profile', [
            'name'  => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile updated successfully.',
            ]);
    }

    /** @test */
    public function it_validates_profile_update_request()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->put('/profile', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email']);
    }

    /** @test */
    public function it_requires_authentication_to_update_profile()
    {
        $response = $this->put('/profile', [
            'name'  => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $response->assertStatus(302)
            ->assertRedirect('/login');
    }

    /** @test */
    public function it_can_change_user_password()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->put('/profile/password', [
            'current_password'      => 'password',
            'password'              => 'newpassword',
            'password_confirmation' => 'newpassword',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Password changed successfully.',
            ]);
    }

    /** @test */
    public function it_validates_password_change_request()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->put('/profile/password', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_password', 'password']);
    }

    /** @test */
    public function it_requires_authentication_to_change_password()
    {
        $response = $this->put('/profile/password', [
            'current_password'      => 'password',
            'password'              => 'newpassword',
            'password_confirmation' => 'newpassword',
        ]);

        $response->assertStatus(302)
            ->assertRedirect('/login');
    }
}
