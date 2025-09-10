<?php

declare(strict_types=1);

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    protected function schedule(Schedule $schedule): void
    {
        // Queue and Cache Management
        $schedule->command('queue:prune-batches')->daily();
        $schedule->command('queue:prune-failed')->weekly();
        $schedule->command('cache:prune-stale-tags')->daily();

        // Log Management (Hostinger has file size limits)
        $schedule->command('log:prune')->daily()
            ->onSuccess(function () {
                \Illuminate\Support\Facades\Log::info('Log pruning completed successfully');
            });

        // Session Cleanup
        $schedule->command('session:gc')->weekly();

        // Storage Cleanup (remove old temporary files)
        $schedule->command('storage:prune-temporary')->daily();

        // Database Maintenance
        $schedule->command('db:monitor')->hourly()
            ->onFailure(function () {
                \Illuminate\Support\Facades\Log::error('Database monitoring failed');
            });

        // Deployment Health Checks
        $schedule->command('deployment:check')->daily()
            ->onFailure(function () {
                \Illuminate\Support\Facades\Log::error('Deployment health check failed');
            });
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require_once base_path('routes/console.php');
    }
}
