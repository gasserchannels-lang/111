<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CentralizedLoggingService
{
    private const LOG_CHANNELS = [
        'application' => 'single',
        'security' => 'daily',
        'performance' => 'daily',
        'errors' => 'daily',
        'audit' => 'daily',
        'api' => 'daily',
        'database' => 'daily',
        'queue' => 'daily',
        'mail' => 'daily',
        'cache' => 'daily',
    ];

    private const LOG_LEVELS = [
        'emergency' => 0,
        'alert' => 1,
        'critical' => 2,
        'error' => 3,
        'warning' => 4,
        'notice' => 5,
        'info' => 6,
        'debug' => 7,
    ];

    private const SENSITIVE_FIELDS = [
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'token',
        'api_key',
        'secret',
        'private_key',
        'credit_card',
        'cvv',
        'ssn',
        'social_security_number',
        'phone',
        'email',
        'address',
        'ip_address',
        'user_agent',
    ];

    private const LOG_ROTATION_DAYS = 30;

    private const LOG_COMPRESSION_DAYS = 7;

    private const MAX_LOG_SIZE = 10485760; // 10MB

    /**
     * Log application event.
     */
    /**
     * @param  array<string, mixed>  $context
     */
    public function logApplication(string $level, string $message, array $context = []): void
    {
        $this->log('application', $level, $message, $context);
    }

    /**
     * Log security event.
     */
    /**
     * @param  array<string, mixed>  $context
     */
    public function logSecurity(string $level, string $message, array $context = []): void
    {
        $this->log('security', $level, $message, $context);
    }

    /**
     * Log performance event.
     */
    /**
     * @param  array<string, mixed>  $context
     */
    public function logPerformance(string $level, string $message, array $context = []): void
    {
        $this->log('performance', $level, $message, $context);
    }

    /**
     * Log error event.
     */
    /**
     * @param  array<string, mixed>  $context
     */
    public function logError(string $level, string $message, array $context = []): void
    {
        $this->log('errors', $level, $message, $context);
    }

    /**
     * Log audit event.
     */
    /**
     * @param  array<string, mixed>  $context
     */
    public function logAudit(string $level, string $message, array $context = []): void
    {
        $this->log('audit', $level, $message, $context);
    }

    /**
     * Log API event.
     */
    /**
     * @param  array<string, mixed>  $context
     */
    public function logApi(string $level, string $message, array $context = []): void
    {
        $this->log('api', $level, $message, $context);
    }

    /**
     * Log database event.
     */
    /**
     * @param  array<string, mixed>  $context
     */
    public function logDatabase(string $level, string $message, array $context = []): void
    {
        $this->log('database', $level, $message, $context);
    }

    /**
     * Log queue event.
     */
    /**
     * @param  array<string, mixed>  $context
     */
    public function logQueue(string $level, string $message, array $context = []): void
    {
        $this->log('queue', $level, $message, $context);
    }

    /**
     * Log mail event.
     */
    /**
     * @param  array<string, mixed>  $context
     */
    public function logMail(string $level, string $message, array $context = []): void
    {
        $this->log('mail', $level, $message, $context);
    }

    /**
     * Log cache event.
     */
    /**
     * @param  array<string, mixed>  $context
     */
    public function logCache(string $level, string $message, array $context = []): void
    {
        $this->log('cache', $level, $message, $context);
    }

    /**
     * Central logging method.
     */
    /**
     * @param  array<string, mixed>  $context
     */
    private function log(string $channel, string $level, string $message, array $context = []): void
    {
        // Validate log level
        if (! array_key_exists($level, self::LOG_LEVELS)) {
            $level = 'info';
        }

        // Filter sensitive data
        $context = $this->filterSensitiveData($context);

        // Add metadata
        $context = $this->addMetadata($context);

        // Log to specific channel
        Log::channel($channel)->$level($message, $context);

        // Store in cache for real-time monitoring
        $this->storeInCache($channel, $level, $message, $context);

        // Check for log rotation
        $this->checkLogRotation($channel);
    }

    /**
     * Filter sensitive data from context.
     */
    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function filterSensitiveData(array $context): array
    {
        foreach ($context as $key => $value) {
            if (is_array($value)) {
                $context[$key] = $this->filterSensitiveData($value);
            } elseif (is_string($value) && $this->isSensitiveField($key)) {
                $context[$key] = $this->maskSensitiveData($value);
            }
        }

        return $context;
    }

    /**
     * Check if field is sensitive.
     */
    private function isSensitiveField(string $field): bool
    {
        $field = strtolower($field);

        foreach (self::SENSITIVE_FIELDS as $sensitiveField) {
            if (str_contains($field, $sensitiveField)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Mask sensitive data.
     */
    private function maskSensitiveData(string $value): string
    {
        $length = strlen($value);

        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        return substr($value, 0, 2).str_repeat('*', $length - 4).substr($value, -2);
    }

    /**
     * Add metadata to log context.
     */
    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function addMetadata(array $context): array
    {
        return array_merge($context, [
            'timestamp' => now()->toISOString(),
            'request_id' => request()->header('X-Request-ID', uniqid()),
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->url(),
            'method' => request()->method(),
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
            'execution_time' => microtime(true) - LARAVEL_START,
        ]);
    }

    /**
     * Store log in cache for real-time monitoring.
     */
    /**
     * @param  array<string, mixed>  $context
     */
    private function storeInCache(string $channel, string $level, string $message, array $context): void
    {
        $key = "logs:{$channel}:recent";
        $logEntry = [
            'channel' => $channel,
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'timestamp' => now()->toISOString(),
        ];

        $recentLogs = Cache::get($key, []);
        if (is_array($recentLogs)) {
            $recentLogs[] = $logEntry;

            // Keep only last 100 entries
            if (count($recentLogs) > 100) {
                $recentLogs = array_slice($recentLogs, -100);
            }
        } else {
            $recentLogs = [$logEntry];
        }

        Cache::put($key, $recentLogs, now()->addHours(1));
    }

    /**
     * Check and perform log rotation.
     */
    private function checkLogRotation(string $channel): void
    {
        $logPath = storage_path("logs/{$channel}.log");

        if (! file_exists($logPath)) {
            return;
        }

        $fileSize = filesize($logPath);

        if ($fileSize > self::MAX_LOG_SIZE) {
            $this->rotateLog($channel, $logPath);
        }
    }

    /**
     * Rotate log file.
     */
    private function rotateLog(string $channel, string $logPath): void
    {
        $timestamp = now()->format('Y-m-d-H-i-s');
        $rotatedPath = storage_path("logs/{$channel}-{$timestamp}.log");

        // Move current log to rotated file
        rename($logPath, $rotatedPath);

        // Compress old log if it's older than compression days
        if (filemtime($rotatedPath) < now()->subDays(self::LOG_COMPRESSION_DAYS)->timestamp) {
            $this->compressLog($rotatedPath);
        }

        // Clean up old logs
        $this->cleanupOldLogs($channel);
    }

    /**
     * Compress log file.
     */
    private function compressLog(string $logPath): void
    {
        $compressedPath = $logPath.'.gz';

        if (function_exists('gzopen')) {
            $fp = gzopen($compressedPath, 'w9');
            if ($fp !== false) {
                $content = file_get_contents($logPath);
                if ($content !== false) {
                    gzwrite($fp, $content);
                }
                gzclose($fp);
            }

            unlink($logPath);
        }
    }

    /**
     * Clean up old log files.
     */
    private function cleanupOldLogs(string $channel): void
    {
        $logDir = storage_path('logs');
        $pattern = "{$logDir}/{$channel}-*.log*";
        $files = glob($pattern);
        if ($files === false) {
            $files = [];
        }

        $cutoffDate = now()->subDays(self::LOG_ROTATION_DAYS);

        foreach ($files as $file) {
            if (filemtime($file) < $cutoffDate->timestamp) {
                unlink($file);
            }
        }
    }

    /**
     * Get recent logs from cache.
     */
    /**
     * @return list<array<string, mixed>>
     */
    public function getRecentLogs(?string $channel = null, int $limit = 50): array
    {
        if ($channel) {
            $key = "logs:{$channel}:recent";

            return Cache::get($key, []);
        }

        $allLogs = [];
        foreach (array_keys(self::LOG_CHANNELS) as $channelName) {
            $key = "logs:{$channelName}:recent";
            $channelLogs = Cache::get($key, []);
            if (is_array($channelLogs)) {
                $allLogs = array_merge($allLogs, $channelLogs);
            }
        }

        // Sort by timestamp
        usort($allLogs, function ($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });

        return array_slice($allLogs, 0, $limit);
    }

    /**
     * Get log statistics.
     */
    /**
     * @return array<string, mixed>
     */
    public function getLogStatistics(): array
    {
        $stats = [];

        foreach (array_keys(self::LOG_CHANNELS) as $channel) {
            $key = "logs:{$channel}:recent";
            $logs = Cache::get($key, []);

            $levelCounts = [];
            foreach ($logs as $log) {
                $level = $log['level'];
                $levelCounts[$level] = ($levelCounts[$level] ?? 0) + 1;
            }

            $stats[$channel] = [
                'total' => count($logs),
                'levels' => $levelCounts,
                'last_log' => ! empty($logs) ? end($logs)['timestamp'] : null,
            ];
        }

        return $stats;
    }

    /**
     * Search logs.
     */
    /**
     * @return list<array<string, mixed>>
     */
    public function searchLogs(string $query, ?string $channel = null, ?string $level = null, int $limit = 100): array
    {
        $logs = $this->getRecentLogs($channel, 1000);
        $results = [];

        foreach ($logs as $log) {
            // Filter by level
            if ($level && $log['level'] !== $level) {
                continue;
            }

            // Search in message and context
            $searchText = strtolower($log['message'].' '.json_encode($log['context']));

            if (str_contains($searchText, strtolower($query))) {
                $results[] = $log;
            }

            if (count($results) >= $limit) {
                break;
            }
        }

        return $results;
    }

    /**
     * Export logs.
     */
    public function exportLogs(?string $channel = null, string $format = 'json'): string
    {
        $logs = $this->getRecentLogs($channel, 1000);

        switch ($format) {
            case 'json':
                $result = json_encode($logs, JSON_PRETTY_PRINT);

                return $result !== false ? $result : '';
            case 'csv':
                return $this->exportToCsv($logs);
            case 'txt':
                return $this->exportToTxt($logs);
            default:
                $result = json_encode($logs, JSON_PRETTY_PRINT);

                return $result !== false ? $result : '';
        }
    }

    /**
     * Export logs to CSV.
     */
    /**
     * @param  list<array<string, mixed>>  $logs
     */
    private function exportToCsv(array $logs): string
    {
        $csv = "Timestamp,Channel,Level,Message,Context\n";

        foreach ($logs as $log) {
            $csv .= sprintf(
                "%s,%s,%s,%s,%s\n",
                $log['timestamp'],
                $log['channel'],
                $log['level'],
                str_replace(',', ';', $log['message']),
                str_replace(',', ';', json_encode($log['context']) ?: '')
            );
        }

        return $csv;
    }

    /**
     * Export logs to TXT.
     */
    /**
     * @param  list<array<string, mixed>>  $logs
     */
    private function exportToTxt(array $logs): string
    {
        $txt = '';

        foreach ($logs as $log) {
            $txt .= sprintf(
                "[%s] %s.%s: %s\nContext: %s\n\n",
                $log['timestamp'],
                $log['channel'],
                strtoupper($log['level']),
                $log['message'],
                json_encode($log['context'], JSON_PRETTY_PRINT)
            );
        }

        return $txt;
    }

    /**
     * Clear logs.
     */
    public function clearLogs(?string $channel = null): void
    {
        if ($channel) {
            $key = "logs:{$channel}:recent";
            Cache::forget($key);
        } else {
            foreach (array_keys(self::LOG_CHANNELS) as $channelName) {
                $key = "logs:{$channelName}:recent";
                Cache::forget($key);
            }
        }
    }

    /**
     * Get log configuration.
     */
    /**
     * @return array<string, mixed>
     */
    public function getLogConfiguration(): array
    {
        return [
            'channels' => self::LOG_CHANNELS,
            'levels' => self::LOG_LEVELS,
            'sensitive_fields' => self::SENSITIVE_FIELDS,
            'rotation_days' => self::LOG_ROTATION_DAYS,
            'compression_days' => self::LOG_COMPRESSION_DAYS,
            'max_log_size' => self::MAX_LOG_SIZE,
        ];
    }
}
