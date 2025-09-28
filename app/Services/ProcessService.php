<?php

namespace App\Services;

use App\DTO\ProcessResult;
use Illuminate\Support\Facades\Process;

class ProcessService
{
    /**
     * Processing metrics.
     *
     * @var array<string, int>
     */
    private array $metrics = [
        'processed_count' => 0,
        'error_count' => 0,
    ];

    /**
     * Validation errors.
     *
     * @var array<string, mixed>
     */
    private array $errors = [];

    /**
     * Processing status.
     */
    private string $status = 'idle';

    /**
     * Create a new process service instance.
     */
    public function __construct()
    {
        // Constructor for future configuration if needed
    }

    /**
     * Run a process command.
     *
     * @param  array<string>|string  $command
     */
    public function run(string|array $command): ProcessResult
    {
        // Set timeout for long-running commands like Pint
        $timeout = 300; // 5 minutes
        if (is_string($command) && str_contains($command, 'pint')) {
            $timeout = 300;
        }

        $result = Process::timeout($timeout)->run($command);

        // For git commands, stderr often contains success messages, not errors
        $isGitCommand = is_string($command) && str_starts_with($command, 'git');

        if ($isGitCommand) {
            // For git commands, check if the stderr contains success messages
            $errorOutput = $result->errorOutput();
            $isSuccessMessage = str_contains($errorOutput, 'Switched to a new branch') ||
                str_contains($errorOutput, 'Branch created') ||
                str_contains($errorOutput, 'successfully');

            if ($isSuccessMessage) {
                // Treat stderr as output for successful git commands, and force success
                $output = $result->output() ?: $errorOutput;
                $errorOutput = '';
                $exitCode = 0; // Force success for git commands with success messages
            } else {
                $output = $result->output();
                $exitCode = $result->exitCode() ?? 0;
            }
        } else {
            $output = $result->output();
            $errorOutput = $result->errorOutput();
            $exitCode = $result->exitCode() ?? 0;
        }

        return new ProcessResult(
            $exitCode,
            $output,
            $errorOutput
        );
    }

    /**
     * Process data.
     *
     * @param  array<string, mixed>|null  $data
     * @return array<string, mixed>
     */
    public function process(?array $data): array
    {
        try {
            $this->status = 'processing';

            if ($data === null || $data === []) {
                return ['error' => true, 'message' => 'Invalid data provided'];
            }

            $cleanedData = $this->clean($data);
            $validated = $this->validate($cleanedData);

            if (! $validated) {
                $this->metrics['error_count']++;

                return ['error' => true, 'message' => 'Validation failed', 'errors' => $this->getErrors()];
            }

            $transformedData = $this->transform($cleanedData);
            $this->metrics['processed_count']++;
            $this->status = 'completed';

            return ['processed' => true, 'data' => $transformedData];
        } catch (\Exception $e) {
            $this->metrics['error_count']++;
            $this->status = 'error';

            return ['error' => true, 'message' => $e->getMessage()];
        }
    }

    /**
     * Validate data.
     *
     * @param  array<string, mixed>  $data
     */
    public function validate(array $data): bool
    {
        $this->errors = [];

        if (isset($data['name']) && empty($data['name'])) {
            $this->errors['name'] = 'Name is required';
        }

        if (isset($data['email']) && ! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'Email is invalid';
        }

        return $this->errors === [];
    }

    /**
     * Get validation errors.
     *
     * @return array<string, mixed>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Clean data.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function clean(array $data): array
    {
        $cleaned = [];

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $cleaned[$key] = trim($value);

                if ($key === 'email') {
                    $cleaned[$key] = strtolower($cleaned[$key]);
                }
            } else {
                $cleaned[$key] = $value;
            }
        }

        return $cleaned;
    }

    /**
     * Transform data.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function transform(array $data): array
    {
        $transformed = [];

        foreach ($data as $key => $value) {
            $transformed[$key] = is_string($value) ? ucfirst($value) : $value;
        }

        return $transformed;
    }

    /**
     * Get processing status.
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Reset service.
     */
    public function reset(): void
    {
        $this->errors = [];
        $this->status = 'idle';
        $this->metrics = [
            'processed_count' => 0,
            'error_count' => 0,
        ];
    }

    /**
     * Get processing metrics.
     *
     * @return array<string, int>
     */
    public function getMetrics(): array
    {
        return $this->metrics;
    }
}
