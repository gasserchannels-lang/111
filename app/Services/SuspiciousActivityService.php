<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

final class SuspiciousActivityService
{
    /**
     * @var array<string, mixed>
     */
    private array $config;

    public function __construct()
    {
        $this->config = config('suspicious_activity', [
            'enabled' => true,
            'monitoring_rules' => [
                'multiple_failed_logins' => [
                    'enabled' => true,
                    'threshold' => 5,
                    'time_window' => 15, // minutes
                    'severity' => 'high',
                ],
                'unusual_login_location' => [
                    'enabled' => true,
                    'severity' => 'medium',
                ],
                'rapid_api_requests' => [
                    'enabled' => true,
                    'threshold' => 100,
                    'time_window' => 5, // minutes
                    'severity' => 'medium',
                ],
                'unusual_data_access' => [
                    'enabled' => true,
                    'threshold' => 1000,
                    'time_window' => 60, // minutes
                    'severity' => 'high',
                ],
                'admin_actions' => [
                    'enabled' => true,
                    'severity' => 'high',
                ],
            ],
            'notification' => [
                'email' => true,
                'slack' => false,
                'webhook' => false,
            ],
        ]);
    }

    /**
     * Monitor user activity.
     *
     * @param  array<string, mixed>  $data
     */
    public function monitorActivity(string $event, array $data): void
    {
        if (! $this->config['enabled']) {
            return;
        }

        try {
            $userId = $data['user_id'] ?? null;
            $ipAddress = $data['ip_address'] ?? null;
            $location = $data['location'] ?? null;

            // Check for suspicious patterns
            $suspiciousActivities = [];

            // Multiple failed logins
            if ($event === 'login_failed' && $userId) {
                $suspiciousActivities = array_merge($suspiciousActivities, $this->checkMultipleFailedLogins($userId, $ipAddress));
            }

            // Unusual login location
            if ($event === 'login_success' && $userId && $location) {
                $suspiciousActivities = array_merge($suspiciousActivities, $this->checkUnusualLoginLocation($userId, $location, $ipAddress));
            }

            // Rapid API requests
            if ($event === 'api_request' && $userId) {
                $suspiciousActivities = array_merge($suspiciousActivities, $this->checkRapidApiRequests($userId, $ipAddress));
            }

            // Unusual data access
            if ($event === 'data_access' && $userId) {
                $suspiciousActivities = array_merge($suspiciousActivities, $this->checkUnusualDataAccess($userId, $data));
            }

            // Admin actions
            if ($event === 'admin_action' && $userId) {
                $suspiciousActivities = array_merge($suspiciousActivities, $this->checkAdminActions($userId, $data));
            }

            // Process suspicious activities
            foreach ($suspiciousActivities as $activity) {
                $this->processSuspiciousActivity($activity);
            }
        } catch (Exception $e) {
            Log::error('Suspicious activity monitoring failed', [
                'event' => $event,
                'data' => $data,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Check for multiple failed logins.
     *
     * @return list<array<string, mixed>>
     */
    private function checkMultipleFailedLogins(int $userId, string $ipAddress): array
    {
        $rule = $this->config['monitoring_rules']['multiple_failed_logins'];

        if (! $rule['enabled']) {
            return [];
        }

        $key = "failed_logins:{$userId}:{$ipAddress}";
        $failedCount = Cache::get($key, 0);
        $failedCount++;

        Cache::put($key, $failedCount, $rule['time_window'] * 60);

        if ($failedCount >= $rule['threshold']) {
            return [[
                'type' => 'multiple_failed_logins',
                'severity' => $rule['severity'],
                'user_id' => $userId,
                'ip_address' => $ipAddress,
                'details' => [
                    'failed_attempts' => $failedCount,
                    'time_window' => $rule['time_window'],
                ],
                'timestamp' => now()->toISOString(),
            ]];
        }

        return [];
    }

    /**
     * Check for unusual login location.
     *
     * @param  array<string, mixed>  $location
     * @return list<array<string, mixed>>
     */
    private function checkUnusualLoginLocation(int $userId, array $location, string $ipAddress): array
    {
        $rule = $this->config['monitoring_rules']['unusual_login_location'];

        if (! $rule['enabled']) {
            return [];
        }

        // Get user's previous login locations
        $previousLocations = $this->getUserPreviousLocations($userId);

        if (empty($previousLocations)) {
            return []; // No previous locations to compare
        }

        // Check if current location is significantly different
        $isUnusual = $this->isLocationUnusual($location, $previousLocations);

        if ($isUnusual) {
            return [[
                'type' => 'unusual_login_location',
                'severity' => $rule['severity'],
                'user_id' => $userId,
                'ip_address' => $ipAddress,
                'details' => [
                    'current_location' => $location,
                    'previous_locations' => $previousLocations,
                ],
                'timestamp' => now()->toISOString(),
            ]];
        }

        return [];
    }

    /**
     * Check for rapid API requests.
     *
     * @return list<array<string, mixed>>
     */
    private function checkRapidApiRequests(int $userId, string $ipAddress): array
    {
        $rule = $this->config['monitoring_rules']['rapid_api_requests'];

        if (! $rule['enabled']) {
            return [];
        }

        $key = "api_requests:{$userId}:{$ipAddress}";
        $requestCount = Cache::get($key, 0);
        $requestCount++;

        Cache::put($key, $requestCount, $rule['time_window'] * 60);

        if ($requestCount >= $rule['threshold']) {
            return [[
                'type' => 'rapid_api_requests',
                'severity' => $rule['severity'],
                'user_id' => $userId,
                'ip_address' => $ipAddress,
                'details' => [
                    'request_count' => $requestCount,
                    'time_window' => $rule['time_window'],
                ],
                'timestamp' => now()->toISOString(),
            ]];
        }

        return [];
    }

    /**
     * Check for unusual data access.
     *
     * @param  array<string, mixed>  $data
     * @return list<array<string, mixed>>
     */
    private function checkUnusualDataAccess(int $userId, array $data): array
    {
        $rule = $this->config['monitoring_rules']['unusual_data_access'];

        if (! $rule['enabled']) {
            return [];
        }

        $key = "data_access:{$userId}";
        $accessCount = Cache::get($key, 0);
        $accessCount++;

        Cache::put($key, $accessCount, $rule['time_window'] * 60);

        if ($accessCount >= $rule['threshold']) {
            return [[
                'type' => 'unusual_data_access',
                'severity' => $rule['severity'],
                'user_id' => $userId,
                'details' => [
                    'access_count' => $accessCount,
                    'time_window' => $rule['time_window'],
                    'data_type' => $data['data_type'] ?? 'unknown',
                ],
                'timestamp' => now()->toISOString(),
            ]];
        }

        return [];
    }

    /**
     * Check for admin actions.
     *
     * @param  array<string, mixed>  $data
     * @return list<array<string, mixed>>
     */
    private function checkAdminActions(int $userId, array $data): array
    {
        $rule = $this->config['monitoring_rules']['admin_actions'];

        if (! $rule['enabled']) {
            return [];
        }

        // Log all admin actions as potentially suspicious
        return [[
            'type' => 'admin_action',
            'severity' => $rule['severity'],
            'user_id' => $userId,
            'details' => [
                'action' => $data['action'] ?? 'unknown',
                'resource' => $data['resource'] ?? 'unknown',
                'changes' => $data['changes'] ?? [],
            ],
            'timestamp' => now()->toISOString(),
        ]];
    }

    /**
     * Process suspicious activity.
     *
     * @param  array<string, mixed>  $activity
     */
    private function processSuspiciousActivity(array $activity): void
    {
        try {
            // Log the activity
            Log::warning('Suspicious activity detected', $activity);

            // Store in database (if configured)
            $this->storeSuspiciousActivity($activity);

            // Send notifications
            $this->sendNotifications($activity);

            // Take automatic actions if needed
            $this->takeAutomaticActions($activity);
        } catch (Exception $e) {
            Log::error('Failed to process suspicious activity', [
                'activity' => $activity,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Store suspicious activity.
     *
     * @param  array<string, mixed>  $activity
     */
    private function storeSuspiciousActivity(array $activity): void
    {
        // This would store in a suspicious_activities table
        // For now, we'll just log it
        Log::info('Suspicious activity stored', $activity);
    }

    /**
     * Send notifications.
     *
     * @param  array<string, mixed>  $activity
     */
    private function sendNotifications(array $activity): void
    {
        $notification = $this->config['notification'];

        if ($notification['email']) {
            $this->sendEmailNotification($activity);
        }

        if ($notification['slack']) {
            $this->sendSlackNotification($activity);
        }

        if ($notification['webhook']) {
            $this->sendWebhookNotification($activity);
        }
    }

    /**
     * Send email notification.
     *
     * @param  array<string, mixed>  $activity
     */
    private function sendEmailNotification(array $activity): void
    {
        try {
            $adminEmails = config('app.admin_emails', []);

            if (! empty($adminEmails)) {
                $subject = "Suspicious Activity Alert - {$activity['type']}";
                $message = $this->formatActivityMessage($activity);

                Mail::raw($message, function ($mail) use ($adminEmails, $subject) {
                    $mail->to($adminEmails)->subject($subject);
                });
            }
        } catch (Exception $e) {
            Log::error('Failed to send email notification', [
                'activity' => $activity,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send Slack notification.
     *
     * @param  array<string, mixed>  $activity
     */
    private function sendSlackNotification(array $activity): void
    {
        try {
            $webhookUrl = config('suspicious_activity.slack_webhook_url');

            if ($webhookUrl) {
                // Send to Slack webhook
                // Implementation would go here
            }
        } catch (Exception $e) {
            Log::error('Failed to send Slack notification', [
                'activity' => $activity,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send webhook notification.
     *
     * @param  array<string, mixed>  $activity
     */
    private function sendWebhookNotification(array $activity): void
    {
        try {
            $webhookUrl = config('suspicious_activity.webhook_url');

            if ($webhookUrl) {
                // Send to webhook
                // Implementation would go here
            }
        } catch (Exception $e) {
            Log::error('Failed to send webhook notification', [
                'activity' => $activity,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Take automatic actions.
     *
     * @param  array<string, mixed>  $activity
     */
    private function takeAutomaticActions(array $activity): void
    {
        $severity = $activity['severity'];
        $type = $activity['type'];

        // High severity actions
        if ($severity === 'high') {
            switch ($type) {
                case 'multiple_failed_logins':
                    $this->lockUserAccount($activity['user_id']);
                    break;
                case 'unusual_data_access':
                    $this->suspendUserAccount($activity['user_id']);
                    break;
            }
        }

        // Medium severity actions
        if ($severity === 'medium') {
            switch ($type) {
                case 'rapid_api_requests':
                    $this->throttleUserRequests($activity['user_id']);
                    break;
            }
        }
    }

    /**
     * Lock user account.
     */
    private function lockUserAccount(int $userId): void
    {
        try {
            // This would update the users table
            Log::info('User account locked due to suspicious activity', [
                'user_id' => $userId,
                'timestamp' => now()->toISOString(),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to lock user account', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Suspend user account.
     */
    private function suspendUserAccount(int $userId): void
    {
        try {
            // This would update the users table
            Log::info('User account suspended due to suspicious activity', [
                'user_id' => $userId,
                'timestamp' => now()->toISOString(),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to suspend user account', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Throttle user requests.
     */
    private function throttleUserRequests(int $userId): void
    {
        try {
            // This would add user to throttling list
            Log::info('User requests throttled due to suspicious activity', [
                'user_id' => $userId,
                'timestamp' => now()->toISOString(),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to throttle user requests', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Format activity message.
     *
     * @param  array<string, mixed>  $activity
     */
    private function formatActivityMessage(array $activity): string
    {
        $message = "Suspicious Activity Detected\n\n";
        $message .= "Type: {$activity['type']}\n";
        $message .= "Severity: {$activity['severity']}\n";
        $message .= "User ID: {$activity['user_id']}\n";
        $message .= "IP Address: {$activity['ip_address']}\n";
        $message .= "Timestamp: {$activity['timestamp']}\n\n";
        $message .= "Details:\n";
        $message .= json_encode($activity['details'], JSON_PRETTY_PRINT);

        return $message;
    }

    /**
     * Get user's previous login locations.
     *
     * @return array<string, mixed>
     */
    private function getUserPreviousLocations(int $userId): array
    {
        // This would query the login_logs table
        // For now, return empty array
        return [];
    }

    /**
     * Check if location is unusual.
     *
     * @param  array<string, mixed>  $currentLocation
     * @param  array<string, mixed>  $previousLocations
     */
    private function isLocationUnusual(array $currentLocation, array $previousLocations): bool
    {
        // Simple distance-based check
        // In production, this would use more sophisticated geolocation analysis

        $currentLat = $currentLocation['latitude'] ?? 0;
        $currentLng = $currentLocation['longitude'] ?? 0;

        foreach ($previousLocations as $location) {
            $prevLat = $location['latitude'] ?? 0;
            $prevLng = $location['longitude'] ?? 0;

            $distance = $this->calculateDistance($currentLat, $currentLng, $prevLat, $prevLng);

            // If distance is less than 100km, it's not unusual
            if ($distance < 100) {
                return false;
            }
        }

        return true;
    }

    /**
     * Calculate distance between two coordinates.
     */
    private function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Get suspicious activity statistics.
     *
     * @return array<string, mixed>
     */
    public function getStatistics(): array
    {
        // This would query the suspicious_activities table
        // For now, return mock data
        return [
            'total_activities' => 0,
            'activities_by_type' => [],
            'activities_by_severity' => [],
            'recent_activities' => [],
        ];
    }
}
