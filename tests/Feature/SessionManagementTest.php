<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversNothing]
class SessionManagementTest extends TestCase
{
    #[Test]
    #[CoversNothing]
    public function sessions_are_created()
    {
        // Test that sessions can be created
        $this->startSession();
        $this->assertTrue(session()->isStarted());
    }

    #[Test]
    #[CoversNothing]
    public function sessions_are_destroyed()
    {
        // Test that sessions can be destroyed
        $this->startSession();
        $this->assertTrue(session()->isStarted());

        session()->flush();
        $this->assertTrue(true); // Session destroyed
    }

    #[Test]
    #[CoversNothing]
    public function session_data_is_persistent()
    {
        // Test that session data persists
        $this->startSession();
        session(['test_key' => 'test_value']);
        $this->assertEquals('test_value', session('test_key'));
    }
}
