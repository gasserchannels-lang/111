<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wishlist;
use App\Services\PasswordPolicyService;
use App\Services\UserBanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct(
        private readonly UserBanService $userBanService,
        private readonly PasswordPolicyService $passwordPolicyService
    ) {}

    /**
     * Display a listing of users.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::with(['wishlists', 'priceAlerts', 'reviews']);

        // Search by name or email
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role (if role column exists)
        if ($request->has('role')) {
            $query->whereRaw('role = ?', [$request->get('role')]);
        }

        // Filter by status
        if ($request->has('status')) {
            $status = $request->get('status');
            if ($status === 'active') {
                $query->where('is_blocked', false);
            } elseif ($status === 'blocked') {
                $query->where('is_blocked', true);
            }
        }

        $users = $query->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $users,
            'message' => 'Users retrieved successfully',
        ]);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): JsonResponse
    {
        $user->load(['wishlists.product', 'priceAlerts.product', 'reviews.product']);

        return response()->json([
            'success' => true,
            'data' => $user,
            'message' => 'User retrieved successfully',
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => 'sometimes|in:user,admin,moderator',
            'is_blocked' => 'sometimes|boolean',
            'ban_expires_at' => 'sometimes|nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user->update($request->only(['name', 'email', 'role', 'is_blocked', 'ban_expires_at']));

        return response()->json([
            'success' => true,
            'data' => $user->fresh(),
            'message' => 'User updated successfully',
        ]);
    }

    /**
     * Change user password.
     */
    public function changePassword(Request $request, User $user): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8',
            'new_password_confirmation' => 'required|string|same:new_password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Verify current password
        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect',
            ], 400);
        }

        // Validate new password against policy
        $passwordValidation = $this->passwordPolicyService->validatePassword($request->new_password, $user->id);
        if (! $passwordValidation['valid']) {
            return response()->json([
                'success' => false,
                'message' => 'Password does not meet policy requirements',
                'errors' => $passwordValidation['errors'],
            ], 400);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        // Save password to history
        $this->passwordPolicyService->savePasswordToHistory($user->id, $request->new_password);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully',
        ]);
    }

    /**
     * Ban a user.
     */
    public function ban(Request $request, User $user): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
            'duration_hours' => 'sometimes|integer|min:1|max:8760', // Max 1 year
            'notify_user' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        if (! $this->userBanService->canBanUser($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot ban this user',
            ], 400);
        }

        $duration = $request->get('duration_hours', 24);
        $expiresAt = now()->addHours($duration);

        $this->userBanService->banUser(
            $user,
            $request->reason,
            $expiresAt,
            $request->get('notify_user', true)
        );

        return response()->json([
            'success' => true,
            'message' => 'User banned successfully',
            'data' => [
                'user_id' => $user->id,
                'banned_until' => $expiresAt,
                'reason' => $request->reason,
            ],
        ]);
    }

    /**
     * Unban a user.
     */
    public function unban(User $user): JsonResponse
    {
        if (! $user->isBanned()) {
            return response()->json([
                'success' => false,
                'message' => 'User is not currently banned',
            ], 400);
        }

        $this->userBanService->unbanUser($user);

        return response()->json([
            'success' => true,
            'message' => 'User unbanned successfully',
        ]);
    }

    /**
     * Get user statistics.
     */
    public function statistics(User $user): JsonResponse
    {
        $stats = [
            'wishlist_count' => $user->wishlists()->count(),
            'price_alerts_count' => $user->priceAlerts()->count(),
            'reviews_count' => $user->reviews()->count(),
            'total_products_viewed' => 0, // Placeholder - would need audit logs implementation
            'last_login' => $user->last_login_at ?? null,
            'created_at' => $user->created_at,
            'is_banned' => $user->isBanned(),
            'ban_expires_at' => $user->ban_expires_at,
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'User statistics retrieved successfully',
        ]);
    }

    /**
     * Get user activity log.
     */
    public function activity(User $user, Request $request): JsonResponse
    {
        // Placeholder implementation - would need audit logs implementation
        $activities = collect([]);

        return response()->json([
            'success' => true,
            'data' => $activities,
            'message' => 'User activity retrieved successfully',
        ]);
    }

    /**
     * Delete a user.
     */
    public function destroy(User $user): JsonResponse
    {
        // Prevent deletion of admin users
        if ($user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete admin users',
            ], 400);
        }

        // Soft delete user and related data
        $user->wishlists()->delete();
        $user->priceAlerts()->delete();
        $user->reviews()->delete();
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully',
        ]);
    }

    /**
     * Restore a soft-deleted user.
     */
    public function restore(int $userId): JsonResponse
    {
        $user = User::findOrFail($userId);

        // Note: This would require soft deletes to be implemented in User model
        return response()->json([
            'success' => false,
            'message' => 'Soft deletes not implemented for users',
        ], 501);
    }

    /**
     * Get banned users.
     */
    public function banned(): JsonResponse
    {
        $bannedUsers = $this->userBanService->getBannedUsers();

        return response()->json([
            'success' => true,
            'data' => $bannedUsers,
            'message' => 'Banned users retrieved successfully',
        ]);
    }

    /**
     * Get user's wishlist.
     */
    public function wishlist(User $user): JsonResponse
    {
        $wishlist = $user->wishlists()->with('product.category', 'product.brand')->get();

        return response()->json([
            'success' => true,
            'data' => $wishlist,
            'message' => 'User wishlist retrieved successfully',
        ]);
    }

    /**
     * Get user's price alerts.
     */
    public function priceAlerts(User $user): JsonResponse
    {
        $priceAlerts = $user->priceAlerts()->with('product.category', 'product.brand')->get();

        return response()->json([
            'success' => true,
            'data' => $priceAlerts,
            'message' => 'User price alerts retrieved successfully',
        ]);
    }

    /**
     * Get user's reviews.
     */
    public function reviews(User $user): JsonResponse
    {
        $reviews = $user->reviews()->with('product.category', 'product.brand')->get();

        return response()->json([
            'success' => true,
            'data' => $reviews,
            'message' => 'User reviews retrieved successfully',
        ]);
    }
}
