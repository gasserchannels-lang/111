<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class ContinuousQualityMonitor
{
    /** @var array<string, mixed> */
    private array $monitoringRules = [];

    /** @var array<string, mixed> */
    /** @var array<int|string, mixed> */
    private array $alerts = [];

    private int $checkInterval = 300; // 5 minutes

    public function __construct()
    {
        $this->initializeMonitoringRules();
    }

    /**
     * Initialize monitoring rules.
     */
    private function initializeMonitoringRules(): void
    {
        $this->monitoringRules = [
            'code_quality' => [
                'name' => 'جودة الكود',
                'threshold' => 95, // 95% success rate required
                'command' => './vendor/bin/phpstan analyse --memory-limit=1G --configuration=phpstan.strict.neon',
                'critical' => true,
            ],
            'test_coverage' => [
                'name' => 'تغطية الاختبارات',
                'threshold' => 90, // 90% coverage required
                'command' => 'php artisan test --configuration=phpunit.strict.xml --coverage-text',
                'critical' => true,
            ],
            'security_scan' => [
                'name' => 'فحص الأمان',
                'threshold' => 100, // 100% security required
                'command' => 'composer audit',
                'critical' => true,
            ],
            'performance' => [
                'name' => 'الأداء',
                'threshold' => 80, // 80% performance score required
                'command' => 'php artisan test tests/Performance/ --configuration=phpunit.strict.xml',
                'critical' => false,
            ],
            'memory_usage' => [
                'name' => 'استهلاك الذاكرة',
                'threshold' => 512, // 512MB max memory usage
                'command' => 'php -d memory_limit=512M artisan test --configuration=phpunit.strict.xml',
                'critical' => true,
            ],
        ];
    }

    /**
     * Start continuous monitoring.
     */
    public function startMonitoring(): void
    {
        Log::info('🔍 بدء المراقبة المستمرة للجودة...');

        $running = true;
        while ($running) {
            $this->performQualityCheck();
            sleep($this->checkInterval);
            // In a real implementation, you would have a way to stop this loop
            // For now, we'll add a break after a reasonable number of iterations
            static $iterations = 0;
            if (++$iterations > 1000) {
                $running = false;
            }
        }
    }

    /**
     * Perform quality check.
     *
     * @return array<string, mixed>
     */
    public function performQualityCheck(): array
    {
        $results = [];
        $overallHealth = 100;

        foreach ($this->monitoringRules as $ruleId => $rule) {
            if (is_array($rule)) {
                $result = $this->checkRule((string) $ruleId, $rule);
                $results[(string) $ruleId] = $result;

                if (is_numeric($result['health_score'] ?? null) && is_numeric($rule['threshold'] ?? null)) {
                    $healthScore = (int) $result['health_score'];
                    $threshold = (int) $rule['threshold'];

                    if ($healthScore < $threshold) {
                        $overallHealth = min($overallHealth, $healthScore);

                        if ($rule['critical'] ?? false) {
                            $this->triggerCriticalAlert($ruleId, $result);
                        } else {
                            $this->triggerWarningAlert($ruleId, $result);
                        }
                    }
                }
            }
        }

        $this->updateHealthStatus($overallHealth, $results);

        return [
            'overall_health' => $overallHealth,
            'rules' => $results,
            'alerts' => $this->alerts,
        ];
    }

    /**
     * Check a specific rule.
     *
     * @param  array<mixed, mixed>  $rule
     * @return array<string, mixed>
     */
    private function checkRule(string $ruleId, array $rule): array
    {
        $startTime = microtime(true);

        try {
            $command = $rule['command'] ?? '';
            if (is_string($command) && ! empty($command)) {
                $result = Process::run($command);
                $endTime = microtime(true);
                $duration = round($endTime - $startTime, 2);

                $success = $result->successful();
                $healthScore = $this->calculateHealthScore($ruleId, $result, $rule);
            } else {
                $result = null;
                $endTime = microtime(true);
                $duration = round($endTime - $startTime, 2);
                $success = false;
                $healthScore = 0;
            }

            return [
                'name' => $rule['name'],
                'success' => $success,
                'health_score' => $healthScore,
                'duration' => $duration,
                'output' => $result ? $result->output() : '',
                'errors' => $result ? $result->errorOutput() : [],
                'timestamp' => now()->toISOString(),
                'critical' => $rule['critical'],
            ];
        } catch (\Exception $e) {
            return [
                'name' => $rule['name'],
                'success' => false,
                'health_score' => 0,
                'duration' => 0,
                'output' => '',
                'errors' => [$e->getMessage()],
                'timestamp' => now()->toISOString(),
                'critical' => $rule['critical'],
            ];
        }
    }

    /**
     * Calculate health score based on rule type.
     *
     * @param  array<string, mixed>  $rule
     */
    private function calculateHealthScore(string $ruleId, mixed $result, array $rule): int
    {
        if (! $result || ! is_object($result) || ! method_exists($result, 'successful') || ! $result->successful()) {
            return 0;
        }

        switch ($ruleId) {
            case 'code_quality':
                return $this->calculateCodeQualityScore(method_exists($result, 'output') ? $result->output() : '');

            case 'test_coverage':
                return $this->calculateTestCoverageScore(method_exists($result, 'output') ? $result->output() : '');

            case 'security_scan':
                return $this->calculateSecurityScore(method_exists($result, 'output') ? $result->output() : '');

            case 'performance':
                return $this->calculatePerformanceScore(method_exists($result, 'output') ? $result->output() : '');

            case 'memory_usage':
                return $this->calculateMemoryScore(method_exists($result, 'output') ? $result->output() : '');

            default:
                return 100;
        }
    }

    /**
     * Calculate code quality score.
     */
    private function calculateCodeQualityScore(string $output): int
    {
        // Parse PHPStan output to calculate score
        if (str_contains($output, 'No errors')) {
            return 100;
        }

        // Count errors and calculate score
        $errorCount = substr_count($output, 'ERROR');
        $warningCount = substr_count($output, 'WARNING');

        $totalIssues = $errorCount + $warningCount;
        $score = max(0, 100 - ($totalIssues * 5));

        return $score;
    }

    /**
     * Calculate test coverage score.
     */
    private function calculateTestCoverageScore(string $output): int
    {
        // Parse coverage output to extract percentage
        if (preg_match('/(\d+)%/', $output, $matches)) {
            return (int) $matches[1];
        }

        return 0;
    }

    /**
     * Calculate security score.
     */
    private function calculateSecurityScore(string $output): int
    {
        if (str_contains($output, 'No security vulnerabilities')) {
            return 100;
        }

        // Count security issues
        $vulnerabilityCount = substr_count($output, 'vulnerability');
        $score = max(0, 100 - ($vulnerabilityCount * 20));

        return $score;
    }

    /**
     * Calculate performance score.
     */
    private function calculatePerformanceScore(string $output): int
    {
        // Parse performance test results
        if (str_contains($output, 'PASS')) {
            return 100;
        }

        return 50; // Default score for performance tests
    }

    /**
     * Calculate memory usage score.
     */
    private function calculateMemoryScore(string $output): int
    {
        // Parse memory usage from output
        if (preg_match('/(\d+)MB/', $output, $matches)) {
            $memoryUsage = (int) $matches[1];
            $maxMemory = 512;
            $score = max(0, 100 - (($memoryUsage / $maxMemory) * 100));

            return $score;
        }

        return 100;
    }

    /**
     * Trigger critical alert.
     *
     * @param  array<string, mixed>  $result
     */
    private function triggerCriticalAlert(string $ruleId, array $result): void
    {
        $alert = [
            'type' => 'critical',
            'rule' => $ruleId,
            'message' => 'تنبيه حرج: فشل في '.(is_string($result['name'] ?? null) ? $result['name'] : ''),
            'details' => is_array($result['errors'] ?? null) ? $result['errors'] : [],
            'timestamp' => now()->toISOString(),
        ];

        $this->alerts[(string) count($this->alerts)] = $alert;
        Log::critical("🚨 تنبيه حرج: {$alert['message']}");

        // Send notification (email, Slack, etc.)
        $this->sendNotification($alert);
    }

    /**
     * Trigger warning alert.
     *
     * @param  array<string, mixed>  $result
     */
    private function triggerWarningAlert(string $ruleId, array $result): void
    {
        $alert = [
            'type' => 'warning',
            'rule' => $ruleId,
            'message' => 'تحذير: مشكلة في '.(is_string($result['name'] ?? null) ? $result['name'] : ''),
            'details' => is_array($result['errors'] ?? null) ? $result['errors'] : [],
            'timestamp' => now()->toISOString(),
        ];

        $this->alerts[(string) count($this->alerts)] = $alert;
        Log::warning("⚠️ تحذير: {$alert['message']}");
    }

    /**
     * Update health status.
     *
     * @param  array<string, mixed>  $results
     */
    private function updateHealthStatus(int $overallHealth, array $results): void
    {
        Cache::put('quality_health_score', $overallHealth, 3600);
        Cache::put('quality_last_check', now()->toISOString(), 3600);
        Cache::put('quality_detailed_results', $results, 3600);

        Log::info("📊 تحديث حالة الجودة: {$overallHealth}%");
    }

    /**
     * Send notification.
     *
     * @param  array<string, mixed>  $alert
     */
    private function sendNotification(array $alert): void
    {
        // Implement notification logic (email, Slack, etc.)
        $message = is_string($alert['message'] ?? null) ? $alert['message'] : '';
        Log::info('📧 إرسال إشعار: '.$message);
    }

    /**
     * Get current health status.
     *
     * @return array<string, mixed>
     */
    public function getHealthStatus(): array
    {
        return [
            'score' => Cache::get('quality_health_score', 0),
            'last_check' => Cache::get('quality_last_check'),
            'detailed_results' => Cache::get('quality_detailed_results', []),
            'alerts' => $this->alerts,
        ];
    }

    /**
     * Get alerts summary.
     *
     * @return array<string, mixed>
     */
    public function getAlertsSummary(): array
    {
        $criticalAlerts = array_filter($this->alerts, function ($alert) {
            return is_array($alert) && is_string($alert['type'] ?? null) && $alert['type'] === 'critical';
        });
        $warningAlerts = array_filter($this->alerts, function ($alert) {
            return is_array($alert) && is_string($alert['type'] ?? null) && $alert['type'] === 'warning';
        });

        return [
            'total' => count($this->alerts),
            'critical' => count($criticalAlerts),
            'warnings' => count($warningAlerts),
            'alerts' => $this->alerts,
        ];
    }

    /**
     * Clear alerts.
     */
    public function clearAlerts(): void
    {
        $this->alerts = [];
        Log::info('🗑️ تم مسح جميع التنبيهات');
    }
}
