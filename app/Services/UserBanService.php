<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

final class UserBanService
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
     *
     * @param  User<\Database\Factories\UserFactory>  $user
     */
    public function banUser(User $user, string $reason, ?string $description = null, ?Carbon $expiresAt = null): bool
    {
        if (! array_key_exists($reason, self::BAN_REASONS)) {
            $reason = 'other';
        }

        $user->is_blocked = true;
        $user->ban_reason = $reason;
        $user->ban_description = $description;
        $user->banned_at = now()->toDateTimeString();
        $user->ban_expires_at = $expiresAt?->toDateTimeString();
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
     *
     * @param  User<\Database\Factories\UserFactory>  $user
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
     *
     * @param  User<\Database\Factories\UserFactory>  $user
     */
    public function isUserBanned(User $user): bool
    {
        if (! $user->isBanned()) {
            return false;
        }

        // Check if ban has expired
        if ($user->isBanExpired()) {
            $this->unbanUser($user, 'Ban expired');

            return false;
        }

        return true;
    }

    /**
     * Get ban information.
     *
     * @param  User<\Database\Factories\UserFactory>  $user
     * @return array<string, mixed>|null
     */
    public function getBanInfo(User $user): ?array
    {
        if (! $user->isBanned()) {
            return null;
        }

        return [
            'is_banned' => $user->isBanned(),
            'reason' => $user->ban_reason,
            'description' => $user->ban_description,
            'banned_at' => $user->banned_at ? Carbon::parse($user->banned_at)->toISOString() : null,
            'expires_at' => $user->ban_expires_at ? Carbon::parse($user->ban_expires_at)->toISOString() : null,
            'is_permanent' => $user->ban_expires_at === null,
            'reason_text' => self::BAN_REASONS[$user->ban_reason] ?? 'غير محدد',
        ];
    }

    /**
     * Get all banned users.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    public function getBannedUsers(): \Illuminate\Database\Eloquent\Collection
    {
        return User::where('is_blocked', true)
            ->where(function ($query): void {
                $query->whereNull('ban_expires_at')
                    ->orWhere('ban_expires_at', '>', now());
            })
            ->get();
    }

    /**
     * Get users with expired bans.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
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
     *
     * @return array<string, mixed>
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
     *
     * @return array<string, string>
     */
    public function getBanReasons(): array
    {
        return self::BAN_REASONS;
    }

    /**
     * Check if user can be banned.
     *
     * @param  User<\Database\Factories\UserFactory>  $user
     */
    public function canBanUser(User $user): bool
    {
        // Cannot ban admin users
        if ($user->isAdmin()) {
            return false;
        }

        // Cannot ban already banned users
        return ! $user->isBanned();
    }

    /**
     * Check if user can be unbanned.
     *
     * @param  User<\Database\Factories\UserFactory>  $user
     */
    public function canUnbanUser(User $user): bool
    {
        // Can only unban banned users
        return $user->is_blocked;
    }

    /**
     * Get ban history for user.
     *
     * @param  User<\Database\Factories\UserFactory>  $user
     * @return array<int, array<string, mixed>>
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
     *
     * @param  User<\Database\Factories\UserFactory>  $user
     */
    public function extendBan(User $user, Carbon $newExpiresAt, ?string $reason = null): bool
    {
        if (! $user->isBanned()) {
            return false;
        }

        $user->ban_expires_at = $newExpiresAt->toDateTimeString();
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
     *
     * @param  User<\Database\Factories\UserFactory>  $user
     */
    public function reduceBan(User $user, Carbon $newExpiresAt, ?string $reason = null): bool
    {
        if (! $user->isBanned()) {
            return false;
        }

        $user->ban_expires_at = $newExpiresAt->toDateTimeString();
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
