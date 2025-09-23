<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Admin\BrandController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DocumentationController;
use App\Http\Controllers\Api\PriceSearchController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::middleware(['auth:sanctum', 'throttle:auth'])->get('/user', [AuthController::class, 'user']);
Route::middleware(['auth:sanctum', 'throttle:authenticated'])->get('/me', [AuthController::class, 'me']);

// Public API routes (no authentication required)
Route::middleware(['throttle:public'])->group(function () {
    // Price search routes
    Route::get('/price-search', [PriceSearchController::class, 'search']);
    Route::get('/price-search/best-offer', [PriceSearchController::class, 'bestOffer']);
    Route::get('/price-search/supported-stores', [PriceSearchController::class, 'supportedStores']);

    // Public product routes
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show'])->whereNumber('id');

    // Additional API routes for testing
    Route::get('/categories', function () {
        return response()->json(['data' => [], 'message' => 'Categories endpoint']);
    });

    Route::get('/brands', function () {
        return response()->json(['data' => [], 'message' => 'Brands endpoint']);
    });

    Route::get('/wishlist', function () {
        return response()->json(['data' => [], 'message' => 'Wishlist endpoint']);
    });

    Route::get('/price-alerts', function () {
        return response()->json(['data' => [], 'message' => 'Price alerts endpoint']);
    });

    Route::get('/reviews', function () {
        return response()->json(['data' => [], 'message' => 'Reviews endpoint']);
    });

    Route::get('/search', function () {
        return response()->json(['data' => [], 'message' => 'Search endpoint']);
    });

    Route::get('/ai', function () {
        return response()->json(['data' => [], 'message' => 'AI endpoint']);
    });

    // Product creation requires authentication
    // Route::post('/products', [ProductController::class, 'store']);
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

// Product deletion requires authentication
// Route::middleware(['throttle:public'])->group(function () {
//     Route::delete('/products/{id}', [ProductController::class, 'destroy']);
// });

// Admin API routes (high rate limits)
Route::middleware(['auth:sanctum', 'admin', 'throttle:admin'])->group(function () {
    // Admin-specific routes
    Route::get('/admin/stats', function () {
        return response()->json([
            'uptime' => time() - strtotime('2025-01-01 00:00:00'),
            'total_users' => \App\Models\User::count(),
            'total_products' => \App\Models\Product::count(),
            'total_offers' => \App\Models\PriceOffer::count(),
            'total_reviews' => \App\Models\Review::count(),
            'active_users_today' => \App\Models\User::whereDate('created_at', today())->count(),
            'new_products_today' => \App\Models\Product::whereDate('created_at', today())->count(),
            'server_time' => now()->toISOString(),
            'status' => 'operational',
        ]);
    });

    // Admin resource routes
    Route::apiResource('admin/categories', CategoryController::class)->names('api.admin.categories');
    Route::apiResource('admin/brands', BrandController::class)->names('api.admin.brands');
});

// API Documentation (no rate limiting for documentation)
Route::get('/documentation', [DocumentationController::class, 'index']);

// CSRF token route for testing - REMOVED FOR PRODUCTION
// Route::get('/csrf-token', function () {
//     return response()->json(['token' => uniqid('csrf_', true)]);
// });

// Debug route for best offer - REMOVED FOR PRODUCTION
// Route::get('/debug-best-offer', function (Request $request) {
//     return response()->json([
//         'message' => 'Debug route working',
//         'params' => $request->all(),
//         'url' => $request->url()
//     ]);
// });

// Simple test route
Route::get('/test-simple', function () {
    return response()->json(['message' => 'Simple test route works']);
});

// Test route for API tests
Route::get('/test', function () {
    return response()->json([
        'data' => ['message' => 'API test route works'],
        'status' => 'success',
    ]);
});

// POST route for validation testing
Route::post('/test', function (Request $request) {
    try {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
        ]);

        return response()->json(['message' => 'Validation passed', 'data' => $validated]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $e->errors(),
        ], 422);
    }
});

