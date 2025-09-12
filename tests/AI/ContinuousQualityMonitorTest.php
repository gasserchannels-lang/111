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

    public function test_monitor_initializes_correctly()
    {
        $this->assertInstanceOf(ContinuousQualityMonitor::class, $this->monitor);
    }

    public function test_monitor_has_required_rules()
    {
        $reflection = new \ReflectionClass($this->monitor);
        $rulesProperty = $reflection->getProperty('monitoringRules');
        $rulesProperty->setAccessible(true);
        $rules = $rulesProperty->getValue($this->monitor);

        $expectedRules = [
            'code_quality',
            'test_coverage',
            'security_scan',
            'performance',
            'memory_usage',
        ];

        foreach ($expectedRules as $rule) {
            $this->assertArrayHasKey($rule, $rules);
        }
    }

    public function test_monitor_rules_have_required_properties()
    {
        $reflection = new \ReflectionClass($this->monitor);
        $rulesProperty = $reflection->getProperty('monitoringRules');
        $rulesProperty->setAccessible(true);
        $rules = $rulesProperty->getValue($this->monitor);

        foreach ($rules as $ruleId => $rule) {
            $this->assertArrayHasKey('name', $rule);
            $this->assertArrayHasKey('threshold', $rule);
            $this->assertArrayHasKey('command', $rule);
            $this->assertArrayHasKey('critical', $rule);
            $this->assertIsInt($rule['threshold']);
            $this->assertIsBool($rule['critical']);
        }
    }

    public function test_monitor_performs_quality_check()
    {
        Process::fake([
            './vendor/bin/phpstan analyse --memory-limit=1G --configuration=phpstan.strict.neon' => Process::result(exitCode: 0, output: 'No errors'),
            'php artisan test --configuration=phpunit.strict.xml --coverage-text' => Process::result(exitCode: 0, output: '100% coverage'),
            'composer audit' => Process::result(exitCode: 0, output: 'No security vulnerabilities'),
            'php artisan test tests/Performance/ --configuration=phpunit.strict.xml' => Process::result(exitCode: 0, output: 'PASS'),
            'php -d memory_limit=512M artisan test --configuration=phpunit.strict.xml' => Process::result(exitCode: 0, output: 'Memory usage: 256MB'),
        ]);

        $results = $this->monitor->performQualityCheck();

        $this->assertIsArray($results);
        $this->assertArrayHasKey('overall_health', $results);
        $this->assertArrayHasKey('rules', $results);
        $this->assertArrayHasKey('alerts', $results);
        $this->assertIsInt($results['overall_health']);
        $this->assertIsArray($results['rules']);
    }

    public function test_monitor_calculates_health_scores_correctly()
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

    public function test_monitor_handles_failed_commands()
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

        $this->assertFalse($result['success']);
        $this->assertEquals(0, $result['health_score']);
        $this->assertNotEmpty($result['errors']);
    }

    public function test_monitor_triggers_critical_alerts()
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
        $this->assertGreaterThan(0, $alerts['total']);
        $this->assertGreaterThan(0, $alerts['critical']);
    }

    public function test_monitor_triggers_warning_alerts()
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
        $this->assertGreaterThan(0, $alerts['total']);
        $this->assertGreaterThan(0, $alerts['warnings']);
    }

    public function test_monitor_updates_health_status()
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
        $this->assertIsArray($detailedResults);
    }

    public function test_monitor_returns_health_status()
    {
        Cache::put('quality_health_score', 85, 3600);
        Cache::put('quality_last_check', now()->toISOString(), 3600);
        Cache::put('quality_detailed_results', ['test' => 'data'], 3600);

        $status = $this->monitor->getHealthStatus();

        $this->assertIsArray($status);
        $this->assertArrayHasKey('score', $status);
        $this->assertArrayHasKey('last_check', $status);
        $this->assertArrayHasKey('detailed_results', $status);
        $this->assertArrayHasKey('alerts', $status);
        $this->assertEquals(85, $status['score']);
    }

    public function test_monitor_returns_alerts_summary()
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

        $this->assertIsArray($summary);
        $this->assertArrayHasKey('total', $summary);
        $this->assertArrayHasKey('critical', $summary);
        $this->assertArrayHasKey('warnings', $summary);
        $this->assertArrayHasKey('alerts', $summary);
        $this->assertEquals(3, $summary['total']);
        $this->assertEquals(1, $summary['critical']);
        $this->assertEquals(2, $summary['warnings']);
    }

    public function test_monitor_can_clear_alerts()
    {
        $reflection = new \ReflectionClass($this->monitor);
        $alertsProperty = $reflection->getProperty('alerts');
        $alertsProperty->setAccessible(true);
        $alertsProperty->setValue($this->monitor, [
            ['type' => 'critical', 'message' => 'Critical alert'],
        ]);

        $this->monitor->clearAlerts();

        $summary = $this->monitor->getAlertsSummary();
        $this->assertEquals(0, $summary['total']);
    }
}
