<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BanUserRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Models\Wishlist;
use App\Services\PasswordPolicyService;
use App\Services\UserBanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

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
            $searchInput = $request->get('search');
            $search = is_string($searchInput) ? $searchInput : '';
            $query->where(function ($q) use ($search): void {
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
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $user->update($request->validated());

        return response()->json([
            'success' => true,
            'data' => $user->fresh(),
            'message' => 'User updated successfully',
        ]);
    }

    /**
     * Change user password.
     */
    public function changePassword(ChangePasswordRequest $request, User $user): JsonResponse
    {
        // Verify current password
        $currentPassword = $request->current_password;
        if (! Hash::check(is_string($currentPassword) ? $currentPassword : '', $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect',
            ], 400);
        }

        // Validate new password against policy
        $newPassword = $request->new_password;
        $passwordValidation = $this->passwordPolicyService->validatePassword(is_string($newPassword) ? $newPassword : '', $user->id);
        if (! $passwordValidation['valid']) {
            return response()->json([
                'success' => false,
                'message' => 'Password does not meet policy requirements',
                'errors' => $passwordValidation['errors'],
            ], 400);
        }

        // Update password
        $user->update([
            'password' => Hash::make(is_string($newPassword) ? $newPassword : ''),
        ]);

        // Save password to history
        $this->passwordPolicyService->savePasswordToHistory($user->id, is_string($newPassword) ? $newPassword : '');

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully',
        ]);
    }

    /**
     * Ban a user.
     */
    public function ban(BanUserRequest $request, User $user): JsonResponse
    {
        if (! $this->userBanService->canBanUser($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot ban this user',
            ], 400);
        }

        $durationInput = $request->get('duration_hours', 24);
        $duration = is_numeric($durationInput) ? (int) $durationInput : 24;
        $expiresAt = now()->addHours($duration);

        $reason = $request->reason;
        $notifyUser = $request->get('notify_user', true);

        $this->userBanService->banUser(
            $user,
            is_string($reason) ? $reason : '',
            null, // description
            $expiresAt
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
        User::findOrFail($userId);

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
