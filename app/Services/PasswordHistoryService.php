<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class PasswordHistoryService
{
    /**
     * @var array<string, mixed>
     */
    private array $config;

    public function __construct()
    {
        $config = config('password_policy', [
            'history_count' => 5,
        ]);
        $this->config = is_array($config) ? $config : ['history_count' => 5];
    }

    /**
     * التحقق من وجود كلمة المرور في التاريخ
     */
    public function isPasswordInHistory(string $password, int $userId): bool
    {
        try {
            $history = $this->getPasswordHistory($userId);

            foreach ($history as $oldPassword) {
                if (is_string($oldPassword) && Hash::check($password, $oldPassword)) {
                    return true;
                }
            }

            return false;
        } catch (Exception $e) {
            Log::error('Password history check failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * حفظ كلمة المرور في التاريخ
     */
    public function savePasswordToHistory(string $password, int $userId): void
    {
        try {
            $hashedPassword = Hash::make($password);
            $history = $this->getPasswordHistory($userId);

            // إضافة كلمة المرور الجديدة
            array_unshift($history, $hashedPassword);

            // الحفاظ على العدد المحدد من كلمات المرور
            $historyCount = is_numeric($this->config['history_count']) ? (int) $this->config['history_count'] : 5;
            $history = array_slice($history, 0, $historyCount);

            // حفظ التاريخ
            cache()->put("password_history_{$userId}", $history, 86400 * 30); // 30 يوم

            Log::info("Password saved to history for user {$userId}");
        } catch (Exception $e) {
            Log::error('Failed to save password to history: '.$e->getMessage());
        }
    }

    /**
     * الحصول على تاريخ كلمات المرور
     *
     * @return array<string, mixed>
     */
    private function getPasswordHistory(int $userId): array
    {
        $history = cache()->get("password_history_{$userId}", []);

        return is_array($history) ? $history : [];
    }

    /**
     * مسح تاريخ كلمات المرور
     */
    public function clearPasswordHistory(int $userId): void
    {
        cache()->forget("password_history_{$userId}");
        Log::info("Password history cleared for user {$userId}");
    }
}
