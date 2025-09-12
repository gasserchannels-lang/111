<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionManagementTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function session_starts_correctly()
    {
        $response = $this->get('/');
        $this->assertTrue(session()->isStarted());
    }

    /** @test */
    public function can_store_data_in_session()
    {
        $this->get('/');
        session(['test_key' => 'test_value']);
        $this->assertEquals('test_value', session('test_key'));
    }

    /** @test */
    public function session_persists_across_requests()
    {
        $this->get('/');
        session(['user_id' => 123]);

        $response = $this->get('/dashboard');
        $this->assertEquals(123, session('user_id'));
    }

    /** @test */
    public function session_expires_after_timeout()
    {
        config(['session.lifetime' => 1]); // 1 minute

        $this->get('/');
        session(['test_data' => 'persistent']);

        // Simulate time passing
        $this->travel(2)->minutes();

        $response = $this->get('/');
        $this->assertNull(session('test_data'));
    }

    /** @test */
    public function can_flash_data_to_session()
    {
        $this->get('/');
        session()->flash('success', 'Operation completed');

        $this->assertEquals('Operation completed', session('success'));

        // Flash data should be available for one request
        $this->get('/');
        $this->assertNull(session('success'));
    }

    /** @test */
    public function session_regenerates_on_login()
    {
        $user = User::factory()->create();

        $oldSessionId = session()->getId();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $newSessionId = session()->getId();
        $this->assertNotEquals($oldSessionId, $newSessionId);
    }
}
