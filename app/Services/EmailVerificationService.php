<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmailVerificationService
{
    private const VERIFICATION_EXPIRY = 24; // hours

    private const MAX_ATTEMPTS = 3;

    private const CACHE_PREFIX = 'email_verification:';

    /**
     * Send verification email.
     */
    public function sendVerificationEmail(User $user): bool
    {
        if ($user->hasVerifiedEmail()) {
            return false;
        }

        // Generate verification token
        $token = $this->generateVerificationToken();

        // Store token in cache
        $this->storeVerificationToken($user->email, $token);

        // Send email
        try {
            Mail::send('emails.email-verification', [
                'user' => $user,
                'token' => $token,
                'expiry' => self::VERIFICATION_EXPIRY,
            ], function ($message) use ($user) {
                $message->to($user->email, $user->name)
                    ->subject('تأكيد البريد الإلكتروني - كوبرا');
            });

            Log::info('Email verification sent', [
                'email' => $user->email,
                'user_id' => $user->id,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send email verification', [
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Verify email with token.
     */
    public function verifyEmail(string $email, string $token): bool
    {
        // Validate token
        if (!$this->validateVerificationToken($email, $token)) {
            Log::warning('Invalid email verification token', [
                'email' => $email,
                'token' => $token,
                'ip' => request()->ip(),
            ]);

            return false;
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return false;
        }

        // Mark email as verified
        $user->email_verified_at = now();
        $user->save();

        // Clear verification token
        $this->clearVerificationToken($email);

        // Log email verification
        Log::info('Email verified successfully', [
            'email' => $email,
            'user_id' => $user->id,
            'ip' => request()->ip(),
        ]);

        return true;
    }

    /**
     * Resend verification email.
     */
    public function resendVerificationEmail(string $email): bool
    {
        $user = User::where('email', $email)->first();

        if (!$user || $user->hasVerifiedEmail()) {
            return false;
        }

        // Check if user has exceeded resend limit
        if ($this->hasExceededResendLimit($email)) {
            Log::warning('Email verification resend limit exceeded', [
                'email' => $email,
                'ip' => request()->ip(),
            ]);

            return false;
        }

        // Increment resend count
        $this->incrementResendCount($email);

        // Send verification email
        return $this->sendVerificationEmail($user);
    }

    /**
     * Generate verification token.
     */
    private function generateVerificationToken(): string
    {
        return Str::random(64);
    }

    /**
     * Store verification token.
     */
    private function storeVerificationToken(string $email, string $token): void
    {
        $key = self::CACHE_PREFIX . md5($email);

        $data = [
            'token' => $token,
            'created_at' => now()->toISOString(),
            'attempts' => 0,
            'resend_count' => 0,
        ];

        Cache::put($key, $data, now()->addHours(self::VERIFICATION_EXPIRY));
    }

    /**
     * Validate verification token.
     */
    private function validateVerificationToken(string $email, string $token): bool
    {
        $key = self::CACHE_PREFIX . md5($email);
        $data = Cache::get($key);

        if (!$data) {
            return false;
        }

        // Check if token matches
        if ($data['token'] !== $token) {
            // Increment attempts
            $data['attempts']++;
            Cache::put($key, $data, now()->addHours(self::VERIFICATION_EXPIRY));

            // Block if too many attempts
            if ($data['attempts'] >= self::MAX_ATTEMPTS) {
                Cache::forget($key);
            }

            return false;
        }

        // Check if token is expired
        $createdAt = Carbon::parse($data['created_at']);
        if ($createdAt->addHours(self::VERIFICATION_EXPIRY)->isPast()) {
            Cache::forget($key);

            return false;
        }

        return true;
    }

    /**
     * Clear verification token.
     */
    private function clearVerificationToken(string $email): void
    {
        $key = self::CACHE_PREFIX . md5($email);
        Cache::forget($key);
    }

    /**
     * Check if user has exceeded resend limit.
     */
    private function hasExceededResendLimit(string $email): bool
    {
        $key = self::CACHE_PREFIX . md5($email);
        $data = Cache::get($key);

        if (!$data) {
            return false;
        }

        return $data['resend_count'] >= self::MAX_ATTEMPTS;
    }

    /**
     * Increment resend count.
     */
    private function incrementResendCount(string $email): void
    {
        $key = self::CACHE_PREFIX . md5($email);
        $data = Cache::get($key);

        if ($data) {
            $data['resend_count']++;
            Cache::put($key, $data, now()->addHours(self::VERIFICATION_EXPIRY));
        }
    }

    /**
     * Check if verification token exists.
     */
    public function hasVerificationToken(string $email): bool
    {
        $key = self::CACHE_PREFIX . md5($email);

        return Cache::has($key);
    }

    /**
     * Get verification token info.
     */
    public function getVerificationTokenInfo(string $email): ?array
    {
        $key = self::CACHE_PREFIX . md5($email);
        $data = Cache::get($key);

        if (!$data) {
            return null;
        }

        return [
            'created_at' => $data['created_at'],
            'expires_at' => Carbon::parse($data['created_at'])->addHours(self::VERIFICATION_EXPIRY)->toISOString(),
            'attempts' => $data['attempts'],
            'resend_count' => $data['resend_count'],
            'remaining_attempts' => self::MAX_ATTEMPTS - $data['attempts'],
            'remaining_resends' => self::MAX_ATTEMPTS - $data['resend_count'],
        ];
    }

    /**
     * Clean up expired tokens.
     */
    public function cleanupExpiredTokens(): int
    {
        $cleaned = 0;
        $pattern = self::CACHE_PREFIX . '*';

        // This would need to be implemented based on your cache driver
        // For now, return 0
        return $cleaned;
    }

    /**
     * Get email verification statistics.
     */
    public function getStatistics(): array
    {
        return [
            'verification_expiry_hours' => self::VERIFICATION_EXPIRY,
            'max_attempts' => self::MAX_ATTEMPTS,
            'expired_tokens_cleaned' => $this->cleanupExpiredTokens(),
        ];
    }
}
