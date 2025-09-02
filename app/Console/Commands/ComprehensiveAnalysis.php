<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ComprehensiveAnalysis extends Command
{
    protected $signature = 'agent:analyze';
    protected $description = 'Runs a comprehensive analysis of the codebase using various tools.';
    private $phpPath;

    public function __construct()
    {
        parent::__construct();
        // Find the PHP executable path once and reuse it.
        $this->phpPath = (new PhpExecutableFinder())->find(false);
        if (!$this->phpPath) {
            throw new \RuntimeException('PHP executable not found.');
        }
    }

    public function handle()
    {
        $this->info('🚀 Starting Comprehensive Analysis...');

        $this->runPint();
        $this->runLarastan();
        $this->runPest();

        $this->info('✅ Comprehensive Analysis Finished Successfully.');
        return self::SUCCESS;
    }

    private function runPint()
    {
        $this->runTool(
            '🎨 Running Laravel Pint...',
            ['vendor/bin/pint', '--test', '--verbose'],
            function (ProcessFailedException $exception) {
                // Pint returns exit code 1 for style issues, which is not a fatal error.
                if ($exception->getProcess()->getExitCode() === 1) {
                    $this->warn('⚠️  Laravel Pint found style issues to fix.');
                    $this->line($exception->getProcess()->getOutput());
                    return; // Continue to the next tool
                }
                // For any other error, treat it as fatal.
                throw $exception;
            }
        );
    }

    private function runLarastan()
    {
        $this->runTool(
            '🔬 Running Larastan...',
            ['vendor/bin/phpstan', 'analyse', '-l', '5', '--memory-limit=1G']
        );
    }

    private function runPest()
    {
        $this->runTool(
            '🧪 Running Pest tests...',
            ['vendor/bin/pest', '--parallel']
        );
    }

    private function runTool(string $message, array $command, ?callable $failureHandler = null)
    {
        $this->line('');
        $this->info($message);

        // Prepend the PHP executable to the command.
        // This is the most robust way to run Composer bin scripts on any OS.
        $fullCommand = array_merge([$this->phpPath], $command);

        $process = new Process($fullCommand, base_path());
        $process->setTimeout(3600);

        try {
            $process->mustRun(function ($type, $buffer) {
                $this->output->write($buffer);
            });
            $this->info('✅ Tool finished successfully.');
        } catch (ProcessFailedException $exception) {
            if ($failureHandler) {
                $failureHandler($exception);
            } else {
                $this->error('❌ A fatal error occurred during: ' . $message);
                $this->error($exception->getProcess()->getErrorOutput());
                exit(self::FAILURE);
            }
        }
    }
}
