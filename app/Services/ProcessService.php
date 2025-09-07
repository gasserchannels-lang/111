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
    public function run(string|array $command): ProcessResult
    {
        return $this->processFactory->run($command);
    }
}
