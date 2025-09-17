<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\PriceOffer;
use App\Models\Store;
use Illuminate\Console\Command;

class UpdatePricesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coprra:update-prices {--store= : Update prices for specific store} {--product= : Update prices for specific product} {--dry-run : Show what would be updated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update price offers from external APIs or manual sources';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔄 Starting price update process...');

        $storeId = $this->option('store');
        $productId = $this->option('product');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('🧪 Running in dry-run mode - no changes will be made');
        }

        $query = PriceOffer::with(['store', 'product']);

        if ($storeId) {
            $query->where('store_id', $storeId);
            $this->info("📍 Filtering by store ID: {$storeId}");
        }

        if ($productId) {
            $query->where('product_id', $productId);
            $this->info("📦 Filtering by product ID: {$productId}");
        }

        $priceOffers = $query->get();
        $this->info("📊 Found {$priceOffers->count()} price offers to process");

        $updatedCount = 0;
        $errorCount = 0;

        $progressBar = $this->output->createProgressBar($priceOffers->count());
        $progressBar->start();

        foreach ($priceOffers as $priceOffer) {
            try {
                $newPrice = $this->fetchPriceFromAPI($priceOffer);

                if ($newPrice && $newPrice !== (float) $priceOffer->price) {
                    if (! $dryRun) {
                        $priceOffer->update([
                            'price' => $newPrice,
                            'updated_at' => now(),
                        ]);
                    }

                    $updatedCount++;
                    $productName = $priceOffer->product->name ?? 'Unknown Product';
                    $storeName = $priceOffer->store->name ?? 'Unknown Store';
                    $this->line("\n💰 Updated {$productName} at {$storeName}: {$priceOffer->price} → {$newPrice}");
                }
            } catch (\Exception $e) {
                $errorCount++;
                $productName = $priceOffer->product->name ?? 'Unknown Product';
                $storeName = $priceOffer->store->name ?? 'Unknown Store';
                $this->error("\n❌ Error updating {$productName} at {$storeName}: ".$e->getMessage());
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        $this->info('✅ Price update completed!');
        $this->table(['Metric', 'Count'], [
            ['Total processed', $priceOffers->count()],
            ['Updated', $updatedCount],
            ['Errors', $errorCount],
            ['Unchanged', $priceOffers->count() - $updatedCount - $errorCount],
        ]);

        return $errorCount > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    /**
     * Fetch price from external API (placeholder implementation).
     *
     * @param  \App\Models\PriceOffer<\Database\Factories\PriceOfferFactory>  $priceOffer
     */
    private function fetchPriceFromAPI(\App\Models\PriceOffer $priceOffer): ?float
    {
        // This is a placeholder implementation
        // In a real application, you would call the store's API here

        // Simulate API call with random price fluctuation
        $fluctuation = random_int(-10, 10) / 100; // ±10%
        $newPrice = $priceOffer->price * (1 + $fluctuation);

        // Only return if price changed significantly (more than 1%)
        if (abs($priceOffer->price - $newPrice) / $priceOffer->price > 0.01) {
            return round($newPrice, 2);
        }

        return null;
    }
}
