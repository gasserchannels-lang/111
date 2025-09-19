<?php

declare(strict_types=1);

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProcessHeavyOperation implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 300; // 5 minutes

    public int $tries = 3;

    public int $maxExceptions = 3;

    /**
     * Create a new job instance.
     */
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(private string $operation, private array $data = [], private ?int $userId = null) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $startTime = microtime(true);

        try {
            Log::info('Heavy operation started', [
                'operation' => $this->operation,
                'user_id' => $this->userId,
                'data' => $this->data,
            ]);

            // Update job status
            $this->updateJobStatus('processing');

            // Execute the operation
            $result = $this->executeOperation();

            // Update job status
            $this->updateJobStatus('completed', $result);

            $executionTime = microtime(true) - $startTime;

            Log::info('Heavy operation completed', [
                'operation' => $this->operation,
                'user_id' => $this->userId,
                'execution_time' => $executionTime,
                'result' => $result,
            ]);
        } catch (Exception $e) {
            $this->updateJobStatus('failed', ['error' => $e->getMessage()]);

            Log::error('Heavy operation failed', [
                'operation' => $this->operation,
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Execute the specific operation.
     */
    private function executeOperation(): mixed
    {
        return match ($this->operation) {
            'generate_report' => $this->generateReport(),
            'process_images' => $this->processImages(),
            'sync_data' => $this->syncData(),
            'send_bulk_notifications' => $this->sendBulkNotifications(),
            'update_statistics' => $this->updateStatistics(),
            'cleanup_old_data' => $this->cleanupOldData(),
            'export_data' => $this->exportData(),
            'import_data' => $this->importData(),
            default => throw new Exception("Unknown operation: {$this->operation}"),
        };
    }

    /**
     * Generate report.
     */
    /**
     * @return array<string, mixed>
     */
    private function generateReport(): array
    {
        $reportType = $this->data['type'] ?? 'general';
        $startDate = $this->data['start_date'] ?? now()->subMonth();
        $endDate = $this->data['end_date'] ?? now();

        // Simulate heavy report generation
        sleep(2);

        return [
            'type' => $reportType,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'generated_at' => now(),
            'status' => 'completed',
        ];
    }

    /**
     * Process images.
     */
    /**
     * @return array<string, mixed>
     */
    private function processImages(): array
    {
        $imageIds = is_array($this->data['image_ids'] ?? []) ? $this->data['image_ids'] : [];
        $processed = 0;

        if (is_array($imageIds)) {
            foreach ($imageIds as $imageId) {
                // Simulate image processing
                usleep(100000); // 100ms per image
                $processed++;
            }
        }

        return [
            'total_images' => is_array($imageIds) ? count($imageIds) : 0,
            'processed' => $processed,
            'status' => 'completed',
        ];
    }

    /**
     * Sync data.
     */
    /**
     * @return array<string, mixed>
     */
    private function syncData(): array
    {
        $source = $this->data['source'] ?? 'external_api';
        $synced = 0;

        // Simulate data synchronization
        sleep(3);

        return [
            'source' => $source,
            'synced_records' => $synced,
            'status' => 'completed',
        ];
    }

    /**
     * Send bulk notifications.
     */
    /**
     * @return array<string, mixed>
     */
    private function sendBulkNotifications(): array
    {
        $userIds = is_array($this->data['user_ids'] ?? []) ? $this->data['user_ids'] : [];
        $message = is_string($this->data['message'] ?? '') ? $this->data['message'] : '';
        $sent = 0;

        if (is_array($userIds)) {
            foreach ($userIds as $userId) {
                // Simulate notification sending
                usleep(50000); // 50ms per notification
                $sent++;
            }
        }

        return [
            'total_users' => is_array($userIds) ? count($userIds) : 0,
            'sent' => $sent,
            'message' => $message,
            'status' => 'completed',
        ];
    }

    /**
     * Update statistics.
     */
    /**
     * @return array<string, mixed>
     */
    private function updateStatistics(): array
    {
        $statTypes = is_array($this->data['stat_types'] ?? []) ? $this->data['stat_types'] : ['users', 'products', 'orders'];
        $updated = 0;

        if (is_array($statTypes)) {
            foreach ($statTypes as $statType) {
                // Simulate statistics update
                usleep(200000); // 200ms per stat type
                $updated++;
            }
        }

        return [
            'stat_types' => $statTypes,
            'updated' => $updated,
            'status' => 'completed',
        ];
    }

    /**
     * Cleanup old data.
     */
    /**
     * @return array<string, mixed>
     */
    private function cleanupOldData(): array
    {
        $daysOld = $this->data['days_old'] ?? 30;
        $cleaned = 0;

        // Simulate data cleanup
        sleep(2);

        return [
            'days_old' => $daysOld,
            'cleaned_records' => $cleaned,
            'status' => 'completed',
        ];
    }

    /**
     * Export data.
     */
    /**
     * @return array<string, mixed>
     */
    private function exportData(): array
    {
        $format = is_string($this->data['format'] ?? '') ? $this->data['format'] : 'csv';
        $table = is_string($this->data['table'] ?? '') ? $this->data['table'] : 'products';

        // Simulate data export
        sleep(3);

        return [
            'format' => $format,
            'table' => $table,
            'file_path' => 'exports/'.(is_string($table) ? $table : 'unknown').'_'.(is_string($format) ? $format : 'csv').'_'.time().'.'.(is_string($format) ? $format : 'csv'),
            'status' => 'completed',
        ];
    }

    /**
     * Import data.
     */
    /**
     * @return array<string, mixed>
     */
    private function importData(): array
    {
        $filePath = $this->data['file_path'] ?? '';
        $imported = 0;

        // Simulate data import
        sleep(4);

        return [
            'file_path' => $filePath,
            'imported_records' => $imported,
            'status' => 'completed',
        ];
    }

    /**
     * Update job status.
     */
    private function updateJobStatus(string $status, mixed $result = null): void
    {
        $jobId = $this->job?->getJobId() ?? 'unknown';
        $cacheKey = "job_status:{$jobId}";

        $statusData = [
            'job_id' => $jobId,
            'operation' => $this->operation,
            'status' => $status,
            'user_id' => $this->userId,
            'updated_at' => now(),
            'result' => $result,
        ];

        Cache::put($cacheKey, $statusData, 3600); // Cache for 1 hour
    }

    /**
     * Handle job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error('Heavy operation job failed', [
            'operation' => $this->operation,
            'user_id' => $this->userId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        $this->updateJobStatus('failed', ['error' => $exception->getMessage()]);
    }

    /**
     * Get job status.
     */
    /**
     * @return array<string, mixed>|null
     */
    public static function getJobStatus(string $jobId): ?array
    {
        $cacheKey = "job_status:{$jobId}";

        return Cache::get($cacheKey);
    }

    /**
     * Get user's job statuses.
     */
    /**
     * @return array<string, mixed>
     */
    public static function getUserJobStatuses(int $userId): array
    {
        // This would require a more sophisticated implementation
        // to track jobs by user ID
        return [];
    }

    /**
     * Cancel job.
     */
    public function cancel(): bool
    {
        try {
            $this->delete();
            $this->updateJobStatus('cancelled');

            Log::info('Heavy operation cancelled', [
                'operation' => $this->operation,
                'user_id' => $this->userId,
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Failed to cancel heavy operation', [
                'operation' => $this->operation,
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
