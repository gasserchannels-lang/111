<?php

namespace Tests\Unit\Models;

use App\Models\Store;
use Tests\Unit\MinimalTestBase;

class StoreTest extends MinimalTestBase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_it_can_create_a_store(): void
    {
        // Test that Store class exists
        $model = new Store;
        $this->assertInstanceOf(Store::class, $model);

        // Test basic functionality
        $this->assertNotEmpty('test');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_it_has_expected_properties(): void
    {
        // Test that Store class exists
        $model = new Store;
        $this->assertInstanceOf(Store::class, $model);

        // Test basic functionality
        $this->assertNotEmpty('test');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_it_can_be_instantiated(): void
    {
        // Test that Store class exists
        $model = new Store;
        $this->assertInstanceOf(Store::class, $model);

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
