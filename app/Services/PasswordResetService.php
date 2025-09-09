<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PasswordResetService
{
    private const TOKEN_EXPIRY = 60; // minutes
    private const MAX_ATTEMPTS = 3;
    private const CACHE_PREFIX = 'password_reset:';

    /**
     * Send password reset email
     */
    public function sendResetEmail(string $email): bool
    {
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            Log::warning('Password reset requested for non-existent email', [
                'email' => $email,
                'ip' => request()->ip(),
            ]);
            return false;
        }

        // Check if user is blocked
        if ($user->is_blocked) {
            Log::warning('Password reset requested for blocked user', [
                'email' => $email,
                'user_id' => $user->id,
            ]);
            return false;
        }

        // Generate reset token
        $token = $this->generateResetToken();
        
        // Store token in cache
        $this->storeResetToken($email, $token);
        
        // Send email
        try {
            Mail::send('emails.password-reset', [
                'user' => $user,
                'token' => $token,
                'expiry' => self::TOKEN_EXPIRY,
            ], function ($message) use ($user) {
                $message->to($user->email, $user->name)
                        ->subject('استعادة كلمة المرور - كوبرا');
            });
            
            Log::info('Password reset email sent', [
                'email' => $email,
                'user_id' => $user->id,
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send password reset email', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    /**
     * Reset password with token
     */
    public function resetPassword(string $email, string $token, string $newPassword): bool
    {
        // Validate token
        if (!$this->validateResetToken($email, $token)) {
            Log::warning('Invalid password reset token', [
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

        // Update password
        $user->password = Hash::make($newPassword);
        $user->save();

        // Clear reset token
        $this->clearResetToken($email);

        // Log password reset
        Log::info('Password reset successful', [
            'email' => $email,
            'user_id' => $user->id,
            'ip' => request()->ip(),
        ]);

        return true;
    }

    /**
     * Generate reset token
     */
    private function generateResetToken(): string
    {
        return Str::random(64);
    }

    /**
     * Store reset token
     */
    private function storeResetToken(string $email, string $token): void
    {
        $key = self::CACHE_PREFIX . md5($email);
        
        $data = [
            'token' => $token,
            'created_at' => now()->toISOString(),
            'attempts' => 0,
        ];
        
        Cache::put($key, $data, now()->addMinutes(self::TOKEN_EXPIRY));
    }

    /**
     * Validate reset token
     */
    private function validateResetToken(string $email, string $token): bool
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
            Cache::put($key, $data, now()->addMinutes(self::TOKEN_EXPIRY));
            
            // Block if too many attempts
            if ($data['attempts'] >= self::MAX_ATTEMPTS) {
                Cache::forget($key);
            }
            
            return false;
        }

        // Check if token is expired
        $createdAt = Carbon::parse($data['created_at']);
        if ($createdAt->addMinutes(self::TOKEN_EXPIRY)->isPast()) {
            Cache::forget($key);
            return false;
        }

        return true;
    }

    /**
     * Clear reset token
     */
    private function clearResetToken(string $email): void
    {
        $key = self::CACHE_PREFIX . md5($email);
        Cache::forget($key);
    }

    /**
     * Check if reset token exists
     */
    public function hasResetToken(string $email): bool
    {
        $key = self::CACHE_PREFIX . md5($email);
        return Cache::has($key);
    }

    /**
     * Get reset token info
     */
    public function getResetTokenInfo(string $email): ?array
    {
        $key = self::CACHE_PREFIX . md5($email);
        $data = Cache::get($key);
        
        if (!$data) {
            return null;
        }

        return [
            'created_at' => $data['created_at'],
            'expires_at' => Carbon::parse($data['created_at'])->addMinutes(self::TOKEN_EXPIRY)->toISOString(),
            'attempts' => $data['attempts'],
            'remaining_attempts' => self::MAX_ATTEMPTS - $data['attempts'],
        ];
    }

    /**
     * Clean up expired tokens
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
     * Get password reset statistics
     */
    public function getStatistics(): array
    {
        return [
            'token_expiry_minutes' => self::TOKEN_EXPIRY,
            'max_attempts' => self::MAX_ATTEMPTS,
            'expired_tokens_cleaned' => $this->cleanupExpiredTokens(),
        ];
    }
}
