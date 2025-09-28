<?php

namespace App\Http\Controllers;

use App\Services\BehaviorAnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(private BehaviorAnalysisService $behaviorAnalysisService) {}

    public function userAnalytics(Request $request): JsonResponse
    {
        $user = $request->user();
        $analytics = $this->behaviorAnalysisService->getUserAnalytics($user);

        return response()->json([
            'analytics' => $analytics,
        ]);
    }

    public function siteAnalytics(): JsonResponse
    {
        $analytics = $this->behaviorAnalysisService->getSiteAnalytics();

        return response()->json([
            'analytics' => $analytics,
        ]);
    }

    public function trackBehavior(Request $request): JsonResponse
    {
        $request->validate([
            'action' => 'required|string|max:50',
            'data' => 'nullable|array',
        ]);

        $user = $request->user();
        $this->behaviorAnalysisService->trackUserBehavior(
            $user,
            $request->action,
            $request->data ?? []
        );

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل السلوك بنجاح',
        ]);
    }
}
