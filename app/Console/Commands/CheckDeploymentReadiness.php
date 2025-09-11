<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class CheckDeploymentReadiness extends Command
{
    protected $signature = 'deployment:check';

    protected $description = 'Check if the application is ready for deployment to Hostinger';

    public function handle(): int
    {
        $this->info('Starting deployment readiness check...');
        $hasErrors = false;

        // 1. Environment Check
        $this->info('Checking environment configuration...');
        $envErrors = $this->checkEnvironment();
        if (! empty($envErrors)) {
            $hasErrors = true;
        }

        // 2. Storage Permissions
        $this->info('Checking storage permissions...');
        $storageErrors = $this->checkStoragePermissions();
        $hasErrors = $hasErrors || ! empty($storageErrors);

        // 3. Database Configuration
        $this->info('Checking database configuration...');
        $dbErrors = $this->checkDatabase();
        $hasErrors = $hasErrors || ! empty($dbErrors);

        // 4. Cache Configuration
        $this->info('Checking cache configuration...');
        $cacheErrors = $this->checkCache();
        $hasErrors = $hasErrors || ! empty($cacheErrors);

        // 5. Queue Configuration
        $this->info('Checking queue configuration...');
        $queueErrors = $this->checkQueue();
        $hasErrors = $hasErrors || ! empty($queueErrors);

        // 6. Logging Configuration
        $this->info('Checking logging configuration...');
        $logErrors = $this->checkLogging();
        $hasErrors = $hasErrors || ! empty($logErrors);

        // Display Results
        $this->displayResults([
            'Environment' => $envErrors,
            'Storage' => $storageErrors,
            'Database' => $dbErrors,
            'Cache' => $cacheErrors,
            'Queue' => $queueErrors,
            'Logging' => $logErrors,
        ]);

        return $hasErrors ? 1 : 0;
    }

    /**
     * @return list<string>
     */
    private function checkEnvironment(): array
    {
        $errors = [];

        $requiredEnvVars = [
            'APP_KEY',
            'APP_URL',
            'DB_HOST',
            'DB_DATABASE',
            'DB_USERNAME',
            'DB_PASSWORD',
            'MAIL_HOST',
            'MAIL_USERNAME',
            'MAIL_PASSWORD',
        ];

        foreach ($requiredEnvVars as $var) {
            if (empty(config('app.'.strtolower($var)))) {
                $errors[] = "Missing required environment variable: {$var}";
            }
        }

        if (config('app.debug') === true) {
            $errors[] = 'APP_DEBUG should be set to false in production';
        }

        if (config('app.env') !== 'production') {
            $errors[] = 'APP_ENV should be set to production';
        }

        return $errors;
    }

    /**
     * @return list<string>
     */
    /**
     * @return list<string>
     */
    private function checkStoragePermissions(): array
    {
        $errors = [];
        $paths = [
            storage_path('app'),
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('logs'),
            public_path('storage'),
        ];

        foreach ($paths as $path) {
            if (! is_writable($path)) {
                $errors[] = "Directory not writable: {$path}";
            }
        }

        return $errors;
    }

    /**
     * @return list<string>
     */
    /**
     * @return list<string>
     */
    private function checkDatabase(): array
    {
        $errors = [];

        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $errors[] = "Database connection failed: {$e->getMessage()}";

            return $errors;
        }

        // Check if all migrations can be run
        try {
            $this->call('migrate:status');
        } catch (\Exception $e) {
            $errors[] = "Migration check failed: {$e->getMessage()}";
        }

        return $errors;
    }

    /**
     * @return list<string>
     */
    /**
     * @return list<string>
     */
    private function checkCache(): array
    {
        $errors = [];

        try {
            Cache::store()->set('deployment_test', true, 1);
            Cache::store()->get('deployment_test');
        } catch (\Exception $e) {
            $errors[] = "Cache test failed: {$e->getMessage()}";
        }

        return $errors;
    }

    /**
     * @return list<string>
     */
    /**
     * @return list<string>
     */
    private function checkQueue(): array
    {
        $errors = [];

        if (config('queue.default') === 'sync' && config('app.env') === 'production') {
            $errors[] = "Queue should not be set to 'sync' in production";
        }

        // Check if required queue tables exist when using database queue
        if (config('queue.default') === 'database') {
            if (! Schema::hasTable('jobs')) {
                $errors[] = "Missing required table 'jobs' for database queue";
            }
            if (! Schema::hasTable('failed_jobs')) {
                $errors[] = "Missing required table 'failed_jobs' for database queue";
            }
        }

        return $errors;
    }

    /**
     * @return list<string>
     */
    /**
     * @return list<string>
     */
    private function checkLogging(): array
    {
        $errors = [];
        $logPath = storage_path('logs');

        if (! is_writable($logPath)) {
            $errors[] = "Logs directory not writable: {$logPath}";
        }

        try {
            Log::info('Deployment check test log');
        } catch (\Exception $e) {
            $errors[] = "Logging test failed: {$e->getMessage()}";
        }

        // Check log file size (Hostinger typically has a 100MB limit per file)
        $logFile = storage_path('logs/laravel.log');
        if (file_exists($logFile) && filesize($logFile) > 100 * 1024 * 1024) {
            $errors[] = "Log file exceeds 100MB limit: {$logFile}";
        }

        return $errors;
    }

    /**
     * @param  array<string, list<string>>  $checkResults
     */
    private function displayResults(array $checkResults): void
    {
        $this->newLine();
        $this->info('Deployment Readiness Check Results:');
        $this->newLine();

        $hasErrors = false;

        foreach ($checkResults as $category => $errors) {
            if (empty($errors)) {
                $this->info("✓ {$category}: Passed");
            } else {
                $hasErrors = true;
                $this->error("✗ {$category}: Failed");
                foreach ($errors as $error) {
                    $this->line("  - {$error}");
                }
            }
        }

        $this->newLine();
        if ($hasErrors) {
            $this->error('Deployment checks failed. Please fix the above issues before deploying.');
        } else {
            $this->info('All deployment checks passed successfully!');
        }
    }
}
