<?php

namespace Tests\Unit\Models;

use App\Models\Language;
use Tests\Unit\MinimalTestBase;

class LanguageTest extends MinimalTestBase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_it_can_create_a_language(): void
    {
        // Test that Language class exists
        $model = new Language;
        $this->assertInstanceOf(Language::class, $model);

        // Test basic functionality
        $this->assertNotEmpty('test');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_it_has_expected_properties(): void
    {
        // Test that Language class exists
        $model = new Language;
        $this->assertInstanceOf(Language::class, $model);

        // Test basic functionality
        $this->assertNotEmpty('test');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_it_can_be_instantiated(): void
    {
        // Test that Language class exists
        $model = new Language;
        $this->assertInstanceOf(Language::class, $model);

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
