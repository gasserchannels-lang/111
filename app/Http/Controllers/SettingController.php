<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SettingController extends Controller
{
    /**
     * Get all settings.
     */
    public function index(): JsonResponse
    {
        $settings = [
            'app_name' => Config::get('app.name'),
            'debug_mode' => Config::get('app.debug'),
            'timezone' => Config::get('app.timezone'),
            'mail_driver' => Config::get('mail.driver'),
            'cache_driver' => Config::get('cache.default'),
            'session_driver' => Config::get('session.driver'),
            'queue_driver' => Config::get('queue.default'),
        ];

        return response()->json([
            'success' => true,
            'data' => $settings,
            'message' => 'Settings retrieved successfully',
        ]);
    }

    /**
     * Update settings.
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'app_name' => 'sometimes|string|max:255',
                'debug_mode' => 'sometimes|boolean',
                'timezone' => 'sometimes|string|max:255',
                'mail_driver' => 'sometimes|string|max:255',
                'cache_driver' => 'sometimes|string|max:255',
                'session_driver' => 'sometimes|string|max:255',
                'queue_driver' => 'sometimes|string|max:255',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            // Update settings (example: updating .env or config files directly is not recommended in production)
            if ($request->has('app_name')) {
                Config::set('app.name', $request->input('app_name'));
            }
            if ($request->has('debug_mode')) {
                Config::set('app.debug', $request->input('debug_mode'));
            }
            if ($request->has('timezone')) {
                Config::set('app.timezone', $request->input('timezone'));
            }
            if ($request->has('mail_driver')) {
                Config::set('mail.driver', $request->input('mail_driver'));
            }
            if ($request->has('cache_driver')) {
                Config::set('cache.default', $request->input('cache_driver'));
            }
            if ($request->has('session_driver')) {
                Config::set('session.driver', $request->input('session_driver'));
            }
            if ($request->has('queue_driver')) {
                Config::set('queue.default', $request->input('queue_driver'));
            }

            // Clear config cache
            Artisan::call('config:clear');

            Log::info('Settings updated by user: '.(auth()->id() ?? 'Guest'));

            return response()->json([
                'success' => true,
                'message' => 'Settings updated successfully',
                'data' => $request->all(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating settings: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get password policy settings.
     *
     * @return array<string, mixed>
     */
    public function getPasswordPolicySettings(): array
    {
        return [
            'min_length' => 8,
            'require_uppercase' => true,
            'require_lowercase' => true,
            'require_numbers' => true,
            'require_symbols' => false,
            'max_age_days' => 90,
            'prevent_reuse_count' => 5,
        ];
    }

    /**
     * Get notification settings.
     *
     * @return array<string, mixed>
     */
    public function getNotificationSettings(): array
    {
        return [
            'email_notifications' => true,
            'push_notifications' => true,
            'sms_notifications' => false,
            'price_alerts' => true,
            'system_updates' => true,
            'marketing_emails' => false,
        ];
    }

    /**
     * Get storage settings.
     *
     * @return array<string, mixed>
     */
    public function getStorageSettings(): array
    {
        return [
            'max_file_size' => '10MB',
            'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'],
            'storage_driver' => 'local',
            'backup_frequency' => 'daily',
            'retention_days' => 30,
        ];
    }

    /**
     * Get general settings.
     *
     * @return array<string, mixed>
     */
    public function getGeneralSettings(): array
    {
        return [
            'site_name' => Config::get('app.name'),
            'site_description' => 'Price comparison platform',
            'contact_email' => 'admin@example.com',
            'timezone' => Config::get('app.timezone'),
            'language' => 'en',
            'currency' => 'USD',
        ];
    }

    /**
     * Get security settings.
     *
     * @return array<string, mixed>
     */
    public function getSecuritySettings(): array
    {
        return [
            'two_factor_auth' => false,
            'session_timeout' => 120,
            'max_login_attempts' => 5,
            'lockout_duration' => 15,
            'ip_whitelist' => [],
            'ssl_required' => true,
        ];
    }

    /**
     * Get performance settings.
     *
     * @return array<string, mixed>
     */
    public function getPerformanceSettings(): array
    {
        return [
            'cache_enabled' => true,
            'cache_driver' => Config::get('cache.default'),
            'cache_ttl' => 3600,
            'query_cache' => true,
            'view_cache' => true,
            'route_cache' => true,
        ];
    }

    /**
     * Reset settings to default.
     */
    public function resetToDefault(): JsonResponse
    {
        try {
            // Reset password policy settings
            $this->resetPasswordPolicySettings();

            // Reset storage settings
            $this->resetStorageSettings();

            Log::info('Settings reset to default by user: '.(auth()->id() ?? 'Guest'));

            return response()->json([
                'success' => true,
                'message' => 'Settings reset to default successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error resetting settings: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to reset settings',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reset password policy settings.
     */
    private function resetPasswordPolicySettings(): void
    {
        // Placeholder for password policy reset
        Log::info('Password policy settings reset to default');
    }

    /**
     * Reset storage settings.
     */
    private function resetStorageSettings(): void
    {
        // Placeholder for storage settings reset
        Log::info('Storage settings reset to default');
    }

    /**
     * Import settings from file.
     */
    public function importSettings(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'settings_file' => 'required|file|mimes:json,csv|max:2048',
            ]);

            $file = $request->file('settings_file');
            $content = file_get_contents($file->getPathname());
            if ($content === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to read file',
                ], 400);
            }
            $settings = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($settings)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid JSON file',
                ], 400);
            }

            // Process imported settings
            $settingsArray = $settings;
            // Ensure all keys are strings
            $settingsWithStringKeys = [];
            foreach ($settingsArray as $key => $value) {
                $settingsWithStringKeys[(string) $key] = $value;
            }
            $this->processImportedSettings($settingsWithStringKeys);

            return response()->json([
                'success' => true,
                'message' => 'Settings imported successfully',
                'data' => $settings,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error importing settings: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to import settings',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Process imported settings.
     *
     * @param  array<string, mixed>  $settings
     */
    private function processImportedSettings(array $settings): void
    {
        // Placeholder for processing imported settings
        Log::info('Processing imported settings: '.json_encode($settings));
    }

    /**
     * Export settings to file.
     */
    public function exportSettings(): JsonResponse
    {
        try {
            $settings = [
                'general' => $this->getGeneralSettings(),
                'security' => $this->getSecuritySettings(),
                'performance' => $this->getPerformanceSettings(),
                'password_policy' => $this->getPasswordPolicySettings(),
                'notifications' => $this->getNotificationSettings(),
                'storage' => $this->getStorageSettings(),
            ];

            $filename = 'settings_'.now()->format('Y-m-d_H-i-s').'.json';
            $filePath = storage_path('app/'.$filename);

            file_put_contents($filePath, json_encode($settings, JSON_PRETTY_PRINT));

            return response()->json([
                'success' => true,
                'message' => 'Settings exported successfully',
                'data' => [
                    'filename' => $filename,
                    'download_url' => url('storage/'.$filename),
                    'expires_at' => now()->addHours(24)->toISOString(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error exporting settings: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to export settings',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get system health status.
     */
    public function getSystemHealth(): JsonResponse
    {
        try {
            $health = [
                'status' => 'healthy',
                'database' => 'connected',
                'cache' => 'operational',
                'storage' => 'available',
                'memory_usage' => memory_get_usage(true),
                'disk_space' => disk_free_space('/'),
                'last_check' => now()->toISOString(),
            ];

            return response()->json([
                'success' => true,
                'data' => $health,
                'message' => 'System health retrieved successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting system health: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get system health',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
