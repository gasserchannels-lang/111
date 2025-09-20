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
            return is_array($decoded) ? $decoded : [];
        }

        return is_array($configRules) ? $configRules : [];
    }
}