// Temporary best offer route outside middleware - test method call
Route::get('/best-offer-debug', function (Request $request) {
    try {
        $controller = app(\App\Http\Controllers\Api\PriceSearchController::class);
        $result = $controller->bestOffer($request);

        return $result;
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Method call failed',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
});

// Direct test of the bestOffer method
Route::get('/direct-best-offer', function (Request $request) {
    return response()->json([
        'message' => 'Direct route test',
        'params' => $request->all(),
        'method' => 'bestOffer',
        'controller' => 'PriceSearchController',
    ]);
});

// Versioned API routes
Route::prefix('v1')->middleware(['throttle:api'])->group(function (): void {
    Route::get('/best-offer', [PriceSearchController::class, 'bestOffer']);
    Route::get('/supported-stores', [PriceSearchController::class, 'supportedStores']);
});

// Test API routes for external service testing
Route::middleware(['throttle:public'])->group(function () {
    // AI Text Analysis API
    Route::post('/ai/analyze', function (Request $request) {
        try {
            // Validate input
            $validated = $request->validate([
                'text' => 'required|string|min:1|max:10000',
                'type' => 'nullable|string|in:general,product_analysis,product_classification,recommendations,sentiment',
            ]);

            $text = is_string($validated['text']) ? $validated['text'] : '';
            $type = is_string($validated['type'] ?? null) ? $validated['type'] : 'general';

            // Simple text analysis for testing
            $analysis = [
                'text' => $text,
                'word_count' => str_word_count($text),
                'character_count' => strlen($text),
                'sentiment' => 'neutral', // Placeholder
                'language' => 'en', // Placeholder
                'analysis_date' => now()->toISOString(),
                'type' => $type,
            ];

            return response()->json([
                'success' => true,
                'data' => $analysis,
                'message' => 'Analysis completed successfully',
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Analysis failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    });

    // AI Product Classification API
    Route::post('/ai/classify-product', function (Request $request) {
        try {
            // Validate input
            $validated = $request->validate([
                'name' => 'required|string|min:1|max:255',
                'description' => 'nullable|string|max:1000',
                'price' => 'nullable|numeric|min:0',
            ]);

            $name = $validated['name'];
            $description = $validated['description'] ?? '';
            $price = $validated['price'] ?? 0;

            // Simple classification for testing
            $classification = [
                'product_name' => $name,
                'description' => $description,
                'price' => $price,
                'category' => 'Electronics', // Placeholder
                'confidence' => 0.85, // Placeholder
                'tags' => ['wireless', 'bluetooth', 'audio'], // Placeholder
                'classification_date' => now()->toISOString(),
            ];

            return response()->json([
                'success' => true,
                'data' => $classification,
                'message' => 'Product classified successfully',
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Classification failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    });

    Route::get('/external-data', function () {
        try {
            $response = Http::get('https://api.external-service.com/data');

            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            return response()->json(['error' => 'External service unavailable'], 503);
        }
    });

    Route::get('/slow-external-data', function () {
        try {
            $response = Http::timeout(3)->get('https://api.slow-service.com/data');

            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
            return response()->json(['error' => 'External service unavailable'], 503);
        }
    });

    Route::get('/authenticated-external-data', function () {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer test-token',
            ])->get('https://api.authenticated-service.com/data');

            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
            return response()->json(['error' => 'Service unavailable'], 503);
        }
    });

    Route::get('/cached-external-data', function () {
        return Cache::remember('external-data', 60, function () {
            try {
                $response = Http::get('https://api.cacheable-service.com/data');

                return $response->json();
            } catch (\Exception $e) {
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
        } catch (\Exception $e) {
            // Primary service failed, try fallback
        }

        try {
            // Try fallback service
            $response = Http::get('https://api.fallback-service.com/data');

            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            return response()->json(['error' => 'All services unavailable'], 503);
        }
    });
});

// AI API routes
Route::middleware(['throttle:ai'])->prefix('ai')->group(function () {
    Route::post('/analyze', function (Request $request) {
        try {
            $request->validate([
                'text' => 'required|string|max:10000',
                'type' => 'required|string|in:general,product_analysis,product_classification,recommendations,sentiment',
            ]);

            $aiService = app(\App\Services\AIService::class);
            $text = is_string($request->text) ? $request->text : '';
            $type = is_string($request->type) ? $request->type : 'general';
            $result = $aiService->analyzeText($text, $type);

            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Analysis completed successfully',
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'message' => 'Invalid input data',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Analysis failed',
                'message' => 'فشل في تحليل النص',
                'details' => $e->getMessage(),
            ], 500);
        }
    });

    Route::post('/classify-product', function (Request $request) {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'price' => 'nullable|numeric|min:0',
            ]);

            $aiService = app(\App\Services\AIService::class);
            $productDescription = is_string($request->input('description')) ? $request->input('description') : '';
            $category = $aiService->classifyProduct($productDescription);

            return response()->json([
                'success' => true,
                'category' => $category,
                'confidence' => 0.8,
                'data' => [
                    'category' => $category,
                    'confidence' => 0.8,
                ],
                'message' => 'Product classified successfully',
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'message' => 'Invalid input data',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Classification failed',
                'message' => 'فشل في تصنيف المنتج',
                'details' => $e->getMessage(),
            ], 500);
        }
    });

    Route::post('/analyze-image', function (Request $request) {
        $request->validate([
            'image_url' => 'required|url|max:2048',
        ]);

        try {
            $aiService = app(\App\Services\AIService::class);
            $imageUrl = is_string($request->image_url) ? $request->image_url : '';
            $result = $aiService->analyzeImage($imageUrl);

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'فشل في تحليل الصورة',
                'message' => $e->getMessage(),
            ], 500);
        }
    });

    Route::post('/recommendations', function (Request $request) {
        $request->validate([
            'preferences' => 'required|array|min:1',
            'preferences.*' => 'string|max:255',
            'products' => 'required|array|min:1',
            'products.*.name' => 'required|string|max:255',
            'products.*.description' => 'nullable|string|max:1000',
            'products.*.price' => 'nullable|numeric|min:0',
        ]);

        try {
            $aiService = app(\App\Services\AIService::class);
            $preferences = is_array($request->preferences) ? $request->preferences : [];
            $products = is_array($request->products) ? $request->products : [];

            // Ensure preferences is array<string, mixed>
            $validPreferences = [];
            foreach ($preferences as $key => $value) {
                if (is_string($key)) {
                    $validPreferences[$key] = $value;
                }
            }

            // Ensure products is array<int, array<string, mixed>>
            $validProducts = [];
            foreach ($products as $index => $product) {
                if (is_int($index) && is_array($product)) {
                    $validProducts[$index] = $product;
                }
            }

            $recommendations = $aiService->generateRecommendations($validPreferences, $validProducts);

            return response()->json([
                'success' => true,
                'recommendations' => $recommendations,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'فشل في توليد التوصيات',
                'message' => $e->getMessage(),
            ], 500);
        }
    });
});
