<?php

namespace Tests\Unit;

use App\Console\Commands\AgentProposeFixCommand;
use App\Services\ProcessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Process\ProcessResult;
use Tests\TestCase;

class AgentProposeFixCommandTest extends TestCase
{
    use RefreshDatabase;

    private ProcessService $processService;

    private AgentProposeFixCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->processService = $this->createMock(ProcessService::class);
        $this->command = new AgentProposeFixCommand($this->processService);
    }

    public function test_it_has_correct_signature_and_description()
    {
        $this->assertEquals('agent:propose-fix', $this->command->getName());
        $this->assertEquals('Propose automated fixes via Pull Request for different types of issues', $this->command->getDescription());
    }

    public function test_it_generates_correct_commit_messages()
    {
        $reflection = new \ReflectionClass($this->command);
        $method = $reflection->getMethod('getCommitMessage');
        $method->setAccessible(true);

        $this->assertEquals('style: Apply automated code style fixes', $method->invoke($this->command, 'style'));
        $this->assertEquals('refactor: Generate PHPStan baseline', $method->invoke($this->command, 'analysis'));
        $this->assertEquals('fix: Apply automated custom fixes', $method->invoke($this->command, 'custom'));
    }

    public function test_it_generates_correct_pr_titles()
    {
        $reflection = new \ReflectionClass($this->command);
        $method = $reflection->getMethod('getPullRequestTitle');
        $method->setAccessible(true);

        $this->assertEquals('Automated Style Fixes', $method->invoke($this->command, 'style'));
        $this->assertEquals('Automated Static Analysis Fixes: PHPStan Baseline', $method->invoke($this->command, 'analysis'));
        $this->assertEquals('Automated custom Fixes', $method->invoke($this->command, 'custom'));
    }

    public function test_it_generates_correct_pr_bodies()
    {
        $reflection = new \ReflectionClass($this->command);
        $method = $reflection->getMethod('getPullRequestBody');
        $method->setAccessible(true);

        $styleBody = $method->invoke($this->command, 'style');
        $analysisBody = $method->invoke($this->command, 'analysis');
        $customBody = $method->invoke($this->command, 'custom');

        $this->assertStringContainsString('Laravel Pint', $styleBody);
        $this->assertStringContainsString('PHPStan baseline', $analysisBody);
        $this->assertStringContainsString('custom fixes', $customBody);
    }

    public function test_process_service_is_injected_correctly()
    {
        $reflection = new \ReflectionClass($this->command);
        $property = $reflection->getProperty('processService');
        $property->setAccessible(true);

        $this->assertSame($this->processService, $property->getValue($this->command));
    }

    public function test_command_extends_console_command()
    {
        $this->assertInstanceOf(\Illuminate\Console\Command::class, $this->command);
    }

    public function test_command_has_correct_signature_property()
    {
        $reflection = new \ReflectionClass($this->command);
        $property = $reflection->getProperty('signature');
        $property->setAccessible(true);

        $this->assertEquals('agent:propose-fix {--type=style : The type of issue to fix (e.g., style, analysis)}', $property->getValue($this->command));
    }

    public function test_command_has_correct_description_property()
    {
        $reflection = new \ReflectionClass($this->command);
        $property = $reflection->getProperty('description');
        $property->setAccessible(true);

        $this->assertEquals('Propose automated fixes via Pull Request for different types of issues', $property->getValue($this->command));
    }

    /**
     * Create a mock ProcessResult object
     */
    private function createMockProcessResult(bool $failed, string $output): ProcessResult
    {
        $result = $this->createMock(ProcessResult::class);
        $result->method('failed')->willReturn($failed);
        $result->method('output')->willReturn($output);
        $result->method('errorOutput')->willReturn($failed ? 'Error: '.$output : '');

        return $result;
    }
}
