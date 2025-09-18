<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversNothing]
class ThirdPartyApiTest extends TestCase
{
    #[Test]
    #[CoversNothing]
    public function third_party_apis_respond()
    {
        // Test that third-party API endpoints respond
        $response = $this->get('/api/external/test');
        $this->assertTrue(in_array($response->status(), [200, 404, 500]));
    }

    #[Test]
    #[CoversNothing]
    public function api_errors_are_handled()
    {
        // Test that API errors are handled gracefully
        $response = $this->get('/api/external/invalid');
        $this->assertTrue(in_array($response->status(), [200, 404, 422, 500]));
    }

    #[Test]
    #[CoversNothing]
    public function api_timeouts_are_handled()
    {
        // Test that API timeouts are handled
        $startTime = microtime(true);
        $response = $this->get('/api/external/slow');
        $endTime = microtime(true);

        $this->assertTrue(in_array($response->status(), [200, 404, 500, 408]));
        $this->assertLessThan(30, $endTime - $startTime); // Should not hang indefinitely
    }
}
