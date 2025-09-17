<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class LogController extends Controller
{
    protected string $logPath;

    public function __construct()
    {
        $this->logPath = storage_path('logs/laravel.log');
    }

    /**
     * Get application logs.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            if (! File::exists($this->logPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Log file not found',
                ], 404);
            }

            $logs = File::get($this->logPath);
            $lines = explode("\n", $logs);
            $reversedLines = array_reverse($lines);

            // Filter by level if specified
            if ($request->has('level')) {
                $level = $request->get('level');
                $reversedLines = array_filter($reversedLines, fn($line): bool => str_contains((string) $line, (string) $level));
            }

            // Limit results
            $limit = $request->get('limit', 100);
            $reversedLines = array_slice($reversedLines, 0, $limit);

            return response()->json([
                'success' => true,
                'data' => $reversedLines,
                'message' => 'Logs retrieved successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving logs: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve logs',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear application logs.
     */
    public function clear(): JsonResponse
    {
        try {
            File::put($this->logPath, '');
            Log::info('Log file cleared by user: '.(auth()->id() ?? 'Guest'));

            return response()->json([
                'success' => true,
                'message' => 'Log file cleared successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error clearing log file: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to clear log file',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download log file.
     */
    public function download(): JsonResponse
    {
        try {
            if (! File::exists($this->logPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Log file not found',
                ], 404);
            }

            $filename = 'laravel_'.now()->format('Y-m-d_H-i-s').'.log';
            $downloadPath = storage_path('app/'.$filename);

            File::copy($this->logPath, $downloadPath);

            return response()->json([
                'success' => true,
                'message' => 'Log file prepared for download',
                'data' => [
                    'filename' => $filename,
                    'download_url' => url('storage/'.$filename),
                    'expires_at' => now()->addHours(24)->toISOString(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error preparing log download: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to prepare log download',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get log statistics.
     */
    public function getStatistics(): JsonResponse
    {
        try {
            if (! File::exists($this->logPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Log file not found',
                ], 404);
            }

            $logs = File::get($this->logPath);
            $lines = explode("\n", $logs);

            $stats = [
                'total_lines' => count($lines),
                'error_count' => $this->countLogLevel($lines, 'ERROR'),
                'warning_count' => $this->countLogLevel($lines, 'WARNING'),
                'info_count' => $this->countLogLevel($lines, 'INFO'),
                'debug_count' => $this->countLogLevel($lines, 'DEBUG'),
                'file_size' => File::size($this->logPath),
                'last_modified' => File::lastModified($this->logPath),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Log statistics retrieved successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting log statistics: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get log statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Parse log file with filters.
     *
     * @param  array<string>  $allowedLevels
     * @return array<string, mixed>
     */
    public function parseLogFile(array $allowedLevels = []): array
    {
        try {
            if (! File::exists($this->logPath)) {
                return [
                    'success' => false,
                    'message' => 'Log file not found',
                ];
            }

            $logs = File::get($this->logPath);
            if (empty($logs)) {
                return [
                    'success' => false,
                    'message' => 'Failed to read log file',
                ];
            }

            $lines = explode("\n", $logs);
            $reversedLines = array_reverse($lines);

            // Filter by allowed levels
            if ($allowedLevels !== []) {
                $reversedLines = array_filter($reversedLines, function ($line) use ($allowedLevels): bool {
                    foreach ($allowedLevels as $level) {
                        if (str_contains($line, $level)) {
                            return true;
                        }
                    }

                    return false;
                });
            }

            return [
                'success' => true,
                'data' => $reversedLines,
                'message' => 'Log file parsed successfully',
            ];
        } catch (\Exception $e) {
            Log::error('Error parsing log file: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to parse log file',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Parse access log file.
     *
     * @return array<string, mixed>
     */
    public function parseAccessLogFile(): array
    {
        try {
            $accessLogPath = storage_path('logs/access.log');

            if (! File::exists($accessLogPath)) {
                return [
                    'success' => false,
                    'message' => 'Access log file not found',
                ];
            }

            $logs = File::get($accessLogPath);
            if (empty($logs)) {
                return [
                    'success' => false,
                    'message' => 'Failed to read access log file',
                ];
            }

            $lines = explode("\n", $logs);
            $reversedLines = array_reverse($lines);

            return [
                'success' => true,
                'data' => $reversedLines,
                'message' => 'Access log file parsed successfully',
            ];
        } catch (\Exception $e) {
            Log::error('Error parsing access log file: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to parse access log file',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get recent errors.
     */
    public function getRecentErrors(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 50);
            $parsedLogs = $this->parseLogFile(['ERROR', 'CRITICAL', 'EMERGENCY']);

            if (! $parsedLogs['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $parsedLogs['message'],
                ], 500);
            }

            $errors = array_slice($parsedLogs['data'], 0, $limit);

            return response()->json([
                'success' => true,
                'data' => $errors,
                'message' => 'Recent errors retrieved successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting recent errors: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get recent errors',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get log levels.
     */
    public function getLogLevels(): JsonResponse
    {
        try {
            $levels = [
                'emergency' => 'Emergency',
                'alert' => 'Alert',
                'critical' => 'Critical',
                'error' => 'Error',
                'warning' => 'Warning',
                'notice' => 'Notice',
                'info' => 'Info',
                'debug' => 'Debug',
            ];

            return response()->json([
                'success' => true,
                'data' => $levels,
                'message' => 'Log levels retrieved successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting log levels: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get log levels',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export logs to file.
     *
     * @param  array<string, mixed>  $logs
     */
    public function exportLogsToFile(array $logs): JsonResponse
    {
        try {
            $filename = 'logs_export_'.now()->format('Y-m-d_H-i-s').'.csv';
            $filePath = storage_path('app/'.$filename);

            $file = fopen($filePath, 'w');
            if ($file === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create export file',
                ], 500);
            }

            // Write CSV header
            fputcsv($file, ['Timestamp', 'Level', 'Message']);

            // Write log data
            fputcsv($file, $logs);

            fclose($file);

            return response()->json([
                'success' => true,
                'message' => 'Logs exported successfully',
                'data' => [
                    'filename' => $filename,
                    'download_url' => url('storage/'.$filename),
                    'expires_at' => now()->addHours(24)->toISOString(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error exporting logs: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to export logs',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get audit logs for export.
     *
     * @return array<string, mixed>
     */
    public function getAuditLogsForExport(): array
    {
        // Placeholder for audit logs
        return [
            'success' => true,
            'data' => [],
            'message' => 'Audit logs retrieved successfully',
        ];
    }

    /**
     * Get system logs for export.
     *
     * @return array<string, mixed>
     */
    public function getSystemLogsForExport(): array
    {
        try {
            return $this->parseLogFile();
        } catch (\Exception $e) {
            Log::error('Error getting system logs: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to get system logs',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get access logs for export.
     *
     * @return array<string, mixed>
     */
    public function getAccessLogsForExport(): array
    {
        try {
            return $this->parseAccessLogFile();
        } catch (\Exception $e) {
            Log::error('Error getting access logs: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to get access logs',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Count log level occurrences.
     *
     * @param  array<string>  $lines
     */
    private function countLogLevel(array $lines, string $level): int
    {
        $count = 0;
        foreach ($lines as $line) {
            if (str_contains($line, $level)) {
                $count++;
            }
        }

        return $count;
    }
}
