<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\PriceAlert;
use App\Models\PriceOffer;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CleanupOldDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coprra:cleanup {--days=30 : Number of days to keep data} {--dry-run : Show what would be deleted without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old data including expired price offers, old reviews, and inactive price alerts';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');

        $this->info("🧹 Starting cleanup process for data older than {$days} days...");

        if ($dryRun) {
            $this->warn('🧪 Running in dry-run mode - no data will be deleted');
        }

        $cutoffDate = Carbon::now()->subDays($days);
        $totalDeleted = 0;

        // Clean up old price offers
        $oldPriceOffersCount = PriceOffer::where('updated_at', '<', $cutoffDate)
            ->where('in_stock', false)
            ->count();

        if ($oldPriceOffersCount > 0) {
            $this->info("📊 Found {$oldPriceOffersCount} old out-of-stock price offers to clean up");

            if (! $dryRun) {
                $deleted = PriceOffer::where('updated_at', '<', $cutoffDate)
                    ->where('in_stock', false)
                    ->delete();
                $totalDeleted += $deleted;
                $this->line("✅ Deleted {$deleted} old price offers");
            }
        }

        // Clean up old reviews (optional - you might want to keep all reviews)
        $oldReviewsCount = Review::where('created_at', '<', $cutoffDate)
            ->where('is_approved', false)
            ->count();

        if ($oldReviewsCount > 0) {
            $this->info("📝 Found {$oldReviewsCount} old unapproved reviews to clean up");

            if (! $dryRun) {
                $deleted = Review::where('created_at', '<', $cutoffDate)
                    ->where('is_approved', false)
                    ->delete();
                $totalDeleted += $deleted;
                $this->line("✅ Deleted {$deleted} old unapproved reviews");
            }
        }

        // Clean up expired price alerts
        $expiredAlertsCount = PriceAlert::where('created_at', '<', $cutoffDate)
            ->where('is_active', false)
            ->count();

        if ($expiredAlertsCount > 0) {
            $this->info("🔔 Found {$expiredAlertsCount} old inactive price alerts to clean up");

            if (! $dryRun) {
                $deleted = PriceAlert::where('created_at', '<', $cutoffDate)
                    ->where('is_active', false)
                    ->delete();
                $totalDeleted += $deleted;
                $this->line("✅ Deleted {$deleted} old price alerts");
            }
        }

        // Clean up session data (Laravel handles this automatically, but we can force it)
        if (! $dryRun) {
            $this->call('session:gc');
            $this->line('✅ Cleaned up expired sessions');
        }

        // Clean up cache
        if (! $dryRun) {
            $this->call('cache:prune-stale-tags');
            $this->line('✅ Pruned stale cache tags');
        }

        $this->newLine();

        $totalWouldDelete = $oldPriceOffersCount + $oldReviewsCount + $expiredAlertsCount;

        if ($dryRun) {
            $this->info("🧪 Dry run completed. Would have deleted {$totalWouldDelete} records");
        } else {
            $this->info("✅ Cleanup completed! Deleted {$totalDeleted} records total");
        }

        $this->table(['Category', 'Count'], [
            ['Old Price Offers', $oldPriceOffersCount],
            ['Old Reviews', $oldReviewsCount],
            ['Expired Alerts', $expiredAlertsCount],
            ['Total', $totalWouldDelete],
        ]);

        return Command::SUCCESS;
    }
}
