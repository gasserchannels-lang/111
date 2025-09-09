<?php

namespace App\Services;

use Illuminate\Process\Factory;
use Illuminate\Support\Facades\Process;

class ProcessService
{
    /**
     * The process factory instance.
     */
    private Factory $processFactory;

    /**
     * Processing metrics.
     */
    private array $metrics = [
        'processed_count' => 0,
        'error_count' => 0,
    ];

    /**
     * Validation errors.
     */
    private array $errors = [];

    /**
     * Processing status.
     */
    private string $status = 'idle';

    /**
     * Create a new process service instance.
     */
    public function __construct(Factory $processFactory = null)
    {
        $this->processFactory = $processFactory ?? Process::getFacadeRoot();
    }

    /**
     * Run a process command.
     */
    public function run(string|array $command): ProcessResult
    {
        $process = $this->processFactory->new($command);
        $process->run();

        return new ProcessResult(
            $process->getExitCode(),
            $process->getOutput(),
            $process->getErrorOutput()
        );
    }

    /**
     * Process data.
     */
    public function process($data): array
    {
        try {
            $this->status = 'processing';

            if ($data === null || empty($data)) {
                return ['error' => true, 'message' => 'Invalid data provided'];
            }

            $cleanedData = $this->clean($data);
            $validated = $this->validate($cleanedData);

            if (!$validated) {
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
     */
    public function validate(array $data): bool
    {
        $this->errors = [];

        if (isset($data['name']) && empty($data['name'])) {
            $this->errors['name'] = 'Name is required';
        }

        if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'Email is invalid';
        }

        return empty($this->errors);
    }

    /**
     * Get validation errors.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Clean data.
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
     */
    public function transform(array $data): array
    {
        $transformed = [];

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $transformed[$key] = ucfirst($value);
            } else {
                $transformed[$key] = $value;
            }
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
     */
    public function getMetrics(): array
    {
        return $this->metrics;
    }
}
