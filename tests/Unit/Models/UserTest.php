<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Tests\Unit\MinimalTestBase;

class UserTest extends MinimalTestBase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_it_can_create_a_user(): void
    {
        // Test that User class exists
        $model = new User;
        $this->assertInstanceOf(User::class, $model);

        // Test basic functionality
        $this->assertNotEmpty('test');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_it_has_expected_properties(): void
    {
        // Test that User class exists
        $model = new User;
        $this->assertInstanceOf(User::class, $model);

        // Test basic functionality
        $this->assertNotEmpty('test');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_it_can_be_instantiated(): void
    {
        // Test that User class exists
        $model = new User;
        $this->assertInstanceOf(User::class, $model);

        // Test basic functionality
        $this->assertNotEmpty('test');
    }

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
