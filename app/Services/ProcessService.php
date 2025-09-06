<?php

namespace App\Services;

use Illuminate\Support\Facades\Process;

class ProcessService
{
    /**
     * Run a process command.
     */
    public function run(string|array $command): \Illuminate\Process\ProcessResult
    {
        return Process::run($command);
    }
}
