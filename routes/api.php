<?php

declare(strict_types=1);

use App\Http\Controllers\Api\PriceSearchController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\DocumentationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// User authentication route
Route::middleware(['auth:sanctum', 'throttle:auth'])->get('/user', fn (Request $request) => $request->user());

// Public API routes (no authentication required)
Route::middleware(['throttle:public'])->group(function () {
    // Price search routes
    Route::get('/price-search', [PriceSearchController::class, 'search']);
    Route::get('/price-search/best-offer', [PriceSearchController::class, 'bestOffer']);
    Route::get('/price-search/supported-stores', [PriceSearchController::class, 'supportedStores']);
    
    // Public product routes
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
});

// Authenticated API routes
Route::middleware(['auth:sanctum', 'throttle:authenticated'])->group(function () {
    // Protected product routes
    Route::post('/products', [ProductController::class, 'store']);
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
});

// API Documentation (no rate limiting for documentation)
Route::get('/documentation', [DocumentationController::class, 'index']);

// Versioned API routes
Route::prefix('v1')->middleware(['throttle:api'])->group(function (): void {
    Route::get('/best-offer', [PriceSearchController::class, 'bestOffer']);
    Route::get('/supported-stores', [PriceSearchController::class, 'supportedStores']);
});
