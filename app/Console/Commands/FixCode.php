<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class FixCode extends Command
{
    protected $signature = 'agent:fix';

    protected $description = 'Automatically fixes code style issues using Laravel Pint.';

    private readonly string $phpPath;

    public function __construct()
    {
        parent::__construct();
        $phpPath = (new PhpExecutableFinder)->find(false);
        if (! $phpPath) {
            throw new RuntimeException('PHP executable not found.');
        }
        $this->phpPath = $phpPath;
    }

    public function handle(): int
    {
        $this->info('🚀 Starting Automatic Code Fixer...');

        $this->runTool(
            '🎨 Running Laravel Pint to fix files...',
            // Note: We run pint without '--test' to apply fixes.
            ['vendor/bin/pint', '--verbose']
        );

        $this->info('✅ Code fixing process finished.');

        return self::SUCCESS;
    }

    /**
     * @param  array<string>  $command
     */
    private function runTool(string $message, array $command): int
    {
        $this->line('');
        $this->info($message);

        $fullCommand = array_merge([$this->phpPath], $command);

        $process = new Process($fullCommand, base_path());
        $process->setTimeout(3600);

        try {
            $process->mustRun(function ($type, $buffer): void {
                if ($type === Process::OUT) {
                    $this->output->write($buffer);
                }
            });
            $this->info('✅ Tool finished successfully.');

            return self::SUCCESS;
        } catch (ProcessFailedException $exception) {
            $this->error('❌ A fatal error occurred during: '.$message);
            $this->error($exception->getProcess()->getErrorOutput());

            return self::FAILURE;
        }
    }
}
