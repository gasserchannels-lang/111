<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UserBanService
{
    private const BAN_REASONS = [
        'spam' => 'إرسال رسائل مزعجة',
        'abuse' => 'إساءة استخدام',
        'fraud' => 'احتيال',
        'violation' => 'انتهاك شروط الاستخدام',
        'security' => 'مخاطر أمنية',
        'other' => 'أسباب أخرى',
    ];

    /**
     * Ban a user.
     */
    public function banUser(User $user, string $reason, ?string $description = null, ?Carbon $expiresAt = null): bool
    {
        if (! array_key_exists($reason, self::BAN_REASONS)) {
            $reason = 'other';
        }

        $user->is_blocked = true;
        $user->ban_reason = $reason;
        $user->ban_description = $description;
        $user->banned_at = now();
        $user->ban_expires_at = $expiresAt;
        $user->save();

        // Log ban action
        Log::warning('User banned', [
            'user_id' => $user->id,
            'email' => $user->email,
            'reason' => $reason,
            'description' => $description,
            'expires_at' => $expiresAt?->toISOString(),
            'banned_by' => auth()->id(),
        ]);

        return true;
    }

    /**
     * Unban a user.
     */
    public function unbanUser(User $user, ?string $reason = null): bool
    {
        $user->is_blocked = false;
        $user->ban_reason = null;
        $user->ban_description = null;
        $user->banned_at = null;
        $user->ban_expires_at = null;
        $user->save();

        // Log unban action
        Log::info('User unbanned', [
            'user_id' => $user->id,
            'email' => $user->email,
            'reason' => $reason,
            'unbanned_by' => auth()->id(),
        ]);

        return true;
    }

    /**
     * Check if user is banned.
     */
    public function isUserBanned(User $user): bool
    {
        if (! $user->is_blocked) {
            return false;
        }

        // Check if ban has expired
        if ($user->ban_expires_at && $user->ban_expires_at->isPast()) {
            $this->unbanUser($user, 'Ban expired');

            return false;
        }

        return true;
    }

    /**
     * Get ban information.
     */
    public function getBanInfo(User $user): ?array
    {
        if (! $user->is_blocked) {
            return null;
        }

        return [
            'reason' => $user->ban_reason,
            'description' => $user->ban_description,
            'banned_at' => $user->banned_at?->toISOString(),
            'expires_at' => $user->ban_expires_at?->toISOString(),
            'is_permanent' => $user->ban_expires_at === null,
            'reason_text' => self::BAN_REASONS[$user->ban_reason] ?? 'غير محدد',
        ];
    }

    /**
     * Get all banned users.
     */
    public function getBannedUsers(): \Illuminate\Database\Eloquent\Collection
    {
        return User::where('is_blocked', true)
            ->where(function ($query) {
                $query->whereNull('ban_expires_at')
                    ->orWhere('ban_expires_at', '>', now());
            })
            ->get();
    }

    /**
     * Get users with expired bans.
     */
    public function getUsersWithExpiredBans(): \Illuminate\Database\Eloquent\Collection
    {
        return User::where('is_blocked', true)
            ->where('ban_expires_at', '<=', now())
            ->get();
    }

    /**
     * Clean up expired bans.
     */
    public function cleanupExpiredBans(): int
    {
        $expiredBans = $this->getUsersWithExpiredBans();
        $count = 0;

        foreach ($expiredBans as $user) {
            $this->unbanUser($user, 'Ban expired');
            $count++;
        }

        Log::info('Expired bans cleaned up', [
            'count' => $count,
        ]);

        return $count;
    }

    /**
     * Get ban statistics.
     */
    public function getBanStatistics(): array
    {
        $totalBanned = User::where('is_blocked', true)->count();
        $permanentBans = User::where('is_blocked', true)
            ->whereNull('ban_expires_at')
            ->count();
        $temporaryBans = User::where('is_blocked', true)
            ->whereNotNull('ban_expires_at')
            ->count();
        $expiredBans = User::where('is_blocked', true)
            ->where('ban_expires_at', '<=', now())
            ->count();

        return [
            'total_banned' => $totalBanned,
            'permanent_bans' => $permanentBans,
            'temporary_bans' => $temporaryBans,
            'expired_bans' => $expiredBans,
            'ban_reasons' => self::BAN_REASONS,
        ];
    }

    /**
     * Get ban reasons.
     */
    public function getBanReasons(): array
    {
        return self::BAN_REASONS;
    }

    /**
     * Check if user can be banned.
     */
    public function canBanUser(User $user): bool
    {
        // Cannot ban admin users
        if ($user->isAdmin()) {
            return false;
        }

        // Cannot ban already banned users
        if ($user->is_blocked) {
            return false;
        }

        return true;
    }

    /**
     * Check if user can be unbanned.
     */
    public function canUnbanUser(User $user): bool
    {
        // Can only unban banned users
        return $user->is_blocked;
    }

    /**
     * Get ban history for user.
     */
    public function getBanHistory(User $user): array
    {
        // This would require a ban_history table
        // For now, return current ban info
        $banInfo = $this->getBanInfo($user);

        return $banInfo ? [$banInfo] : [];
    }

    /**
     * Extend ban duration.
     */
    public function extendBan(User $user, Carbon $newExpiresAt, ?string $reason = null): bool
    {
        if (! $user->is_blocked) {
            return false;
        }

        $user->ban_expires_at = $newExpiresAt;
        $user->save();

        Log::info('Ban extended', [
            'user_id' => $user->id,
            'email' => $user->email,
            'new_expires_at' => $newExpiresAt->toISOString(),
            'reason' => $reason,
            'extended_by' => auth()->id(),
        ]);

        return true;
    }

    /**
     * Reduce ban duration.
     */
    public function reduceBan(User $user, Carbon $newExpiresAt, ?string $reason = null): bool
    {
        if (! $user->is_blocked) {
            return false;
        }

        $user->ban_expires_at = $newExpiresAt;
        $user->save();

        Log::info('Ban reduced', [
            'user_id' => $user->id,
            'email' => $user->email,
            'new_expires_at' => $newExpiresAt->toISOString(),
            'reason' => $reason,
            'reduced_by' => auth()->id(),
        ]);

        return true;
    }
}
