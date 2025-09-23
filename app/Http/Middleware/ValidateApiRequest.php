<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiRequest
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ?string $rules = null): Response
    {
        // Skip validation if no rules provided
        if (! $rules) {
            return $next($request);
        }

        // Get validation rules from config
        $validationRules = $this->getValidationRules($rules);

        if ($validationRules === []) {
            return $next($request);
        }

        // Validate request
        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        return $next($request);
    }

    /**
     * Get validation rules from config.
     *
     * @return array<string, array<int, string>>
     */
    private function getValidationRules(string $rules): array
    {
        $configRules = config("validation.rules.{$rules}", []);

        // If rules are provided as JSON string, decode them
        if (is_string($configRules)) {
            $decoded = json_decode($configRules, true);
            if (is_array($decoded)) {
                // Ensure the decoded array has the correct structure
                /** @var array<string, array<int, string>> $result */
                $result = [];
                foreach ($decoded as $key => $value) {
                    if (is_string($key) && is_array($value)) {
                        /** @var array<int, string> $mappedValue */
                        $mappedValue = array_map(fn ($item): string => is_string($item) ? $item : (is_scalar($item) ? (string) $item : ''), $value);
                        $result[$key] = $mappedValue;
                    }
                }

                return $result;
            }

            return [];
        }

        if (is_array($configRules)) {
            /** @var array<string, array<int, string>> $result */
            $result = [];
            foreach ($configRules as $key => $value) {
                if (is_string($key) && is_array($value)) {
                    /** @var array<int, string> $mappedValue */
                    $mappedValue = array_map(fn ($item): string => is_string($item) ? $item : (is_scalar($item) ? (string) $item : ''), $value);
                    $result[$key] = $mappedValue;
                }
            }

            return $result;
        }

        return [];
    }
}
