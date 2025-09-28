<?php

declare(strict_types=1);

namespace App\Rules;

use Illuminate\Contracts\Config\Repository as ConfigRepository;

class PasswordValidator
{
    /**
     * @var array{
     *   min_length: int,
     *   require_uppercase: bool,
     *   require_lowercase: bool,
     *   require_numbers: bool,
     *   require_symbols: bool,
     *   forbidden_patterns: array<int, string>,
     *   history_count: int
     * }
     */
    private array $config;

    public function __construct(ConfigRepository $configRepository)
    {
        /** @var array $defaultConfig */
        $defaultConfig = [
            'min_length' => 8,
            'require_uppercase' => true,
            'require_lowercase' => true,
            'require_numbers' => true,
            'require_symbols' => true,
            'forbidden_patterns' => [],
            'history_count' => 5,
        ];
        $this->config = $configRepository->get('password_policy', $defaultConfig);
    }

    /**
     * التحقق من صحة كلمة المرور.
     *
     * @return array<string, mixed>
     */
    public function validatePassword(string $password): array
    {
        $errors = [];

        // التحقق من الطول الأدنى
        $minLength = is_numeric($this->config['min_length']) ? (int) $this->config['min_length'] : 8;
        if (strlen($password) < $minLength) {
            $errors[] = "كلمة المرور يجب أن تكون على الأقل {$minLength} أحرف";
        }

        // التحقق من الأحرف الكبيرة
        if ($this->config['require_uppercase'] && ! preg_match('/[A-Z]/', $password)) {
            $errors[] = 'كلمة المرور يجب أن تحتوي على حرف كبير واحد على الأقل';
        }

        // التحقق من الأحرف الصغيرة
        if ($this->config['require_lowercase'] && ! preg_match('/[a-z]/', $password)) {
            $errors[] = 'كلمة المرور يجب أن تحتوي على حرف صغير واحد على الأقل';
        }

        // التحقق من الأرقام
        if ($this->config['require_numbers'] && ! preg_match('/\d/', $password)) {
            $errors[] = 'كلمة المرور يجب أن تحتوي على رقم واحد على الأقل';
        }

        // التحقق من الرموز الخاصة
        if ($this->config['require_symbols'] && ! preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'كلمة المرور يجب أن تحتوي على رمز خاص واحد على الأقل';
        }

        // التحقق من الأنماط المحظورة
        $forbiddenPatterns = $this->config['forbidden_patterns'] ?? [];
        if (is_array($forbiddenPatterns)) {
            foreach ($forbiddenPatterns as $pattern) {
                if (is_string($pattern) && preg_match($pattern, $password)) {
                    $errors[] = 'كلمة المرور تحتوي على نمط محظور';
                    break;
                }
            }
        }

        return [
            'valid' => $errors === [],
            'errors' => $errors,
            'strength' => $this->calculatePasswordStrength($password),
        ];
    }

    /**
     * حساب قوة كلمة المرور.
     */
    private function calculatePasswordStrength(string $password): int
    {
        $score = 0;

        // نقاط الطول (تصل إلى 3 نقاط)
        $score += min((int) (strlen($password) / 4), 3);

        // نقاط التنوع
        $diversity = 0;
        if (preg_match('/[a-z]/', $password)) {
            $diversity++;
        }
        if (preg_match('/[A-Z]/', $password)) {
            $diversity++;
        }
        if (preg_match('/\d/', $password)) {
            $diversity++;
        }
        if (preg_match('/[^A-Za-z0-9]/', $password)) {
            $diversity++;
        }

        // نقاط إضافية للتنوع
        if ($diversity >= 3) {
            $score += $diversity;
        }

        return (int) min($score, 10);
    }
}
