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
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('🧪 Running in dry-run mode - no changes will be made');
        }

        $query = PriceOffer::with(['store', 'product']);

        if ($storeId) {
            $query->where('store_id', (int) $storeId);
            $this->info("📍 Filtering by store ID: {$storeId}");
        }

        if ($productId) {
            $query->where('product_id', (int) $productId);
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

                $currentPrice = is_numeric($priceOffer->price) ? (float) $priceOffer->price : 0.0;
                if ($newPrice && $newPrice !== $currentPrice) {
                    if (! $dryRun) {
                        $priceOffer->update([
                            'price' => $newPrice,
                            'updated_at' => now(),
                        ]);
                    }

                    $updatedCount++;
                    $productName = is_string($priceOffer->product->name) ? $priceOffer->product->name : 'Unknown Product';
                    $storeName = is_string($priceOffer->store->name) ? $priceOffer->store->name : 'Unknown Store';
                    $this->line("\n💰 Updated {$productName} at {$storeName}: {$currentPrice} → {$newPrice}");
                }
            } catch (\Exception $e) {
                $errorCount++;
                $product = $priceOffer->product;
                $store = $priceOffer->store;
                $productName = ($product && is_string($product->name)) ? $product->name : 'Unknown Product';
                $storeName = ($store && is_string($store->name)) ? $store->name : 'Unknown Store';
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
     */
    private function fetchPriceFromAPI(PriceOffer $priceOffer): ?float
    {
        // This is a placeholder implementation
        // In a real application, you would call the store's API here

        // Simulate API call with random price fluctuation
        $fluctuation = random_int(-10, 10) / 100; // ±10%
        $currentPrice = is_numeric($priceOffer->price) ? (float) $priceOffer->price : 0.0;
        $newPrice = $currentPrice * (1 + $fluctuation);

        // Only return if price changed significantly (more than 1%)
        if ($currentPrice > 0 && abs($currentPrice - $newPrice) / $currentPrice > 0.01) {
            return round($newPrice, 2);
        }

        return null;
    }
}
