<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\User;

interface EmailVerificationServiceInterface
{
    /**
     * Send verification email
     */
    public function sendVerificationEmail(User $user): bool;

    /**
     * Verify email with token
     */
    public function verifyEmail(string $email, string $token): bool;

    /**
     * Resend verification email
     */
    public function resendVerificationEmail(string $email): bool;

    /**
     * Check if verification token exists
     */
    public function hasVerificationToken(string $email): bool;

    /**
     * Get verification token info
     */
    public function getVerificationTokenInfo(string $email): ?array;

    /**
     * Clean up expired tokens
     */
    public function cleanupExpiredTokens(): int;

    /**
     * Get email verification statistics
     */
    public function getStatistics(): array;
}
