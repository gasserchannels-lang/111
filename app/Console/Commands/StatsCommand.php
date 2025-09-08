<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\Category;
use App\Models\PriceAlert;
use App\Models\PriceOffer;
use App\Models\Product;
use App\Models\Review;
use App\Models\Store;
use App\Models\User;
use Illuminate\Console\Command;

class StatsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coprra:stats {--detailed : Show detailed statistics}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display comprehensive statistics about the COPRRA platform';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('📊 COPRRA Platform Statistics');
        $this->line('='.str_repeat('=', 50));

        $detailed = $this->option('detailed');

        // Basic counts
        $this->displayBasicStats();

        if ($detailed) {
            $this->newLine();
            $this->displayDetailedStats();
        }

        $this->newLine();
        $this->info('✅ Statistics generated successfully!');

        return Command::SUCCESS;
    }

    private function displayBasicStats(): void
    {
        $stats = [
            ['Metric', 'Count'],
            ['Products', Product::count()],
            ['Active Products', Product::where('is_active', true)->count()],
            ['Stores', Store::count()],
            ['Active Stores', Store::where('is_active', true)->count()],
            ['Brands', Brand::count()],
            ['Categories', Category::count()],
            ['Price Offers', PriceOffer::count()],
            ['In Stock Offers', PriceOffer::where('in_stock', true)->count()],
            ['Reviews', Review::count()],
            ['Users', User::count()],
            ['Price Alerts', PriceAlert::count()],
            ['Active Alerts', PriceAlert::where('is_active', true)->count()],
        ];

        $this->table(['Metric', 'Count'], array_slice($stats, 1));
    }

    private function displayDetailedStats(): void
    {
        $this->info('📈 Detailed Statistics');

        // Price statistics
        $avgPrice = PriceOffer::avg('price');
        $minPrice = PriceOffer::min('price');
        $maxPrice = PriceOffer::max('price');

        $this->table(['Price Metric', 'Value'], [
            ['Average Price', '$'.number_format($avgPrice, 2)],
            ['Minimum Price', '$'.number_format($minPrice, 2)],
            ['Maximum Price', '$'.number_format($maxPrice, 2)],
        ]);

        // Top categories by product count
        $topCategories = Category::withCount('products')
            ->orderBy('products_count', 'desc')
            ->take(5)
            ->get();

        if ($topCategories->isNotEmpty()) {
            $this->info('🏆 Top 5 Categories by Product Count');
            $categoryData = $topCategories->map(function ($category) {
                return [$category->name, $category->products_count];
            })->toArray();

            $this->table(['Category', 'Products'], $categoryData);
        }

        // Top brands by product count
        $topBrands = Brand::withCount('products')
            ->orderBy('products_count', 'desc')
            ->take(5)
            ->get();

        if ($topBrands->isNotEmpty()) {
            $this->info('🏆 Top 5 Brands by Product Count');
            $brandData = $topBrands->map(function ($brand) {
                return [$brand->name, $brand->products_count];
            })->toArray();

            $this->table(['Brand', 'Products'], $brandData);
        }

        // Store statistics
        $storeStats = Store::withCount('priceOffers')
            ->orderBy('price_offers_count', 'desc')
            ->take(5)
            ->get();

        if ($storeStats->isNotEmpty()) {
            $this->info('🏪 Top 5 Stores by Price Offers');
            $storeData = $storeStats->map(function ($store) {
                return [$store->name, $store->price_offers_count];
            })->toArray();

            $this->table(['Store', 'Price Offers'], $storeData);
        }

        // Recent activity
        $recentProducts = Product::where('created_at', '>=', now()->subDays(7))->count();
        $recentOffers = PriceOffer::where('created_at', '>=', now()->subDays(7))->count();
        $recentUsers = User::where('created_at', '>=', now()->subDays(7))->count();

        $this->info('📅 Activity in Last 7 Days');
        $this->table(['Activity', 'Count'], [
            ['New Products', $recentProducts],
            ['New Price Offers', $recentOffers],
            ['New Users', $recentUsers],
        ]);

        // Database size approximation
        $this->info('💾 Database Information');
        $totalRecords = Product::count() + Store::count() + Brand::count() +
                       Category::count() + PriceOffer::count() + Review::count() +
                       User::count() + PriceAlert::count();

        $this->table(['Database Metric', 'Value'], [
            ['Total Records', number_format($totalRecords)],
            ['Estimated Size', $this->formatBytes($totalRecords * 1024)], // Rough estimate
        ]);
    }

    private function formatBytes(int $size, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }

        return round($size, $precision).' '.$units[$i];
    }
}
