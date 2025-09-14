<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class PasswordPolicyService
{
    /**
     * @var array<string, mixed>
     */
    private array $config;

    public function __construct()
    {
        $config = config('password_policy', [
            'min_length' => 8,
            'max_length' => 128,
            'require_uppercase' => true,
            'require_lowercase' => true,
            'require_numbers' => true,
            'require_symbols' => true,
            'forbidden_passwords' => [
                'password',
                '123456',
                'qwerty',
                'abc123',
                'password123',
                'admin',
                'root',
                'user',
            ],
            'history_count' => 5, // Remember last 5 passwords
            'expiry_days' => 90, // Password expires after 90 days
            'lockout_attempts' => 5, // Lock account after 5 failed attempts
            'lockout_duration' => 30, // Lock for 30 minutes
        ]);
        $this->config = is_array($config) ? $config : [];
    }

    /**
     * Validate password against policy.
     *
     * @return array<string, mixed>
     */
    public function validatePassword(string $password, ?int $userId = null): array
    {
        $errors = [];

        // Check minimum length
        $minLength = $this->config['min_length'] ?? 8;
        if (strlen($password) < $minLength) {
            $errors[] = 'Password must be at least '.$minLength.' characters long';
        }

        // Check maximum length
        $maxLength = $this->config['max_length'] ?? 128;
        if (strlen($password) > $maxLength) {
            $errors[] = 'Password must not exceed '.$maxLength.' characters';
        }

        // Check for uppercase letters
        if (($this->config['require_uppercase'] ?? false) && ! preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }

        // Check for lowercase letters
        if (($this->config['require_lowercase'] ?? false) && ! preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }

        // Check for numbers
        if (($this->config['require_numbers'] ?? false) && ! preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }

        // Check for symbols
        if (($this->config['require_symbols'] ?? false) && ! preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        }

        // Check against forbidden passwords
        $forbiddenPasswords = $this->config['forbidden_passwords'] ?? [];
        if (is_array($forbiddenPasswords)) {
            $lowercaseForbidden = array_map(fn ($pwd) => is_string($pwd) ? strtolower($pwd) : '', $forbiddenPasswords);
            if (in_array(strtolower($password), $lowercaseForbidden)) {
                $errors[] = 'Password is too common and not allowed';
            }
        }

        // Check against user's password history
        if ($userId && $this->isPasswordInHistory($userId, $password)) {
            $errors[] = 'Password has been used recently and is not allowed';
        }

        // Check for common patterns
        $patternErrors = $this->checkCommonPatterns($password);
        $errors = array_merge($errors, $patternErrors);

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'strength' => $this->calculatePasswordStrength($password),
        ];
    }

    /**
     * Check for common password patterns.
     *
     * @return list<string>
     */
    private function checkCommonPatterns(string $password): array
    {
        $errors = [];

        // Check for sequential characters
        if (preg_match('/(.)\1{2,}/', $password)) {
            $errors[] = 'Password contains repeated characters';
        }

        // Check for keyboard patterns
        $keyboardPatterns = [
            'qwerty',
            'asdf',
            'zxcv',
            '1234',
            'abcd',
            'qwertyuiop',
            'asdfghjkl',
            'zxcvbnm',
        ];

        foreach ($keyboardPatterns as $pattern) {
            if (stripos($password, $pattern) !== false) {
                $errors[] = 'Password contains keyboard patterns';
                break;
            }
        }

        // Check for common substitutions
        $commonSubstitutions = [
            'password' => ['p@ssw0rd', 'p@ssword', 'passw0rd'],
            'admin' => ['@dmin', 'adm1n', '@dm1n'],
            'welcome' => ['w3lc0m3', 'w3lcome'],
        ];

        foreach ($commonSubstitutions as $original => $substitutions) {
            foreach ($substitutions as $substitution) {
                if (stripos($password, $substitution) !== false) {
                    $errors[] = 'Password contains common character substitutions';
                    break 2;
                }
            }
        }

        return $errors;
    }

    /**
     * Calculate password strength.
     */
    private function calculatePasswordStrength(string $password): string
    {
        $score = 0;
        $length = strlen($password);

        // Length score
        if ($length >= 8) {
            $score += 1;
        }
        if ($length >= 12) {
            $score += 1;
        }
        if ($length >= 16) {
            $score += 1;
        }

        // Character variety score
        if (preg_match('/[a-z]/', $password)) {
            $score += 1;
        }
        if (preg_match('/[A-Z]/', $password)) {
            $score += 1;
        }
        if (preg_match('/[0-9]/', $password)) {
            $score += 1;
        }
        if (preg_match('/[^A-Za-z0-9]/', $password)) {
            $score += 1;
        }

        // Complexity score
        $uniqueChars = count(array_unique(str_split($password)));
        if ($uniqueChars / $length > 0.7) {
            $score += 1;
        }

        // Determine strength level
        if ($score <= 3) {
            return 'weak';
        }
        if ($score <= 5) {
            return 'medium';
        }
        if ($score <= 7) {
            return 'strong';
        }

        return 'very_strong';
    }

    /**
     * Check if password is in user's history.
     */
    private function isPasswordInHistory(int $userId, string $password): bool
    {
        try {
            $historyCount = $this->config['history_count'] ?? 5;

            // This would query a password_history table
            // For now, we'll simulate the check
            $passwordHashes = $this->getUserPasswordHistory($userId, $historyCount);

            foreach ($passwordHashes as $hash) {
                if (Hash::check($password, $hash)) {
                    return true;
                }
            }

            return false;
        } catch (Exception $e) {
            Log::error('Password history check failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get user's password history.
     *
     * @return list<string>
     */
    private function getUserPasswordHistory(int $userId, int $limit): array
    {
        // This would query the password_history table
        // For now, return empty array
        return [];
    }

    /**
     * Save password to history.
     */
    public function savePasswordToHistory(int $userId, string $password): bool
    {
        try {
            $hashedPassword = Hash::make($password);

            // This would save to password_history table
            // For now, we'll just log it
            Log::info('Password saved to history', [
                'user_id' => $userId,
                'hashed' => $hashedPassword,
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Failed to save password to history', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Check if password has expired.
     */
    public function isPasswordExpired(int $userId): bool
    {
        try {
            $expiryDays = $this->config['expiry_days'] ?? 90;

            // This would query the users table for password_updated_at
            // For now, we'll simulate the check
            $lastPasswordChange = $this->getLastPasswordChange($userId);

            // Since getLastPasswordChange always returns null for now,
            // we return true (password is expired/needs to be set)
            return true;
        } catch (Exception $e) {
            Log::error('Password expiry check failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get last password change date.
     */
    private function getLastPasswordChange(int $userId): null
    {
        // This would query the users table
        // For now, return null
        return null;
    }

    /**
     * Check if account is locked due to failed attempts.
     */
    public function isAccountLocked(int $userId): bool
    {
        try {
            $lockoutAttempts = $this->config['lockout_attempts'] ?? 5;
            $lockoutDuration = $this->config['lockout_duration'] ?? 30;

            // This would query a failed_attempts table
            $failedAttempts = $this->getFailedAttempts($userId, $lockoutDuration);

            return $failedAttempts >= $lockoutAttempts;
        } catch (Exception $e) {
            Log::error('Account lock check failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get failed login attempts.
     */
    private function getFailedAttempts(int $userId, int $durationMinutes): int
    {
        // This would query the failed_attempts table
        // For now, return 0
        return 0;
    }

    /**
     * Record failed login attempt.
     */
    public function recordFailedAttempt(int $userId, string $ipAddress): void
    {
        try {
            // This would save to failed_attempts table
            Log::info('Failed login attempt recorded', [
                'user_id' => $userId,
                'ip_address' => $ipAddress,
                'timestamp' => now()->toISOString(),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to record failed attempt', [
                'user_id' => $userId,
                'ip_address' => $ipAddress,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Clear failed attempts.
     */
    public function clearFailedAttempts(int $userId): void
    {
        try {
            // This would clear from failed_attempts table
            Log::info('Failed attempts cleared', [
                'user_id' => $userId,
                'timestamp' => now()->toISOString(),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to clear failed attempts', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Generate secure password.
     */
    public function generateSecurePassword(int $length = 12): string
    {
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $symbols = '!@#$%^&*()_+-=[]{}|;:,.<>?';

        $allChars = $uppercase.$lowercase.$numbers.$symbols;

        $password = '';

        // Ensure at least one character from each category
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $symbols[random_int(0, strlen($symbols) - 1)];

        // Fill the rest randomly
        for ($i = 4; $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }

        // Shuffle the password
        return str_shuffle($password);
    }

    /**
     * Get password policy requirements.
     *
     * @return array<string, mixed>
     */
    public function getPolicyRequirements(): array
    {
        return [
            'min_length' => $this->config['min_length'] ?? 8,
            'max_length' => $this->config['max_length'] ?? 128,
            'require_uppercase' => $this->config['require_uppercase'] ?? true,
            'require_lowercase' => $this->config['require_lowercase'] ?? true,
            'require_numbers' => $this->config['require_numbers'] ?? true,
            'require_symbols' => $this->config['require_symbols'] ?? true,
            'expiry_days' => $this->config['expiry_days'] ?? 90,
            'history_count' => $this->config['history_count'] ?? 5,
        ];
    }

    /**
     * Update password policy.
     *
     * @param  array<string, mixed>  $newPolicy
     */
    public function updatePolicy(array $newPolicy): bool
    {
        try {
            $this->config = array_merge($this->config, $newPolicy);

            // Update config file
            $configPath = config_path('password_policy.php');
            $configContent = "<?php\n\nreturn ".var_export($this->config, true).";\n";
            file_put_contents($configPath, $configContent);

            Log::info('Password policy updated', $newPolicy);

            return true;
        } catch (Exception $e) {
            Log::error('Failed to update password policy', [
                'error' => $e->getMessage(),
                'policy' => $newPolicy,
            ]);

            return false;
        }
    }
}
