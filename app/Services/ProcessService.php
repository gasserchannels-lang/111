<?php

namespace App\Services;

use Illuminate\Process\Factory;
use Illuminate\Process\ProcessResult;

class ProcessService
{
    /**
     * The process factory instance.
     */
    private Factory $processFactory;

    /**
     * Create a new process service instance.
     */
    public function __construct(Factory $processFactory)
    {
        $this->processFactory = $processFactory;
    }

    /**
     * Run a process command.
     */
    public function run(string|array<string> $command): ProcessResult
    {
        $process = $this->processFactory->new($command);
        $process->run();
        
        return new ProcessResult(
            $process->getExitCode(),
            $process->getOutput(),
            $process->getErrorOutput()
        );
    }
}
