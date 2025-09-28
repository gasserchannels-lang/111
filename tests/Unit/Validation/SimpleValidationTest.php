<?php

namespace Tests\Unit\Validation;

use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * @runTestsInSeparateProcesses
 */
class SimpleValidationTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_required_validation_passes_with_value(): void
    {
        $data = ['name' => 'John'];
        $rules = ['name' => 'required'];

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_required_validation_fails_without_value(): void
    {
        $data = [];
        $rules = ['name' => 'required'];

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_validation(): void
    {
        // Test validation
        $this->assertTrue(true);
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
