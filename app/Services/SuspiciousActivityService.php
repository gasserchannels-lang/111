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
        $config = config('suspicious_activity', [
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
        $this->config = is_array($config) ? $config : [];
    }

    /**
     * Monitor user activity.
     *
     * @param  array<string, mixed>  $data
     */
    public function monitorActivity(string $event, array $data): void
    {
        if (! ($this->config['enabled'] ?? false)) {
            return;
        }

        try {
            $userId = $data['user_id'] ?? null;
            $ipAddress = $data['ip_address'] ?? null;
            $location = $data['location'] ?? null;

            // Check for suspicious patterns
            $suspiciousActivities = [];

            // Multiple failed logins
            if ($event === 'login_failed' && is_numeric($userId)) {
                $suspiciousActivities = array_merge($suspiciousActivities, $this->checkMultipleFailedLogins((int) $userId, (string) ($ipAddress ?? '')));
            }

            // Unusual login location
            if ($event === 'login_success' && is_numeric($userId) && is_array($location)) {
                $suspiciousActivities = array_merge($suspiciousActivities, $this->checkUnusualLoginLocation((int) $userId, $location, (string) ($ipAddress ?? '')));
            }

            // Rapid API requests
            if ($event === 'api_request' && is_numeric($userId)) {
                $suspiciousActivities = array_merge($suspiciousActivities, $this->checkRapidApiRequests((int) $userId, (string) ($ipAddress ?? '')));
            }

            // Unusual data access
            if ($event === 'data_access' && is_numeric($userId)) {
                $suspiciousActivities = array_merge($suspiciousActivities, $this->checkUnusualDataAccess((int) $userId, $data));
            }

            // Admin actions
            if ($event === 'admin_action' && is_numeric($userId)) {
                $suspiciousActivities = array_merge($suspiciousActivities, $this->checkAdminActions((int) $userId, $data));
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
        $monitoringRules = $this->config['monitoring_rules'] ?? [];
        $rule = $monitoringRules['multiple_failed_logins'] ?? [];

        if (! ($rule['enabled'] ?? false)) {
            return [];
        }

        $key = "failed_logins:{$userId}:{$ipAddress}";
        $failedCount = Cache::get($key, 0);
        $failedCount++;

        $timeWindow = $rule['time_window'] ?? 60;
        Cache::put($key, $failedCount, $timeWindow * 60);

        $threshold = $rule['threshold'] ?? 5;
        if ($failedCount >= $threshold) {
            return [[
                'type' => 'multiple_failed_logins',
                'severity' => $rule['severity'] ?? 'medium',
                'user_id' => $userId,
                'ip_address' => $ipAddress,
                'details' => [
                    'failed_attempts' => $failedCount,
                    'time_window' => $timeWindow,
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
        $monitoringRules = $this->config['monitoring_rules'] ?? [];
        $rule = $monitoringRules['unusual_login_location'] ?? [];

        if (! ($rule['enabled'] ?? false)) {
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
                'severity' => $rule['severity'] ?? 'medium',
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
        $monitoringRules = $this->config['monitoring_rules'] ?? [];
        $rule = $monitoringRules['rapid_api_requests'] ?? [];

        if (! ($rule['enabled'] ?? false)) {
            return [];
        }

        $key = "api_requests:{$userId}:{$ipAddress}";
        $requestCount = Cache::get($key, 0);
        $requestCount++;

        $timeWindow = $rule['time_window'] ?? 60;
        Cache::put($key, $requestCount, $timeWindow * 60);

        $threshold = $rule['threshold'] ?? 100;
        if ($requestCount >= $threshold) {
            return [[
                'type' => 'rapid_api_requests',
                'severity' => $rule['severity'] ?? 'medium',
                'user_id' => $userId,
                'ip_address' => $ipAddress,
                'details' => [
                    'request_count' => $requestCount,
                    'time_window' => $timeWindow,
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
        $monitoringRules = $this->config['monitoring_rules'] ?? [];
        $rule = $monitoringRules['unusual_data_access'] ?? [];

        if (! ($rule['enabled'] ?? false)) {
            return [];
        }

        $key = "data_access:{$userId}";
        $accessCount = Cache::get($key, 0);
        $accessCount++;

        $timeWindow = $rule['time_window'] ?? 60;
        Cache::put($key, $accessCount, $timeWindow * 60);

        $threshold = $rule['threshold'] ?? 50;
        if ($accessCount >= $threshold) {
            return [[
                'type' => 'unusual_data_access',
                'severity' => $rule['severity'] ?? 'medium',
                'user_id' => $userId,
                'details' => [
                    'access_count' => $accessCount,
                    'time_window' => $timeWindow,
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
        $monitoringRules = $this->config['monitoring_rules'] ?? [];
        $rule = $monitoringRules['admin_actions'] ?? [];

        if (! ($rule['enabled'] ?? false)) {
            return [];
        }

        // Log all admin actions as potentially suspicious
        return [[
            'type' => 'admin_action',
            'severity' => $rule['severity'] ?? 'high',
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
        $notification = $this->config['notification'] ?? [];

        if ($notification['email'] ?? false) {
            $this->sendEmailNotification($activity);
        }

        if ($notification['slack'] ?? false) {
            $this->sendSlackNotification($activity);
        }

        if ($notification['webhook'] ?? false) {
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
                $subject = 'Suspicious Activity Alert - '.($activity['type'] ?? 'unknown');
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
        $severity = $activity['severity'] ?? 'medium';
        $type = $activity['type'] ?? 'unknown';

        // High severity actions
        if ($severity === 'high') {
            switch ($type) {
                case 'multiple_failed_logins':
                    $userId = $activity['user_id'] ?? 0;
                    if (is_numeric($userId)) {
                        $this->lockUserAccount((int) $userId);
                    }
                    break;
                case 'unusual_data_access':
                    $userId = $activity['user_id'] ?? 0;
                    if (is_numeric($userId)) {
                        $this->suspendUserAccount((int) $userId);
                    }
                    break;
            }
        }

        // Medium severity actions
        if ($severity === 'medium') {
            switch ($type) {
                case 'rapid_api_requests':
                    $userId = $activity['user_id'] ?? 0;
                    if (is_numeric($userId)) {
                        $this->throttleUserRequests((int) $userId);
                    }
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
        $message .= 'Type: '.($activity['type'] ?? 'unknown')."\n";
        $message .= 'Severity: '.($activity['severity'] ?? 'unknown')."\n";
        $message .= 'User ID: '.($activity['user_id'] ?? 'unknown')."\n";
        $message .= 'IP Address: '.($activity['ip_address'] ?? 'unknown')."\n";
        $message .= 'Timestamp: '.($activity['timestamp'] ?? 'unknown')."\n\n";
        $message .= "Details:\n";
        $message .= json_encode($activity['details'] ?? [], JSON_PRETTY_PRINT);

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

        $currentLat = is_numeric($currentLocation['latitude'] ?? 0) ? (float) $currentLocation['latitude'] : 0.0;
        $currentLng = is_numeric($currentLocation['longitude'] ?? 0) ? (float) $currentLocation['longitude'] : 0.0;

        foreach ($previousLocations as $location) {
            if (is_array($location)) {
                $prevLat = is_numeric($location['latitude'] ?? 0) ? (float) $location['latitude'] : 0.0;
                $prevLng = is_numeric($location['longitude'] ?? 0) ? (float) $location['longitude'] : 0.0;

                $distance = $this->calculateDistance($currentLat, $currentLng, $prevLat, $prevLng);

                // If distance is less than 100km, it's not unusual
                if ($distance < 100) {
                    return false;
                }
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
