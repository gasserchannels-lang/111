<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ComprehensiveAnalysis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'agent:analyze';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs a comprehensive analysis of the codebase using various tools.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting Comprehensive Analysis...');

        // --- 1. Composer Audit (Security) ---
        $this->runComposerAudit();

        // --- 2. Laravel Pint (Code Style) ---
        $this->runPint();

        // --- 3. Larastan / PHPStan (Static Analysis) ---
        $this->runPhpstan();

        // --- 4. Pest (Testing) ---
        $this->runPest();

        $this->info('✅ Comprehensive Analysis Finished Successfully.');

        return self::SUCCESS;
    }

    /**
     * Run Composer Audit for security vulnerabilities.
     */
    private function runComposerAudit()
    {
        $this->line('');
        $this->info('🛡️ Running Composer Audit for security vulnerabilities...');

        $process = new Process(['composer', 'audit']);
        $process->setTimeout(3600);

        try {
            $process->mustRun(function ($type, $buffer) {
                $this->output->write($buffer);
            });
            $this->info('✅ Composer Audit found no security issues.');
        } catch (ProcessFailedException $exception) {
            $process = $exception->getProcess();
            $processOutput = $process->getOutput();
            $errorOutput = $process->getErrorOutput();

            // --- START DEBUGGING ---
            $this->line('');
            $this->warn('--- DEBUGGING COMPOSER AUDIT ---');
            $this->line('Exit Code: ' . $process->getExitCode());
            $this->line('--- Standard Output (STDOUT) ---');
            $this->line($processOutput);
            $this->line('--- Error Output (STDERR) ---');
            $this->line($errorOutput);
            $this->warn('--- END DEBUGGING ---');
            $this->line('');
            // --- END DEBUGGING ---

            // Check if the failure is due to abandoned packages, which is not a critical error for us.
            // We check both STDOUT and STDERR to be safe.
            if (str_contains($processOutput, 'Found abandoned packages') || str_contains($errorOutput, 'Found abandoned packages')) {
                $this->warn('⚠️  Composer Audit found abandoned packages. This is a non-critical warning. Continuing...');
            } else {
                // For any other error, we treat it as fatal.
                $this->error('❌ A fatal error occurred during: 🛡️ Running Composer Audit...');
                exit(self::FAILURE);
            }
        }
    }

    /**
     * Run the Pint process for code style.
     */
    private function runPint()
    {
        $this->line('');
        $this->info('🎨 Running Laravel Pint for code style...');

        $pintPath = $this->getToolPath('pint');
        $process = new Process([$pintPath, '--test', '--verbose']);
        $process->setTimeout(3600);

        try {
            $process->mustRun(function ($type, $buffer) {
                $this->output->write($buffer);
            });
            $this->info('✅ Laravel Pint found no style issues.');
        } catch (ProcessFailedException $exception) {
            if ($exception->getProcess()->getExitCode() === 1) {
                $this->warn('⚠️  Laravel Pint found style issues to fix.');
                $this->line($exception->getProcess()->getOutput());
            } else {
                $this->error('❌ A fatal error occurred during: 🎨 Running Laravel Pint...');
                $this->error($exception->getProcess()->getErrorOutput());
                exit(self::FAILURE);
            }
        }
    }

    /**
     * Run the PHPStan process for static analysis.
     */
    private function runPhpstan()
    {
        $this->line('');
        $this->info('🔬 Running Larastan for static analysis...');

        $phpstanPath = $this->getToolPath('phpstan');
        $process = new Process([$phpstanPath, 'analyse', '-l', '5', '--memory-limit=1G']);
        $process->setTimeout(3600);

        try {
            $process->mustRun(function ($type, $buffer) {
                $this->output->write($buffer);
            });
            $this->info('✅ Larastan found no issues.');
        } catch (ProcessFailedException $exception) {
            $this->error('❌ An error occurred during: 🔬 Running Larastan...');
            $this->error($exception->getProcess()->getErrorOutput());
            exit(self::FAILURE);
        }
    }

    /**
     * Run the Pest process for testing.
     */
    private function runPest()
    {
        $this->line('');
        $this->info('🧪 Running Pest for testing...');

        $pestPath = $this->getToolPath('pest');
        $process = new Process([$pestPath, '--parallel']);
        $process->setTimeout(3600);

        try {
            $process->mustRun(function ($type, $buffer) {
                $this->output->write($buffer);
            });
            $this->info('✅ Pest tests passed successfully.');
        } catch (ProcessFailedException $exception) {
            $this->error('❌ An error occurred during: 🧪 Running Pest...');
            $this->error($exception->getProcess()->getErrorOutput());
            exit(self::FAILURE);
        }
    }

    /**
     * Helper function to get the correct executable path for a tool.
     */
    private function getToolPath(string $toolName): string
    {
        $toolPath = base_path('vendor' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . $toolName);

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $toolPath .= '.bat';
        }

        return $toolPath;
    }
}
