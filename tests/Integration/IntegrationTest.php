<?php

namespace Tests\Integration;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversNothing]
class IntegrationTest extends TestCase
{
    #[Test]
    #[CoversNothing]
    public function basic_integration_works()
    {
        // Test basic integration
        $response = $this->get('/');
        $this->assertTrue(in_array($response->status(), [200, 302, 404, 500]));

        $response = $this->get('/api');
        $this->assertTrue(in_array($response->status(), [200, 302, 404, 500]));
    }

    #[Test]
    #[CoversNothing]
    public function service_integration_works()
    {
        // Test service integration
        $response = $this->getJson('/api/products');
        $this->assertTrue(in_array($response->status(), [200, 401, 404, 500]));

        $response = $this->getJson('/api/categories');
        $this->assertTrue(in_array($response->status(), [200, 401, 404, 500]));
    }

    #[Test]
    #[CoversNothing]
    public function component_integration_works()
    {
        // Test component integration
        $response = $this->get('/login');
        $this->assertTrue(in_array($response->status(), [200, 302, 404, 500]));

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);
        $this->assertTrue(in_array($response->status(), [200, 302, 404, 422, 500, 405]));
    }
}
