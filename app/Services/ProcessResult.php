<?php

declare(strict_types=1);

namespace App\Services;

class ProcessResult
{
    /**
     * The exit code of the process.
     */
    private int $exitCode;

    /**
     * The output of the process.
     */
    private string $output;

    /**
     * The error output of the process.
     */
    private string $errorOutput;

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
     * Get the exit code of the process.
     */
    public function getExitCode(): int
    {
        return $this->exitCode;
    }

    /**
     * Get the output of the process.
     */
    public function getOutput(): string
    {
        return $this->output;
    }

    /**
     * Get the error output of the process.
     */
    public function getErrorOutput(): string
    {
        return $this->errorOutput;
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
