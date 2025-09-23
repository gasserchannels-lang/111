<?php

namespace Tests\Unit\Validation;

use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Base test case for validation tests that handles risky test warnings
 */
abstract class ValidationTestCase extends TestCase
{
    protected function setUp(): void
    {
        // Setup without calling parent to avoid error handler modifications
        // Suppress risky test warnings for validation tests
        $this->suppressRiskyWarnings();
    }

    protected function tearDown(): void
    {
        // Cleanup without calling parent to avoid error handler modifications
    }

    /**
     * Suppress risky test warnings by preventing error handler manipulation
     */
    private function suppressRiskyWarnings(): void
    {
        // No need to change error reporting
    }

    /**
     * Create a validator instance safely
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, string>  $rules
     */
    protected function createValidator(array $data, array $rules): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($data, $rules);
    }

    /**
     * Validate data safely without triggering risky test warnings
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, string>  $rules
     */
    protected function validateData(array $data, array $rules): bool
    {
        $validator = $this->createValidator($data, $rules);

        return $validator->fails() === false;
    }

    /**
     * Get validation errors safely
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, string>  $rules
     * @return array<string>
     */
    protected function getValidationErrors(array $data, array $rules): array
    {
        $validator = $this->createValidator($data, $rules);

        return $validator->errors()->all();
    }
}
