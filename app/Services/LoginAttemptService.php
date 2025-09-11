<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LoginAttemptService
{
    private const MAX_ATTEMPTS = 5;

    private const LOCKOUT_DURATION = 15; // minutes

    private const CACHE_PREFIX = 'login_attempts:';

    private const IP_PREFIX = 'ip_attempts:';

    /**
     * Record a failed login attempt.
     */
    public function recordFailedAttempt(Request $request, ?string $email = null): void
    {
        $ip = $request->ip();
        $userAgent = $request->userAgent();

        // Record attempt by IP
        $this->recordIpAttempt($ip ?? '');

        // Record attempt by email if provided
        if ($email) {
            $this->recordEmailAttempt($email, $ip ?? '', $userAgent ?? '');
        }

        Log::warning('Failed login attempt', [
            'ip' => $ip,
            'email' => $email,
            'user_agent' => $userAgent,
            'timestamp' => now(),
        ]);
    }

    /**
     * Record a successful login attempt.
     */
    public function recordSuccessfulAttempt(Request $request, string $email): void
    {
        $ip = $request->ip();

        // Clear failed attempts for this email
        $this->clearEmailAttempts($email);

        // Clear failed attempts for this IP
        $this->clearIpAttempts($ip ?? '');

        Log::info('Successful login', [
            'ip' => $ip,
            'email' => $email,
            'timestamp' => now(),
        ]);
    }

    /**
     * Check if login is blocked for email.
     */
    public function isEmailBlocked(string $email): bool
    {
        $key = self::CACHE_PREFIX.md5($email);
        $attempts = Cache::get($key, []);

        return count($attempts) >= self::MAX_ATTEMPTS;
    }

    /**
     * Check if login is blocked for IP.
     */
    public function isIpBlocked(string $ip): bool
    {
        $key = self::IP_PREFIX.md5($ip);
        $attempts = Cache::get($key, []);

        return count($attempts) >= self::MAX_ATTEMPTS;
    }

    /**
     * Get remaining attempts for email.
     */
    public function getRemainingAttempts(string $email): int
    {
        $key = self::CACHE_PREFIX.md5($email);
        $attempts = Cache::get($key, []);

        return max(0, self::MAX_ATTEMPTS - count($attempts));
    }

    /**
     * Get remaining attempts for IP.
     */
    public function getRemainingIpAttempts(string $ip): int
    {
        $key = self::IP_PREFIX.md5($ip);
        $attempts = Cache::get($key, []);

        return max(0, self::MAX_ATTEMPTS - count($attempts));
    }

    /**
     * Get lockout time remaining for email.
     */
    public function getLockoutTimeRemaining(string $email): ?int
    {
        $key = self::CACHE_PREFIX.md5($email);
        $attempts = Cache::get($key, []);

        if (count($attempts) >= self::MAX_ATTEMPTS) {
            $lastAttempt = end($attempts);
            $lockoutEnd = Carbon::parse($lastAttempt['timestamp'])->addMinutes(self::LOCKOUT_DURATION);

            if ($lockoutEnd->isFuture()) {
                return (int) $lockoutEnd->diffInSeconds(now());
            }
        }

        return null;
    }

    /**
     * Get lockout time remaining for IP.
     */
    public function getIpLockoutTimeRemaining(string $ip): ?int
    {
        $key = self::IP_PREFIX.md5($ip);
        $attempts = Cache::get($key, []);

        if (count($attempts) >= self::MAX_ATTEMPTS) {
            $lastAttempt = end($attempts);
            $lockoutEnd = Carbon::parse($lastAttempt['timestamp'])->addMinutes(self::LOCKOUT_DURATION);

            if ($lockoutEnd->isFuture()) {
                return (int) $lockoutEnd->diffInSeconds(now());
            }
        }

        return null;
    }

    /**
     * Record IP attempt.
     */
    private function recordIpAttempt(string $ip): void
    {
        $key = self::IP_PREFIX.md5($ip);
        $attempts = Cache::get($key, []);

        $attempts[] = [
            'timestamp' => now()->toISOString(),
            'ip' => $ip,
        ];

        // Keep only recent attempts
        $attempts = array_slice($attempts, -self::MAX_ATTEMPTS);

        Cache::put($key, $attempts, now()->addMinutes(self::LOCKOUT_DURATION));
    }

    /**
     * Record email attempt.
     */
    private function recordEmailAttempt(string $email, string $ip, string $userAgent): void
    {
        $key = self::CACHE_PREFIX.md5($email);
        $attempts = Cache::get($key, []);

        $attempts[] = [
            'timestamp' => now()->toISOString(),
            'ip' => $ip,
            'user_agent' => $userAgent,
        ];

        // Keep only recent attempts
        $attempts = array_slice($attempts, -self::MAX_ATTEMPTS);

        Cache::put($key, $attempts, now()->addMinutes(self::LOCKOUT_DURATION));
    }

    /**
     * Clear email attempts.
     */
    private function clearEmailAttempts(string $email): void
    {
        $key = self::CACHE_PREFIX.md5($email);
        Cache::forget($key);
    }

    /**
     * Clear IP attempts.
     */
    private function clearIpAttempts(string $ip): void
    {
        $key = self::IP_PREFIX.md5($ip);
        Cache::forget($key);
    }

    /**
     * Get all blocked emails.
     *
     * @return list<string>
     */
    public function getBlockedEmails(): array
    {
        $blocked = [];
        $pattern = self::CACHE_PREFIX.'*';

        // This would need to be implemented based on your cache driver
        // For now, return empty array
        return $blocked;
    }

    /**
     * Get all blocked IPs.
     *
     * @return list<string>
     */
    public function getBlockedIps(): array
    {
        $blocked = [];
        $pattern = self::IP_PREFIX.'*';

        // This would need to be implemented based on your cache driver
        // For now, return empty array
        return $blocked;
    }

    /**
     * Unblock email.
     */
    public function unblockEmail(string $email): void
    {
        $this->clearEmailAttempts($email);

        Log::info('Email unblocked', [
            'email' => $email,
            'timestamp' => now(),
        ]);
    }

    /**
     * Unblock IP.
     */
    public function unblockIp(string $ip): void
    {
        $this->clearIpAttempts($ip);

        Log::info('IP unblocked', [
            'ip' => $ip,
            'timestamp' => now(),
        ]);
    }

    /**
     * Get login attempt statistics.
     *
     * @return array<string, mixed>
     */
    public function getStatistics(): array
    {
        return [
            'max_attempts' => self::MAX_ATTEMPTS,
            'lockout_duration' => self::LOCKOUT_DURATION,
            'blocked_emails_count' => count($this->getBlockedEmails()),
            'blocked_ips_count' => count($this->getBlockedIps()),
        ];
    }
}
