<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\User;
use Carbon\Carbon;

interface UserBanService
{
    /**
     * Ban a user.
     *
     * @param  \App\Models\User<\Database\Factories\UserFactory>  $user
     */
    public function banUser(\App\Models\User $user, string $reason, ?string $description = null, ?Carbon $expiresAt = null): bool;

    /**
     * Unban a user.
     *
     * @param  \App\Models\User<\Database\Factories\UserFactory>  $user
     */
    public function unbanUser(\App\Models\User $user, ?string $reason = null): bool;

    /**
     * Check if user is banned.
     *
     * @param  \App\Models\User<\Database\Factories\UserFactory>  $user
     */
    public function isUserBanned(\App\Models\User $user): bool;

    /**
     * Get ban information.
     *
     * @param  \App\Models\User<\Database\Factories\UserFactory>  $user
     * @return array<string, mixed>|null
     */
    public function getBanInfo(\App\Models\User $user): ?array;

    /**
     * Get all banned users.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\User<\Database\Factories\UserFactory>>
     */
    public function getBannedUsers(): \Illuminate\Database\Eloquent\Collection;

    /**
     * Get users with expired bans.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\User<\Database\Factories\UserFactory>>
     */
    public function getUsersWithExpiredBans(): \Illuminate\Database\Eloquent\Collection;

    /**
     * Clean up expired bans.
     */
    public function cleanupExpiredBans(): int;

    /**
     * Get ban statistics.
     *
     * @return array<string, mixed>
     */
    public function getBanStatistics(): array;

    /**
     * Get ban reasons.
     *
     * @return list<string>
     */
    public function getBanReasons(): array;

    /**
     * Check if user can be banned.
     *
     * @param  \App\Models\User<\Database\Factories\UserFactory>  $user
     */
    public function canBanUser(\App\Models\User $user): bool;

    /**
     * Check if user can be unbanned.
     *
     * @param  \App\Models\User<\Database\Factories\UserFactory>  $user
     */
    public function canUnbanUser(\App\Models\User $user): bool;

    /**
     * Get ban history for user.
     *
     * @param  \App\Models\User<\Database\Factories\UserFactory>  $user
     * @return list<array<string, mixed>>
     */
    public function getBanHistory(\App\Models\User $user): array;

    /**
     * Extend ban duration.
     *
     * @param  \App\Models\User<\Database\Factories\UserFactory>  $user
     */
    public function extendBan(\App\Models\User $user, Carbon $newExpiresAt, ?string $reason = null): bool;

    /**
     * Reduce ban duration.
     *
     * @param  \App\Models\User<\Database\Factories\UserFactory>  $user
     */
    public function reduceBan(\App\Models\User $user, Carbon $newExpiresAt, ?string $reason = null): bool;
}
