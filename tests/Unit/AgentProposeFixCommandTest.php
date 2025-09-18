<?php

namespace Tests\Unit;

use App\Console\Commands\AgentProposeFixCommand;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AgentProposeFixCommandTest extends TestCase
{
    private AgentProposeFixCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $processService = $this->createMock(\App\Services\ProcessService::class);
        $this->command = new AgentProposeFixCommand($processService);
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

    public function test_handle_method_accepts_no_parameters()
    {
        $reflection = new \ReflectionClass($this->command);
        $method = $reflection->getMethod('handle');
        $parameters = $method->getParameters();

        $this->assertCount(0, $parameters);
    }

    public function test_create_branch_method_accepts_only_branch_name()
    {
        $reflection = new \ReflectionClass($this->command);
        $method = $reflection->getMethod('createBranch');
        $parameters = $method->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('branchName', $parameters[0]->getName());
    }

    public function test_run_fixer_method_accepts_only_type()
    {
        $reflection = new \ReflectionClass($this->command);
        $method = $reflection->getMethod('runFixer');
        $parameters = $method->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('type', $parameters[0]->getName());
    }

    public function test_run_style_fixer_method_accepts_no_parameters()
    {
        $reflection = new \ReflectionClass($this->command);
        $method = $reflection->getMethod('runStyleFixer');
        $parameters = $method->getParameters();

        $this->assertCount(0, $parameters);
    }

    public function test_run_analysis_fixer_method_accepts_no_parameters()
    {
        $reflection = new \ReflectionClass($this->command);
        $method = $reflection->getMethod('runAnalysisFixer');
        $parameters = $method->getParameters();

        $this->assertCount(0, $parameters);
    }

    public function test_stage_changes_method_accepts_no_parameters()
    {
        $reflection = new \ReflectionClass($this->command);
        $method = $reflection->getMethod('stageChanges');
        $parameters = $method->getParameters();

        $this->assertCount(0, $parameters);
    }

    public function test_commit_changes_method_accepts_only_type()
    {
        $reflection = new \ReflectionClass($this->command);
        $method = $reflection->getMethod('commitChanges');
        $parameters = $method->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('type', $parameters[0]->getName());
    }

    public function test_push_branch_method_accepts_only_branch_name()
    {
        $reflection = new \ReflectionClass($this->command);
        $method = $reflection->getMethod('pushBranch');
        $parameters = $method->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('branchName', $parameters[0]->getName());
    }

    public function test_create_pull_request_method_accepts_branch_name_and_type()
    {
        $reflection = new \ReflectionClass($this->command);
        $method = $reflection->getMethod('createPullRequest');
        $parameters = $method->getParameters();

        $this->assertCount(2, $parameters);
        $this->assertEquals('branchName', $parameters[0]->getName());
        $this->assertEquals('type', $parameters[1]->getName());
    }
}