<?php

namespace Tests\AI;

use App\Services\AI\ContinuousQualityMonitor;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

class ContinuousQualityMonitorTest extends TestCase
{
    private ContinuousQualityMonitor $monitor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->monitor = new ContinuousQualityMonitor;
        Cache::flush();
    }

    /**
     * @coversNothing
     */
    public function test_monitor_initializes_correctly(): void
    {
        $this->assertInstanceOf(ContinuousQualityMonitor::class, $this->monitor);
    }

    /**
     * @coversNothing
     */
    public function test_monitor_has_required_rules(): void
    {
        $reflection = new \ReflectionClass($this->monitor);
        $rulesProperty = $reflection->getProperty('rules');
        $rulesProperty->setAccessible(true);
        $rules = $rulesProperty->getValue($this->monitor);

        $this->assertIsArray($rules);
        $this->assertGreaterThan(0, count((array) $rules));

        foreach ((array) $rules as $rule) {
            $this->assertIsArray($rule);
            $this->assertArrayHasKey('name', $rule);
            $this->assertArrayHasKey('threshold', $rule);
            $this->assertArrayHasKey('critical', $rule);
            $threshold = $rule['threshold'] ?? 0;
            $critical = $rule['critical'] ?? false;
            $this->assertIsInt($threshold);
            $this->assertIsBool($critical);
        }
    }

    /**
     * @coversNothing
     */
    public function test_monitor_performs_quality_check(): void
    {
        Process::fake([
            './vendor/bin/phpstan analyse --memory-limit=1G --configuration=phpstan.strict.neon' => Process::result(exitCode: 0, output: 'No errors'),
            'php artisan test --configuration=phpunit.strict.xml --coverage-text' => Process::result(exitCode: 0, output: '100% coverage'),
            'composer audit' => Process::result(exitCode: 0, output: 'No security vulnerabilities'),
            'php artisan test tests/Performance/ --configuration=phpunit.strict.xml' => Process::result(exitCode: 0, output: 'PASS'),
            'php -d memory_limit=512M artisan test --configuration=phpunit.strict.xml' => Process::result(exitCode: 0, output: 'Memory usage: 256MB'),
        ]);

        $results = $this->monitor->performQualityCheck();

        $this->assertArrayHasKey('overall_health', $results);
        $this->assertArrayHasKey('rules', $results);
        $this->assertArrayHasKey('alerts', $results);
        $overallHealth = $results['overall_health'] ?? 0;
        $this->assertIsInt($overallHealth);
    }

    /**
     * @coversNothing
     */
    public function test_monitor_calculates_health_scores_correctly(): void
    {
        $reflection = new \ReflectionClass($this->monitor);
        $method = $reflection->getMethod('calculateHealthScore');
        $method->setAccessible(true);

        // Test code quality score
        $codeQualityScore = $method->invoke(
            $this->monitor,
            'code_quality',
            Process::result(exitCode: 0, output: 'No errors'),
            ['threshold' => 95, 'critical' => true]
        );
        $this->assertEquals(100, $codeQualityScore);

        // Test test coverage score
        $coverageScore = $method->invoke(
            $this->monitor,
            'test_coverage',
            Process::result(exitCode: 0, output: 'Lines: 95%'),
            ['threshold' => 90, 'critical' => true]
        );
        $this->assertEquals(95, $coverageScore);

        // Test security score
        $securityScore = $method->invoke(
            $this->monitor,
            'security_scan',
            Process::result(exitCode: 0, output: 'No security vulnerabilities'),
            ['threshold' => 100, 'critical' => true]
        );
        $this->assertEquals(100, $securityScore);
    }

    /**
     * @coversNothing
     */
    public function test_monitor_handles_failed_commands(): void
    {
        Process::fake([
            './vendor/bin/phpstan analyse --memory-limit=1G --configuration=phpstan.strict.neon' => Process::result(exitCode: 1, errorOutput: 'Fatal error'),
        ]);

        $reflection = new \ReflectionClass($this->monitor);
        $method = $reflection->getMethod('checkRule');
        $method->setAccessible(true);

        $rule = [
            'name' => 'Code Quality',
            'threshold' => 95,
            'command' => './vendor/bin/phpstan analyse --memory-limit=1G --configuration=phpstan.strict.neon',
            'critical' => true,
        ];

        $result = $method->invoke($this->monitor, 'code_quality', $rule);

        $this->assertIsArray($result);
        $success = $result['success'] ?? false;
        $healthScore = $result['health_score'] ?? 0;
        $errors = $result['errors'] ?? [];
        $this->assertFalse($success);
        $this->assertEquals(0, $healthScore);
        $this->assertNotEmpty($errors);
    }

    /**
     * @coversNothing
     */
    public function test_monitor_triggers_critical_alerts(): void
    {
        $reflection = new \ReflectionClass($this->monitor);
        $method = $reflection->getMethod('triggerCriticalAlert');
        $method->setAccessible(true);

        $result = [
            'name' => 'Test Rule',
            'health_score' => 50,
            'errors' => ['Critical error occurred'],
        ];

        $method->invoke($this->monitor, 'test_rule', $result);

        $alerts = $this->monitor->getAlertsSummary();
        $total = $alerts['total'] ?? 0;
        $critical = $alerts['critical'] ?? 0;
        $this->assertGreaterThan(0, $total);
        $this->assertGreaterThan(0, $critical);
    }

    /**
     * @coversNothing
     */
    public function test_monitor_triggers_warning_alerts(): void
    {
        $reflection = new \ReflectionClass($this->monitor);
        $method = $reflection->getMethod('triggerWarningAlert');
        $method->setAccessible(true);

        $result = [
            'name' => 'Test Rule',
            'health_score' => 70,
            'errors' => ['Warning occurred'],
        ];

        $method->invoke($this->monitor, 'test_rule', $result);

        $alerts = $this->monitor->getAlertsSummary();
        $total = $alerts['total'] ?? 0;
        $warnings = $alerts['warnings'] ?? 0;
        $this->assertGreaterThan(0, $total);
        $this->assertGreaterThan(0, $warnings);
    }

    /**
     * @coversNothing
     */
    public function test_monitor_updates_health_status(): void
    {
        Process::fake([
            './vendor/bin/phpstan analyse --memory-limit=1G --configuration=phpstan.strict.neon' => Process::result(exitCode: 0, output: 'No errors'),
            'php artisan test --configuration=phpunit.strict.xml --coverage-text' => Process::result(exitCode: 0, output: '100% coverage'),
            'composer audit' => Process::result(exitCode: 0, output: 'No security vulnerabilities'),
            'php artisan test tests/Performance/ --configuration=phpunit.strict.xml' => Process::result(exitCode: 0, output: 'PASS'),
            'php -d memory_limit=512M artisan test --configuration=phpunit.strict.xml' => Process::result(exitCode: 0, output: 'Memory usage: 256MB'),
        ]);

        $this->monitor->performQualityCheck();

        $healthScore = Cache::get('quality_health_score');
        $lastCheck = Cache::get('quality_last_check');
        $detailedResults = Cache::get('quality_detailed_results');

        $this->assertNotNull($healthScore);
        $this->assertNotNull($lastCheck);
        $this->assertNotNull($detailedResults);
        $this->assertIsInt($healthScore);
    }

    /**
     * @coversNothing
     */
    public function test_monitor_returns_health_status(): void
    {
        Cache::put('quality_health_score', 85, 3600);
        Cache::put('quality_last_check', now()->toISOString(), 3600);
        Cache::put('quality_detailed_results', ['test' => 'data'], 3600);

        $status = $this->monitor->getHealthStatus();

        $this->assertArrayHasKey('score', $status);
        $this->assertArrayHasKey('last_check', $status);
        $this->assertArrayHasKey('detailed_results', $status);
        $this->assertArrayHasKey('alerts', $status);
        $score = $status['score'] ?? 0;
        $this->assertEquals(85, $score);
    }

    /**
     * @coversNothing
     */
    public function test_monitor_returns_alerts_summary(): void
    {
        $reflection = new \ReflectionClass($this->monitor);
        $alertsProperty = $reflection->getProperty('alerts');
        $alertsProperty->setAccessible(true);
        $alertsProperty->setValue($this->monitor, [
            ['type' => 'critical', 'message' => 'Critical alert'],
            ['type' => 'warning', 'message' => 'Warning alert'],
            ['type' => 'warning', 'message' => 'Another warning'],
        ]);

        $summary = $this->monitor->getAlertsSummary();

        $this->assertArrayHasKey('total', $summary);
        $this->assertArrayHasKey('critical', $summary);
        $this->assertArrayHasKey('warnings', $summary);
        $this->assertArrayHasKey('alerts', $summary);
        $total = $summary['total'] ?? 0;
        $critical = $summary['critical'] ?? 0;
        $warnings = $summary['warnings'] ?? 0;
        $this->assertEquals(3, $total);
        $this->assertEquals(1, $critical);
        $this->assertEquals(2, $warnings);
    }

    /**
     * @coversNothing
     */
    public function test_monitor_can_clear_alerts(): void
    {
        $reflection = new \ReflectionClass($this->monitor);
        $alertsProperty = $reflection->getProperty('alerts');
        $alertsProperty->setAccessible(true);
        $alertsProperty->setValue($this->monitor, [
            ['type' => 'critical', 'message' => 'Critical alert'],
        ]);

        $this->monitor->clearAlerts();

        $summary = $this->monitor->getAlertsSummary();
        $total = $summary['total'] ?? 0;
        $this->assertEquals(0, $total);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
