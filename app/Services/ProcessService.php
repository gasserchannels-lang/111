<?php

namespace App\Services;

use Illuminate\Process\ProcessResult;
use Illuminate\Process\Factory;

class ProcessService
{
    /**
     * The process factory instance.
     *
     * @var Factory
     */
    private Factory $processFactory;

    /**
     * Create a new process service instance.
     *
     * @param Factory $processFactory
     */
    public function __construct(Factory $processFactory)
    {
        $this->processFactory = $processFactory;
    }

    /**
     * Run a process command.
     *
     * @param string|array $command
     * @return ProcessResult
     */
    public function run(string|array $command): ProcessResult
    {
        return $this->processFactory->run($command);
    }
}
