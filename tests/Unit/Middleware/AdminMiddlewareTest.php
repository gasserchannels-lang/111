<?php

declare(strict_types=1);

namespace Tests\Unit\Middleware;

use App\Http\Middleware\AdminMiddleware;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AdminMiddlewareTest extends TestCase
{
    /**
     * @test
     */
    public function it_allows_admin_users()
    {
        $user = User::factory()->create(['is_admin' => true]);
        $this->actingAs($user);

        $middleware = new AdminMiddleware();
        $request = Request::create('/admin', 'GET');

        $response = $middleware->handle($request, function ($req) {
            return new Response('OK', 200);
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_blocks_non_admin_users()
    {
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user);

        $middleware = new AdminMiddleware();
        $request = Request::create('/admin', 'GET');
        $request->headers->set('Accept', 'application/json');

        $response = $middleware->handle($request, function ($req) {
            return new Response('OK', 200);
        });

        $this->assertEquals(403, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_redirects_guests()
    {
        Auth::logout();

        $middleware = new AdminMiddleware();
        $request = Request::create('/admin', 'GET');

        $response = $middleware->handle($request, function ($req) {
            return new Response('OK', 200);
        });

        $this->assertEquals(302, $response->getStatusCode());
    }
}
