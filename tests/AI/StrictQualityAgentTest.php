<?php

namespace Tests\AI;

use App\Services\AI\StrictQualityAgent;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

class StrictQualityAgentTest extends TestCase
{
    private StrictQualityAgent $agent;

    protected function setUp(): void
    {
        parent::setUp();
        $this->agent = new StrictQualityAgent;
    }

    public function test_agent_initializes_correctly()
    {
        $this->assertInstanceOf(StrictQualityAgent::class, $this->agent);
    }

    public function test_agent_has_all_required_stages()
    {
        $reflection = new \ReflectionClass($this->agent);
        $stagesProperty = $reflection->getProperty('stages');
        $stagesProperty->setAccessible(true);
        $stages = $stagesProperty->getValue($this->agent);

        $expectedStages = [
            'syntax_check',
            'phpstan_analysis',
            'phpmd_quality',
            'pint_formatting',
            'composer_audit',
            'unit_tests',
            'feature_tests',
            'ai_tests',
            'security_tests',
            'performance_tests',
            'integration_tests',
            'e2e_tests',
            'link_checker',
        ];

        foreach ($expectedStages as $stage) {
            $this->assertArrayHasKey($stage, $stages);
        }
    }

    public function test_agent_stages_have_required_properties()
    {
        $reflection = new \ReflectionClass($this->agent);
        $stagesProperty = $reflection->getProperty('stages');
        $stagesProperty->setAccessible(true);
        $stages = $stagesProperty->getValue($this->agent);

        foreach ($stages as $stageId => $stage) {
            $this->assertArrayHasKey('name', $stage);
            $this->assertArrayHasKey('command', $stage);
            $this->assertArrayHasKey('strict', $stage);
            $this->assertArrayHasKey('required', $stage);
            $this->assertTrue($stage['strict']);
            $this->assertTrue($stage['required']);
        }
    }

    public function test_agent_can_execute_single_stage()
    {
        // Mock Process facade
        Process::fake([
            'php -l' => Process::result(exitCode: 0, output: 'No syntax errors detected'),
        ]);

        $reflection = new \ReflectionClass($this->agent);
        $method = $reflection->getMethod('executeStage');
        $method->setAccessible(true);

        $stage = [
            'name' => 'Test Stage',
            'command' => 'php -l',
            'strict' => true,
            'required' => true,
        ];

        $result = $method->invoke($this->agent, 'test_stage', $stage);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('output', $result);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('duration', $result);
        $this->assertArrayHasKey('timestamp', $result);
    }

    public function test_agent_handles_stage_failure()
    {
        Process::fake([
            'invalid-command' => Process::result(exitCode: 1, errorOutput: 'Command not found'),
        ]);

        $reflection = new \ReflectionClass($this->agent);
        $method = $reflection->getMethod('executeStage');
        $method->setAccessible(true);

        $stage = [
            'name' => 'Test Stage',
            'command' => 'invalid-command',
            'strict' => true,
            'required' => true,
        ];

        $result = $method->invoke($this->agent, 'test_stage', $stage);

        $this->assertFalse($result['success']);
        $this->assertNotEmpty($result['errors']);
    }

    public function test_agent_can_auto_fix_issues()
    {
        Process::fake([
            './vendor/bin/pint --config=pint.strict.json' => Process::result(exitCode: 0),
            'composer install --no-dev --optimize-autoloader' => Process::result(exitCode: 0),
            'php artisan config:clear' => Process::result(exitCode: 0),
            'php artisan cache:clear' => Process::result(exitCode: 0),
            'php artisan route:clear' => Process::result(exitCode: 0),
            'php artisan view:clear' => Process::result(exitCode: 0),
        ]);

        $fixes = $this->agent->autoFixIssues();

        $this->assertIsArray($fixes);
        $this->assertArrayHasKey('formatting', $fixes);
        $this->assertArrayHasKey('dependencies', $fixes);
        $this->assertArrayHasKey('caches', $fixes);
    }

    public function test_agent_generates_report_file()
    {
        // Create a mock result
        $mockResults = [
            'overall_success' => true,
            'stages' => [
                'test_stage' => [
                    'success' => true,
                    'output' => 'Test output',
                    'errors' => [],
                    'duration' => 1.5,
                    'timestamp' => now()->toISOString(),
                ],
            ],
            'errors' => [],
            'fixes' => [],
        ];

        $reflection = new \ReflectionClass($this->agent);
        $method = $reflection->getMethod('generateFinalReport');
        $method->setAccessible(true);

        // Set mock results
        $resultsProperty = $reflection->getProperty('results');
        $resultsProperty->setAccessible(true);
        $resultsProperty->setValue($this->agent, $mockResults['stages']);

        $errorsProperty = $reflection->getProperty('errors');
        $errorsProperty->setAccessible(true);
        $errorsProperty->setValue($this->agent, $mockResults['errors']);

        $fixesProperty = $reflection->getProperty('fixes');
        $fixesProperty->setAccessible(true);
        $fixesProperty->setValue($this->agent, $mockResults['fixes']);

        $method->invoke($this->agent, $mockResults['overall_success']);

        $reportPath = storage_path('logs/ai-quality-report.json');
        $this->assertTrue(File::exists($reportPath));

        $report = json_decode(File::get($reportPath), true);
        $this->assertIsArray($report);
        $this->assertArrayHasKey('timestamp', $report);
        $this->assertArrayHasKey('overall_success', $report);
        $this->assertArrayHasKey('total_stages', $report);
    }

    public function test_agent_returns_correct_stage_status()
    {
        $mockResults = [
            'test_stage' => [
                'success' => true,
                'output' => 'Test output',
                'errors' => [],
                'duration' => 1.5,
                'timestamp' => now()->toISOString(),
            ],
        ];

        $reflection = new \ReflectionClass($this->agent);
        $resultsProperty = $reflection->getProperty('results');
        $resultsProperty->setAccessible(true);
        $resultsProperty->setValue($this->agent, $mockResults);

        $status = $this->agent->getStageStatus('test_stage');
        $this->assertIsArray($status);
        $this->assertTrue($status['success']);

        $nonExistentStatus = $this->agent->getStageStatus('non_existent_stage');
        $this->assertNull($nonExistentStatus);
    }

    public function test_agent_returns_all_results()
    {
        $mockResults = [
            'stage1' => ['success' => true],
            'stage2' => ['success' => false],
        ];

        $reflection = new \ReflectionClass($this->agent);
        $resultsProperty = $reflection->getProperty('results');
        $resultsProperty->setAccessible(true);
        $resultsProperty->setValue($this->agent, $mockResults);

        $allResults = $this->agent->getAllResults();
        $this->assertEquals($mockResults, $allResults);
    }

    public function test_agent_returns_errors_summary()
    {
        $mockErrors = [
            'stage1' => 'Error 1',
            'stage2' => 'Error 2',
        ];

        $reflection = new \ReflectionClass($this->agent);
        $errorsProperty = $reflection->getProperty('errors');
        $errorsProperty->setAccessible(true);
        $errorsProperty->setValue($this->agent, $mockErrors);

        $errorsSummary = $this->agent->getErrorsSummary();
        $this->assertIsArray($errorsSummary);
        $this->assertArrayHasKey('total_errors', $errorsSummary);
        $this->assertArrayHasKey('errors_by_stage', $errorsSummary);
        $this->assertArrayHasKey('critical_errors', $errorsSummary);
        $this->assertEquals(2, $errorsSummary['total_errors']);
    }
}
