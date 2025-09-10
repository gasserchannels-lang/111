<?php

namespace App\Services;

class ProcessResult
{
    /**
     * The exit code of the process.
     */
    public int $exitCode;

    /**
     * The output of the process.
     */
    public string $output;

    /**
     * The error output of the process.
     */
    public string $errorOutput;

    /**
     * Create a new process result instance.
     */
    public function __construct(int $exitCode, string $output, string $errorOutput)
    {
        $this->exitCode = $exitCode;
        $this->output = $output;
        $this->errorOutput = $errorOutput;
    }

    /**
     * Check if the process was successful.
     */
    public function successful(): bool
    {
        return $this->exitCode === 0;
    }

    /**
     * Check if the process failed.
     */
    public function failed(): bool
    {
        return ! $this->successful();
    }

    /**
     * Get the full output including errors.
     */
    public function getFullOutput(): string
    {
        return $this->output.($this->errorOutput ? "\n".$this->errorOutput : '');
    }
}
