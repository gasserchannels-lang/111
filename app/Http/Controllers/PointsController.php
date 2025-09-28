<?php

namespace App\Http\Controllers;

use App\Models\Reward;
use App\Services\PointsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PointsController extends Controller
{
    public function __construct(private PointsService $pointsService) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'available_points' => $this->pointsService->getAvailablePoints($user->id),
            'points_history' => $this->pointsService->getPointsHistory($user->id),
        ]);
    }

    public function redeem(Request $request): JsonResponse
    {
        $request->validate([
            'points' => 'required|integer|min:1',
            'description' => 'nullable|string|max:255',
        ]);

        $user = $request->user();
        $success = $this->pointsService->redeemPoints(
            $user,
            $request->points,
            $request->description
        );

        return response()->json([
            'success' => $success,
            'message' => $success
                ? 'تم استرداد النقاط بنجاح'
                : 'النقاط المتاحة غير كافية',
        ]);
    }

    public function getRewards(Request $request): JsonResponse
    {
        $user = $request->user();
        $rewards = $this->pointsService->getAvailableRewards($user->id);

        return response()->json([
            'rewards' => $rewards,
        ]);
    }

    public function redeemReward(Request $request, Reward $reward): JsonResponse
    {
        $user = $request->user();
        $success = $this->pointsService->redeemReward($user, $reward->id);

        return response()->json([
            'success' => $success,
            'message' => $success
                ? 'تم استرداد المكافأة بنجاح'
                : 'النقاط المتاحة غير كافية أو المكافأة غير متاحة',
        ]);
    }
}
