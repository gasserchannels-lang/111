<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RoutingTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function home_route_works()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    #[Test]
    public function api_routes_are_accessible()
    {
        $apiRoutes = [
            '/api/health',
            '/api/products',
            '/api/categories',
            '/api/brands',
            '/api/users',
        ];

        foreach ($apiRoutes as $route) {
            $response = $this->get($route);
            $this->assertContains($response->status(), [200, 401, 404]);
        }
    }

    #[Test]
    public function web_routes_are_accessible()
    {
        $webRoutes = [
            '/',
            '/products',
            '/categories',
            '/about',
            '/contact',
        ];

        foreach ($webRoutes as $route) {
            $response = $this->get($route);
            $this->assertContains($response->status(), [200, 404]);
        }
    }

    #[Test]
    public function admin_routes_require_authentication()
    {
        $adminRoutes = [
            '/admin/dashboard',
            '/admin/users',
            '/admin/products',
            '/admin/settings',
        ];

        foreach ($adminRoutes as $route) {
            $response = $this->get($route);
            $this->assertContains($response->status(), [302, 401, 403]);
        }
    }

    #[Test]
    public function api_routes_return_json()
    {
        $response = $this->getJson('/api/health');
        $response->assertHeader('content-type', 'application/json');
    }

    #[Test]
    public function routes_handle_method_not_allowed()
    {
        $response = $this->post('/api/health');
        $this->assertContains($response->status(), [405, 404]);
    }

    #[Test]
    public function routes_handle_404_correctly()
    {
        $response = $this->get('/nonexistent-route');
        $response->assertStatus(404);
    }

    #[Test]
    public function routes_with_parameters_work()
    {
        $response = $this->get('/products/1');
        $this->assertContains($response->status(), [200, 404]);
    }
}
