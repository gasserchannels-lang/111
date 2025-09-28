<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AIControlPanelController extends Controller
{
    public function __construct(
        private AIService $aiService
    ) {}

    public function index(): View
    {
        return view('admin.ai-control-panel');
    }

    public function analyzeText(Request $request): JsonResponse
    {
        $request->validate([
            'text' => 'required|string|max:1000',
            'type' => 'string|in:sentiment,classification,keywords',
        ]);

        $text = $request->input('text');
        $type = $request->input('type', 'sentiment');

        assert(is_string($text));
        assert(is_string($type));

        $result = $this->aiService->analyzeText($text, $type);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    public function classifyProduct(Request $request): JsonResponse
    {
        $request->validate([
            'description' => 'required|string|max:500',
        ]);

        $description = $request->input('description');
        assert(is_string($description));

        $result = $this->aiService->classifyProduct($description);

        return response()->json([
            'success' => true,
            'category' => $result,
        ]);
    }

    public function generateRecommendations(Request $request): JsonResponse
    {
        $request->validate([
            'preferences' => 'required|array',
            'products' => 'array',
        ]);

        $preferences = $request->input('preferences', []);
        $products = $request->input('products', []);

        assert(is_array($preferences));
        assert(is_array($products));

        /** @var array<string, mixed> $preferences */
        /** @var array<int, array<string, mixed>> $products */
        $result = $this->aiService->generateRecommendations($preferences, $products);

        return response()->json([
            'success' => true,
            'recommendations' => $result,
        ]);
    }

    public function analyzeImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|file|image|max:10240', // 10MB max
        ]);

        $imagePath = $request->file('image')->store('temp');
        $result = $this->aiService->analyzeImage(storage_path('app/'.$imagePath));

        // Clean up temp file
        unlink(storage_path('app/'.$imagePath));

        return response()->json([
            'success' => true,
            'analysis' => $result,
        ]);
    }

    public function getStatus(): JsonResponse
    {
        // Test AI service with a simple request
        $testResult = $this->aiService->analyzeText('test');

        return response()->json([
            'success' => true,
            'status' => isset($testResult['error']) ? 'error' : 'healthy',
            'last_check' => now()->toISOString(),
        ]);
    }
}
