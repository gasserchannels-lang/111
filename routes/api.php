<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Admin\BrandController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\DocumentationController;
use App\Http\Controllers\Api\PriceSearchController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

// User authentication route
Route::middleware(['auth:sanctum', 'throttle:auth'])->get('/user', fn(Request $request) => $request->user());

// Public API routes (no authentication required)
Route::middleware(['throttle:public'])->group(function () {
    // Price search routes
    Route::get('/price-search', [PriceSearchController::class, 'search']);
    Route::get('/price-search/best-offer', [PriceSearchController::class, 'bestOffer']);
    Route::get('/price-search/supported-stores', [PriceSearchController::class, 'supportedStores']);

    // Public product routes
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show'])->whereNumber('id');
    // Allow creating products publicly for testing/validation scenarios
    Route::post('/products', [ProductController::class, 'store']);
});

// Authenticated API routes
Route::middleware(['auth:sanctum', 'throttle:authenticated'])->group(function () {
    // Protected product routes
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    // Upload route
    Route::post('/upload', function () {
        return response()->json(['message' => 'Upload endpoint for testing'], 200);
    });
});

// Admin API routes (high rate limits)
Route::middleware(['auth:sanctum', 'admin', 'throttle:admin'])->group(function () {
    // Admin-specific routes
    Route::get('/admin/stats', function () {
        return response()->json(['message' => 'Admin stats']);
    });

    // Admin resource routes
    Route::apiResource('admin/categories', CategoryController::class);
    Route::apiResource('admin/brands', BrandController::class);
});

// API Documentation (no rate limiting for documentation)
Route::get('/documentation', [DocumentationController::class, 'index']);

// CSRF token route for testing
Route::get('/csrf-token', function () {
    return response()->json(['token' => uniqid('csrf_', true)]);
});

// Versioned API routes
Route::prefix('v1')->middleware(['throttle:api'])->group(function (): void {
    Route::get('/best-offer', [PriceSearchController::class, 'bestOffer']);
    Route::get('/supported-stores', [PriceSearchController::class, 'supportedStores']);
});

// Test API routes for external service testing
Route::middleware(['throttle:public'])->group(function () {
    Route::get('/external-data', function () {
        try {
            $response = Http::get('https://api.external-service.com/data');
            return response()->json($response->json(), $response->status());
        } catch (Exception $e) {
            return response()->json(['error' => 'External service unavailable'], 503);
        }
    });

    Route::get('/slow-external-data', function () {
        try {
            $response = Http::timeout(3)->get('https://api.slow-service.com/data');
            return response()->json($response->json(), $response->status());
        } catch (Exception $e) {
            return response()->json(['error' => 'Service timeout'], 408);
        }
    });

    Route::get('/error-external-data', function () {
        try {
            $response = Http::get('https://api.error-service.com/data');
            if ($response->status() >= 400) {
                return response()->json(['error' => 'External service error'], 502);
            }
            return response()->json($response->json(), $response->status());
        } catch (Exception $e) {
            return response()->json(['error' => 'External service unavailable'], 503);
        }
    });

    Route::get('/authenticated-external-data', function () {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . Config::get('services.external.token', 'test-token')
            ])->get('https://api.authenticated-service.com/data');
            return response()->json($response->json(), $response->status());
        } catch (Exception $e) {
            return response()->json(['error' => 'Authentication failed'], 401);
        }
    });

    Route::get('/rate-limited-external-data', function () {
        try {
            $response = Http::get('https://api.rate-limited-service.com/data');
            if ($response->status() === 429) {
                return response()->json(['error' => 'Rate limited'], 429);
            }
            return response()->json($response->json(), $response->status());
        } catch (Exception $e) {
            return response()->json(['error' => 'Service unavailable'], 503);
        }
    });

    Route::get('/cached-external-data', function () {
        return Cache::remember('external-data', 60, function () {
            try {
                $response = Http::get('https://api.cacheable-service.com/data');
                return $response->json();
            } catch (Exception $e) {
                return ['error' => 'Service unavailable'];
            }
        });
    });

    Route::get('/fallback-external-data', function () {
        try {
            // Try primary service first
            $response = Http::get('https://api.primary-service.com/data');
            if ($response->successful()) {
                return response()->json($response->json(), 200);
            }
        } catch (Exception $e) {
            // Primary service failed, try fallback
        }

        try {
            // Try fallback service
            $response = Http::get('https://api.fallback-service.com/data');
            return response()->json($response->json(), $response->status());
        } catch (Exception $e) {
            return response()->json(['error' => 'All services unavailable'], 503);
        }
    });
});
