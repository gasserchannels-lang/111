<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\PriceAlert;
use App\Models\PriceOffer;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Models\Wishlist;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class StatisticsService
{
    /**
     * Get real-time statistics.
     *
     * @return array<string, mixed>
     */
    public function getRealTimeStats(): array
    {
        return Cache::remember('real_time_stats', 60, function () {
            return [
                'total_users' => User::count(),
                'total_products' => Product::count(),
                'total_offers' => PriceOffer::count(),
                'total_reviews' => Review::count(),
                'total_wishlists' => Wishlist::count(),
                'total_price_alerts' => PriceAlert::count(),
                'active_users_today' => $this->getActiveUsersToday(),
                'new_products_today' => $this->getNewProductsToday(),
                'price_changes_today' => $this->getPriceChangesToday(),
                'new_reviews_today' => $this->getNewReviewsToday(),
            ];
        });
    }

    /**
     * Get daily statistics.
     *
     * @return array<string, mixed>
     */
    public function getDailyStats(Carbon $date): array
    {
        $cacheKey = "daily_stats_{$date->format('Y-m-d')}";

        return Cache::remember($cacheKey, 3600, function () use ($date) {
            return [
                'date' => $date->format('Y-m-d'),
                'new_users' => User::whereDate('created_at', $date)->count(),
                'new_products' => Product::whereDate('created_at', $date)->count(),
                'new_offers' => PriceOffer::whereDate('created_at', $date)->count(),
                'new_reviews' => Review::whereDate('created_at', $date)->count(),
                'new_wishlists' => Wishlist::whereDate('created_at', $date)->count(),
                'new_price_alerts' => PriceAlert::whereDate('created_at', $date)->count(),
                'price_changes' => $this->getPriceChangesForDate($date),
                'most_viewed_products' => $this->getMostViewedProductsForDate($date),
                'most_active_users' => $this->getMostActiveUsersForDate($date),
            ];
        });
    }

    /**
     * Get weekly statistics.
     *
     * @return array<string, mixed>
     */
    public function getWeeklyStats(Carbon $startDate): array
    {
        $endDate = $startDate->copy()->addWeek();
        $cacheKey = "weekly_stats_{$startDate->format('Y-m-d')}";

        return Cache::remember($cacheKey, 3600, function () use ($startDate, $endDate) {
            return [
                'week_start' => $startDate->format('Y-m-d'),
                'week_end' => $endDate->format('Y-m-d'),
                'new_users' => User::whereBetween('created_at', [$startDate, $endDate])->count(),
                'new_products' => Product::whereBetween('created_at', [$startDate, $endDate])->count(),
                'new_offers' => PriceOffer::whereBetween('created_at', [$startDate, $endDate])->count(),
                'new_reviews' => Review::whereBetween('created_at', [$startDate, $endDate])->count(),
                'new_wishlists' => Wishlist::whereBetween('created_at', [$startDate, $endDate])->count(),
                'new_price_alerts' => PriceAlert::whereBetween('created_at', [$startDate, $endDate])->count(),
                'daily_breakdown' => $this->getDailyBreakdown($startDate, $endDate),
                'top_categories' => $this->getTopCategories($startDate, $endDate),
                'top_brands' => $this->getTopBrands($startDate, $endDate),
                'price_trends' => $this->getPriceTrends($startDate, $endDate),
            ];
        });
    }

    /**
     * Get monthly statistics.
     *
     * @return array<string, mixed>
     */
    public function getMonthlyStats(Carbon $date): array
    {
        $startDate = $date->copy()->startOfMonth();
        $endDate = $date->copy()->endOfMonth();
        $cacheKey = "monthly_stats_{$date->format('Y-m')}";

        return Cache::remember($cacheKey, 3600, function () use ($startDate, $endDate, $date) {
            return [
                'month' => $date->format('Y-m'),
                'new_users' => User::whereBetween('created_at', [$startDate, $endDate])->count(),
                'new_products' => Product::whereBetween('created_at', [$startDate, $endDate])->count(),
                'new_offers' => PriceOffer::whereBetween('created_at', [$startDate, $endDate])->count(),
                'new_reviews' => Review::whereBetween('created_at', [$startDate, $endDate])->count(),
                'new_wishlists' => Wishlist::whereBetween('created_at', [$startDate, $endDate])->count(),
                'new_price_alerts' => PriceAlert::whereBetween('created_at', [$startDate, $endDate])->count(),
                'user_growth_rate' => $this->calculateUserGrowthRate($startDate, $endDate),
                'product_growth_rate' => $this->calculateProductGrowthRate($startDate, $endDate),
                'engagement_rate' => $this->calculateEngagementRate($startDate, $endDate),
                'top_performing_products' => $this->getTopPerformingProducts($startDate, $endDate),
                'category_performance' => $this->getCategoryPerformance($startDate, $endDate),
                'brand_performance' => $this->getBrandPerformance($startDate, $endDate),
            ];
        });
    }

    /**
     * Get yearly statistics.
     *
     * @return array<string, mixed>
     */
    public function getYearlyStats(int $year): array
    {
        $startDate = Carbon::createFromDate($year, 1, 1);
        $endDate = Carbon::createFromDate($year, 12, 31);
        $cacheKey = "yearly_stats_{$year}";

        return Cache::remember($cacheKey, 3600, function () use ($startDate, $endDate, $year) {
            return [
                'year' => $year,
                'total_users' => User::whereBetween('created_at', [$startDate, $endDate])->count(),
                'total_products' => Product::whereBetween('created_at', [$startDate, $endDate])->count(),
                'total_offers' => PriceOffer::whereBetween('created_at', [$startDate, $endDate])->count(),
                'total_reviews' => Review::whereBetween('created_at', [$startDate, $endDate])->count(),
                'total_wishlists' => Wishlist::whereBetween('created_at', [$startDate, $endDate])->count(),
                'total_price_alerts' => PriceAlert::whereBetween('created_at', [$startDate, $endDate])->count(),
                'monthly_breakdown' => $this->getMonthlyBreakdown($startDate, $endDate),
                'quarterly_breakdown' => $this->getQuarterlyBreakdown($startDate, $endDate),
                'year_over_year_growth' => $this->getYearOverYearGrowth($year),
                'top_categories_yearly' => $this->getTopCategories($startDate, $endDate),
                'top_brands_yearly' => $this->getTopBrands($startDate, $endDate),
                'price_analysis_yearly' => $this->getPriceAnalysis($startDate, $endDate),
            ];
        });
    }

    /**
     * Get product statistics.
     *
     * @return array<string, mixed>
     */
    public function getProductStats(int $productId): array
    {
        $cacheKey = "product_stats_{$productId}";

        return Cache::remember($cacheKey, 1800, function () use ($productId) {
            $product = Product::with(['category:id,name', 'brand:id,name'])
                ->findOrFail($productId);

            // Use single query with counts to avoid N+1
            $stats = DB::table('products')
                ->leftJoin('wishlists', 'products.id', '=', 'wishlists.product_id')
                ->leftJoin('price_alerts', 'products.id', '=', 'price_alerts.product_id')
                ->leftJoin('reviews', 'products.id', '=', 'reviews.product_id')
                ->leftJoin('price_offers', 'products.id', '=', 'price_offers.product_id')
                ->where('products.id', $productId)
                ->selectRaw('
                    COUNT(DISTINCT wishlists.id) as wishlist_count,
                    COUNT(DISTINCT price_alerts.id) as price_alerts_count,
                    COUNT(DISTINCT reviews.id) as reviews_count,
                    AVG(reviews.rating) as average_rating,
                    COUNT(DISTINCT price_offers.id) as offers_count
                ')
                ->first();

            return [
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'category' => $product->category->name ?? 'N/A',
                    'brand' => $product->brand->name ?? 'N/A',
                ],
                'wishlist_count' => (int) ($stats->wishlist_count ?? 0),
                'price_alerts_count' => (int) ($stats->price_alerts_count ?? 0),
                'reviews_count' => (int) ($stats->reviews_count ?? 0),
                'average_rating' => round((float) ($stats->average_rating ?? 0), 2),
                'offers_count' => (int) ($stats->offers_count ?? 0),
                'price_range' => $this->getProductPriceRange($productId),
                'view_count' => $this->getProductViewCount($productId),
                'engagement_score' => $this->calculateProductEngagementScore($productId),
            ];
        });
    }

    /**
     * Get user statistics.
     *
     * @return array<string, mixed>
     */
    public function getUserStats(int $userId): array
    {
        $cacheKey = "user_stats_{$userId}";

        return Cache::remember($cacheKey, 1800, function () use ($userId) {
            $user = User::findOrFail($userId);

            // Use single query with counts to avoid N+1
            $stats = DB::table('users')
                ->leftJoin('wishlists', 'users.id', '=', 'wishlists.user_id')
                ->leftJoin('price_alerts', 'users.id', '=', 'price_alerts.user_id')
                ->leftJoin('reviews', 'users.id', '=', 'reviews.user_id')
                ->where('users.id', $userId)
                ->selectRaw('
                    COUNT(DISTINCT wishlists.id) as wishlist_count,
                    COUNT(DISTINCT price_alerts.id) as price_alerts_count,
                    COUNT(DISTINCT reviews.id) as reviews_count,
                    AVG(reviews.rating) as average_rating_given
                ')
                ->first();

            return [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at?->format('Y-m-d H:i:s') ?? 'N/A',
                ],
                'wishlist_count' => (int) ($stats->wishlist_count ?? 0),
                'price_alerts_count' => (int) ($stats->price_alerts_count ?? 0),
                'reviews_count' => (int) ($stats->reviews_count ?? 0),
                'average_rating_given' => round((float) ($stats->average_rating_given ?? 0), 2),
                'activity_score' => $this->calculateUserActivityScore($userId),
                'favorite_categories' => $this->getUserFavoriteCategories($userId),
                'favorite_brands' => $this->getUserFavoriteBrands($userId),
                'last_activity' => $this->getUserLastActivity($userId),
            ];
        });
    }

    /**
     * Get system health statistics.
     *
     * @return array<string, mixed>
     */
    public function getSystemHealthStats(): array
    {
        return Cache::remember('system_health_stats', 300, function () {
            return [
                'database_health' => $this->getDatabaseHealth(),
                'cache_health' => $this->getCacheHealth(),
                'queue_health' => $this->getQueueHealth(),
                'storage_health' => $this->getStorageHealth(),
                'api_health' => $this->getApiHealth(),
                'error_rate' => $this->getErrorRate(),
                'response_time' => $this->getAverageResponseTime(),
                'uptime' => $this->getUptime(),
            ];
        });
    }

    /**
     * Get active users today.
     */
    private function getActiveUsersToday(): int
    {
        return User::whereHas('wishlists', function ($query) {
            $query->whereDate('created_at', today());
        })->orWhereHas('priceAlerts', function ($query) {
            $query->whereDate('created_at', today());
        })->orWhereHas('reviews', function ($query) {
            $query->whereDate('created_at', today());
        })->count();
    }

    /**
     * Get new products today.
     */
    private function getNewProductsToday(): int
    {
        return Product::whereDate('created_at', today())->count();
    }

    /**
     * Get price changes today.
     */
    private function getPriceChangesToday(): int
    {
        return AuditLog::where('event', 'updated')
            ->where('auditable_type', 'App\Models\Product')
            ->whereDate('created_at', today())
            ->whereJsonContains('metadata->reason', 'Updated from lowest price offer')
            ->count();
    }

    /**
     * Get new reviews today.
     */
    private function getNewReviewsToday(): int
    {
        return Review::whereDate('created_at', today())->count();
    }

    /**
     * Get price changes for a specific date.
     */
    private function getPriceChangesForDate(Carbon $date): int
    {
        return AuditLog::where('event', 'updated')
            ->where('auditable_type', 'App\Models\Product')
            ->whereDate('created_at', $date)
            ->whereJsonContains('metadata->reason', 'Updated from lowest price offer')
            ->count();
    }

    /**
     * Get most viewed products for a specific date.
     *
     * @return array<string, mixed>
     */
    private function getMostViewedProductsForDate(Carbon $date): array
    {
        return DB::table('audit_logs')
            ->where('event', 'viewed')
            ->where('auditable_type', 'App\Models\Product')
            ->whereDate('created_at', $date)
            ->select('auditable_id', DB::raw('COUNT(*) as view_count'))
            ->groupBy('auditable_id')
            ->orderBy('view_count', 'desc')
            ->take(5)
            ->get()
            ->toArray();
    }

    /**
     * Get most active users for a specific date.
     *
     * @return array<string, mixed>
     */
    private function getMostActiveUsersForDate(Carbon $date): array
    {
        return User::withCount(['wishlists', 'priceAlerts', 'reviews'])
            ->whereHas('wishlists', function ($query) use ($date) {
                $query->whereDate('created_at', $date);
            })
            ->orWhereHas('priceAlerts', function ($query) use ($date) {
                $query->whereDate('created_at', $date);
            })
            ->orWhereHas('reviews', function ($query) use ($date) {
                $query->whereDate('created_at', $date);
            })
            ->orderBy('wishlists_count', 'desc')
            ->orderBy('price_alerts_count', 'desc')
            ->orderBy('reviews_count', 'desc')
            ->take(5)
            ->get()
            ->toArray();
    }

    /**
     * Get daily breakdown.
     *
     * @return list<array<string, mixed>>
     */
    private function getDailyBreakdown(Carbon $startDate, Carbon $endDate): array
    {
        $breakdown = [];
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            $breakdown[] = $this->getDailyStats($current);
            $current->addDay();
        }

        return $breakdown;
    }

    /**
     * Get top categories.
     *
     * @return array<string, mixed>
     */
    private function getTopCategories(Carbon $startDate, Carbon $endDate): array
    {
        return DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('products.created_at', [$startDate, $endDate])
            ->select('categories.id', 'categories.name', DB::raw('COUNT(*) as products_count'))
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('products_count', 'desc')
            ->take(10)
            ->get()
            ->toArray();
    }

    /**
     * Get top brands.
     *
     * @return array<string, mixed>
     */
    private function getTopBrands(Carbon $startDate, Carbon $endDate): array
    {
        return DB::table('products')
            ->join('brands', 'products.brand_id', '=', 'brands.id')
            ->whereBetween('products.created_at', [$startDate, $endDate])
            ->select('brands.id', 'brands.name', DB::raw('COUNT(*) as products_count'))
            ->groupBy('brands.id', 'brands.name')
            ->orderBy('products_count', 'desc')
            ->take(10)
            ->get()
            ->toArray();
    }

    /**
     * Get price trends.
     *
     * @return array<string, mixed>
     */
    private function getPriceTrends(Carbon $startDate, Carbon $endDate): array
    {
        return DB::table('price_offers')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('AVG(price) as average_price'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    /**
     * Calculate user growth rate.
     */
    private function calculateUserGrowthRate(Carbon $startDate, Carbon $endDate): float
    {
        $previousPeriodStart = $startDate->copy()->subMonth();
        $previousPeriodEnd = $startDate->copy()->subDay();

        $previousCount = User::whereBetween('created_at', [$previousPeriodStart, $previousPeriodEnd])->count();
        $currentCount = User::whereBetween('created_at', [$startDate, $endDate])->count();

        if ($previousCount === 0) {
            return $currentCount > 0 ? 100 : 0;
        }

        return (($currentCount - $previousCount) / $previousCount) * 100;
    }

    /**
     * Calculate product growth rate.
     */
    private function calculateProductGrowthRate(Carbon $startDate, Carbon $endDate): float
    {
        $previousPeriodStart = $startDate->copy()->subMonth();
        $previousPeriodEnd = $startDate->copy()->subDay();

        $previousCount = Product::whereBetween('created_at', [$previousPeriodStart, $previousPeriodEnd])->count();
        $currentCount = Product::whereBetween('created_at', [$startDate, $endDate])->count();

        if ($previousCount === 0) {
            return $currentCount > 0 ? 100 : 0;
        }

        return (($currentCount - $previousCount) / $previousCount) * 100;
    }

    /**
     * Calculate engagement rate.
     */
    private function calculateEngagementRate(Carbon $startDate, Carbon $endDate): float
    {
        $totalUsers = User::count();
        $activeUsers = User::whereHas('wishlists', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        })->orWhereHas('priceAlerts', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        })->orWhereHas('reviews', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        })->count();

        return $totalUsers > 0 ? ($activeUsers / $totalUsers) * 100 : 0;
    }

    /**
     * Get top performing products.
     *
     * @return array<string, mixed>
     */
    private function getTopPerformingProducts(Carbon $startDate, Carbon $endDate): array
    {
        return Product::withCount(['wishlists', 'priceAlerts', 'reviews'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('wishlists_count', 'desc')
            ->orderBy('price_alerts_count', 'desc')
            ->orderBy('reviews_count', 'desc')
            ->take(10)
            ->get()
            ->toArray();
    }

    /**
     * Get category performance.
     *
     * @return array<string, mixed>
     */
    private function getCategoryPerformance(Carbon $startDate, Carbon $endDate): array
    {
        return DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('wishlists', 'products.id', '=', 'wishlists.product_id')
            ->leftJoin('price_alerts', 'products.id', '=', 'price_alerts.product_id')
            ->leftJoin('reviews', 'products.id', '=', 'reviews.product_id')
            ->whereBetween('products.created_at', [$startDate, $endDate])
            ->select(
                'categories.id',
                'categories.name',
                DB::raw('COUNT(DISTINCT products.id) as products_count'),
                DB::raw('COUNT(DISTINCT wishlists.id) as wishlists_count'),
                DB::raw('COUNT(DISTINCT price_alerts.id) as price_alerts_count'),
                DB::raw('COUNT(DISTINCT reviews.id) as reviews_count')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('products_count', 'desc')
            ->take(10)
            ->get()
            ->toArray();
    }

    /**
     * Get brand performance.
     *
     * @return array<string, mixed>
     */
    private function getBrandPerformance(Carbon $startDate, Carbon $endDate): array
    {
        return DB::table('products')
            ->join('brands', 'products.brand_id', '=', 'brands.id')
            ->leftJoin('wishlists', 'products.id', '=', 'wishlists.product_id')
            ->leftJoin('price_alerts', 'products.id', '=', 'price_alerts.product_id')
            ->leftJoin('reviews', 'products.id', '=', 'reviews.product_id')
            ->whereBetween('products.created_at', [$startDate, $endDate])
            ->select(
                'brands.id',
                'brands.name',
                DB::raw('COUNT(DISTINCT products.id) as products_count'),
                DB::raw('COUNT(DISTINCT wishlists.id) as wishlists_count'),
                DB::raw('COUNT(DISTINCT price_alerts.id) as price_alerts_count'),
                DB::raw('COUNT(DISTINCT reviews.id) as reviews_count')
            )
            ->groupBy('brands.id', 'brands.name')
            ->orderBy('products_count', 'desc')
            ->take(10)
            ->get()
            ->toArray();
    }

    /**
     * Get monthly breakdown.
     *
     * @return list<array<string, mixed>>
     */
    private function getMonthlyBreakdown(Carbon $startDate, Carbon $endDate): array
    {
        $breakdown = [];
        $current = $startDate->copy()->startOfMonth();

        while ($current->lte($endDate)) {
            $monthEnd = $current->copy()->endOfMonth();
            if ($monthEnd->gt($endDate)) {
                $monthEnd = $endDate;
            }

            $breakdown[] = $this->getMonthlyStats($current);
            $current->addMonth();
        }

        return $breakdown;
    }

    /**
     * Get quarterly breakdown.
     *
     * @return list<array<string, int|string>>
     */
    private function getQuarterlyBreakdown(Carbon $startDate, Carbon $endDate): array
    {
        $quarters = [];
        $current = $startDate->copy()->startOfQuarter();

        while ($current->lte($endDate)) {
            $quarterEnd = $current->copy()->endOfQuarter();
            if ($quarterEnd->gt($endDate)) {
                $quarterEnd = $endDate;
            }

            $quarters[] = [
                'quarter' => $current->quarter,
                'year' => $current->year,
                'start_date' => $current->format('Y-m-d'),
                'end_date' => $quarterEnd->format('Y-m-d'),
                'new_users' => User::whereBetween('created_at', [$current, $quarterEnd])->count(),
                'new_products' => Product::whereBetween('created_at', [$current, $quarterEnd])->count(),
                'new_offers' => PriceOffer::whereBetween('created_at', [$current, $quarterEnd])->count(),
                'new_reviews' => Review::whereBetween('created_at', [$current, $quarterEnd])->count(),
            ];

            $current->addQuarter();
        }

        return $quarters;
    }

    /**
     * Get year over year growth.
     *
     * @return array<string, mixed>
     */
    private function getYearOverYearGrowth(int $year): array
    {
        $currentYear = Carbon::createFromDate($year, 1, 1);
        $previousYear = $currentYear->copy()->subYear();

        $currentYearEnd = $currentYear->copy()->endOfYear();
        $previousYearEnd = $previousYear->copy()->endOfYear();

        $currentUsers = User::whereBetween('created_at', [$currentYear, $currentYearEnd])->count();
        $previousUsers = User::whereBetween('created_at', [$previousYear, $previousYearEnd])->count();

        $currentProducts = Product::whereBetween('created_at', [$currentYear, $currentYearEnd])->count();
        $previousProducts = Product::whereBetween('created_at', [$previousYear, $previousYearEnd])->count();

        return [
            'users_growth' => $this->calculateGrowthRate($previousUsers, $currentUsers),
            'products_growth' => $this->calculateGrowthRate($previousProducts, $currentProducts),
        ];
    }

    /**
     * Get price analysis.
     *
     * @return array<string, mixed>
     */
    private function getPriceAnalysis(Carbon $startDate, Carbon $endDate): array
    {
        $offers = PriceOffer::whereBetween('created_at', [$startDate, $endDate])->get();

        if ($offers->isEmpty()) {
            return [
                'total_offers' => 0,
                'average_price' => 0,
                'price_range' => ['min' => 0, 'max' => 0],
                'price_volatility' => 0,
            ];
        }

        $prices = $offers->pluck('price')->toArray();

        return [
            'total_offers' => $offers->count(),
            'average_price' => array_sum($prices) / count($prices),
            'price_range' => [
                'min' => ! empty($prices) ? min($prices) : 0,
                'max' => ! empty($prices) ? max($prices) : 0,
            ],
            'price_volatility' => $this->calculatePriceVolatility($prices),
        ];
    }

    /**
     * Get product price range.
     *
     * @return array<string, mixed>
     */
    private function getProductPriceRange(int $productId): array
    {
        $offers = PriceOffer::where('product_id', $productId)->get();

        if ($offers->isEmpty()) {
            return ['min' => 0, 'max' => 0];
        }

        $prices = $offers->pluck('price')->toArray();

        return [
            'min' => ! empty($prices) ? min($prices) : 0,
            'max' => ! empty($prices) ? max($prices) : 0,
        ];
    }

    /**
     * Get product view count.
     */
    private function getProductViewCount(int $productId): int
    {
        return AuditLog::where('event', 'viewed')
            ->where('auditable_type', 'App\Models\Product')
            ->where('auditable_id', $productId)
            ->count();
    }

    /**
     * Calculate product engagement score.
     */
    private function calculateProductEngagementScore(int $productId): float
    {
        $wishlistCount = Wishlist::where('product_id', $productId)->count();
        $priceAlertsCount = PriceAlert::where('product_id', $productId)->count();
        $reviewsCount = Review::where('product_id', $productId)->count();
        $viewCount = $this->getProductViewCount($productId);

        return ($wishlistCount * 2) + ($priceAlertsCount * 3) + ($reviewsCount * 4) + ($viewCount * 0.1);
    }

    /**
     * Calculate user activity score.
     */
    private function calculateUserActivityScore(int $userId): float
    {
        $wishlistCount = Wishlist::where('user_id', $userId)->count();
        $priceAlertsCount = PriceAlert::where('user_id', $userId)->count();
        $reviewsCount = Review::where('user_id', $userId)->count();

        return ($wishlistCount * 1) + ($priceAlertsCount * 2) + ($reviewsCount * 3);
    }

    /**
     * Get user favorite categories.
     *
     * @return array<string, mixed>
     */
    private function getUserFavoriteCategories(int $userId): array
    {
        return DB::table('wishlists')
            ->join('products', 'wishlists.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('wishlists.user_id', $userId)
            ->select('categories.id', 'categories.name', DB::raw('COUNT(*) as count'))
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('count', 'desc')
            ->take(5)
            ->get()
            ->toArray();
    }

    /**
     * Get user favorite brands.
     *
     * @return array<string, mixed>
     */
    private function getUserFavoriteBrands(int $userId): array
    {
        return DB::table('wishlists')
            ->join('products', 'wishlists.product_id', '=', 'products.id')
            ->join('brands', 'products.brand_id', '=', 'brands.id')
            ->where('wishlists.user_id', $userId)
            ->select('brands.id', 'brands.name', DB::raw('COUNT(*) as count'))
            ->groupBy('brands.id', 'brands.name')
            ->orderBy('count', 'desc')
            ->take(5)
            ->get()
            ->toArray();
    }

    /**
     * Get user last activity.
     */
    private function getUserLastActivity(int $userId): ?string
    {
        $lastWishlist = Wishlist::where('user_id', $userId)->latest()->first();
        $lastPriceAlert = PriceAlert::where('user_id', $userId)->latest()->first();
        $lastReview = Review::where('user_id', $userId)->latest()->first();

        $activities = collect([$lastWishlist, $lastPriceAlert, $lastReview])
            ->filter()
            ->sortByDesc('created_at');

        return $activities->first()?->created_at?->format('Y-m-d H:i:s');
    }

    /**
     * Get database health.
     *
     * @return array<string, mixed>
     */
    private function getDatabaseHealth(): array
    {
        try {
            $connectionCount = DB::select('SHOW STATUS LIKE "Threads_connected"')[0]->Value ?? 0;
            $maxConnections = DB::select('SHOW VARIABLES LIKE "max_connections"')[0]->Value ?? 0;

            return [
                'status' => 'healthy',
                'connection_usage' => ($connectionCount / $maxConnections) * 100,
                'connection_count' => $connectionCount,
                'max_connections' => $maxConnections,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get cache health.
     *
     * @return array<string, mixed>
     */
    private function getCacheHealth(): array
    {
        try {
            $testKey = 'health_check_'.time();
            Cache::put($testKey, 'test', 60);
            $retrieved = Cache::get($testKey);
            Cache::forget($testKey);

            return [
                'status' => $retrieved === 'test' ? 'healthy' : 'unhealthy',
                'driver' => config('cache.default'),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get queue health.
     *
     * @return array<string, mixed>
     */
    private function getQueueHealth(): array
    {
        try {
            $queueSize = DB::table('jobs')->count();
            $failedJobs = DB::table('failed_jobs')->count();

            return [
                'status' => $failedJobs < 10 ? 'healthy' : 'unhealthy',
                'queue_size' => $queueSize,
                'failed_jobs' => $failedJobs,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get storage health.
     *
     * @return array<string, mixed>
     */
    private function getStorageHealth(): array
    {
        try {
            $disk = \Storage::disk('public');
            $totalSpace = disk_total_space($disk->path(''));
            $freeSpace = disk_free_space($disk->path(''));
            $usedSpace = $totalSpace - $freeSpace;

            return [
                'status' => ($usedSpace / $totalSpace) < 0.9 ? 'healthy' : 'unhealthy',
                'total_space' => $totalSpace,
                'used_space' => $usedSpace,
                'free_space' => $freeSpace,
                'usage_percentage' => ($usedSpace / $totalSpace) * 100,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get API health.
     *
     * @return array<string, mixed>
     */
    private function getApiHealth(): array
    {
        try {
            $response = \Http::timeout(5)->get(config('app.url').'/health');

            return [
                'status' => $response->successful() ? 'healthy' : 'unhealthy',
                'response_time' => $response->transferStats?->getHandlerStat('total_time') ?? 0,
                'status_code' => $response->status(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get error rate.
     */
    private function getErrorRate(): float
    {
        $totalRequests = AuditLog::where('event', 'api_access')->count();
        $errorRequests = AuditLog::where('event', 'api_access')
            ->whereJsonContains('metadata->status_code', 400)
            ->count();

        return $totalRequests > 0 ? ($errorRequests / $totalRequests) * 100 : 0;
    }

    /**
     * Get average response time.
     */
    private function getAverageResponseTime(): float
    {
        $responseTimes = AuditLog::where('event', 'api_access')
            ->whereNotNull('metadata->response_time')
            ->get()
            ->pluck('metadata.response_time')
            ->filter()
            ->values()
            ->toArray();

        return ! empty($responseTimes) ? array_sum($responseTimes) / count($responseTimes) : 0;
    }

    /**
     * Get uptime.
     */
    private function getUptime(): string
    {
        $startTime = config('app.start_time', now()->subDay());
        $uptime = now()->diffInSeconds($startTime);

        $days = floor($uptime / 86400);
        $hours = floor(($uptime % 86400) / 3600);
        $minutes = floor(($uptime % 3600) / 60);

        return "{$days}d {$hours}h {$minutes}m";
    }

    /**
     * Calculate price volatility.
     *
     * @param  array<float>  $prices
     */
    private function calculatePriceVolatility(array $prices): float
    {
        if (count($prices) < 2) {
            return 0;
        }

        $mean = array_sum($prices) / count($prices);
        $variance = array_sum(array_map(function ($price) use ($mean) {
            return pow($price - $mean, 2);
        }, $prices)) / count($prices);

        return sqrt($variance);
    }

    /**
     * Calculate growth rate.
     */
    private function calculateGrowthRate(int $previous, int $current): float
    {
        if ($previous === 0) {
            return $current > 0 ? 100 : 0;
        }

        return (($current - $previous) / $previous) * 100;
    }
}
