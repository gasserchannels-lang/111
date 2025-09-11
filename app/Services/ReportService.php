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
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Generate product performance report.
     *
     * @return array<string, mixed>
     */
    public function generateProductPerformanceReport(int $productId, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->subMonth();
        $endDate = $endDate ?? now();

        $product = Product::findOrFail($productId);

        return [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'current_price' => $product->price,
                'category' => $product->category->name ?? 'N/A',
                'brand' => $product->brand->name ?? 'N/A',
            ],
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'price_analysis' => $this->getPriceAnalysis($productId, $startDate, $endDate),
            'offer_analysis' => $this->getOfferAnalysis($productId, $startDate, $endDate),
            'user_engagement' => $this->getUserEngagement($productId, $startDate, $endDate),
            'reviews_analysis' => $this->getReviewsAnalysis($productId, $startDate, $endDate),
        ];
    }

    /**
     * Generate user activity report.
     *
     * @return array<string, mixed>
     */
    public function generateUserActivityReport(int $userId, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->subMonth();
        $endDate = $endDate ?? now();

        $user = User::findOrFail($userId);

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at?->format('Y-m-d H:i:s') ?? 'N/A',
            ],
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'activity_summary' => $this->getUserActivitySummary($userId, $startDate, $endDate),
            'wishlist_activity' => $this->getWishlistActivity($userId, $startDate, $endDate),
            'price_alerts' => $this->getPriceAlertsActivity($userId, $startDate, $endDate),
            'reviews_activity' => $this->getReviewsActivity($userId, $startDate, $endDate),
        ];
    }

    /**
     * Generate system performance report.
     *
     * @return array<string, mixed>
     */
    public function generateSystemPerformanceReport(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->subMonth();
        $endDate = $endDate ?? now();

        return [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'overview' => $this->getSystemOverview($startDate, $endDate),
            'user_metrics' => $this->getUserMetrics($startDate, $endDate),
            'product_metrics' => $this->getProductMetrics($startDate, $endDate),
            'engagement_metrics' => $this->getEngagementMetrics($startDate, $endDate),
            'performance_metrics' => $this->getPerformanceMetrics($startDate, $endDate),
        ];
    }

    /**
     * Generate sales report.
     *
     * @return array<string, mixed>
     */
    public function generateSalesReport(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->subMonth();
        $endDate = $endDate ?? now();

        return [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'total_offers' => PriceOffer::whereBetween('created_at', [$startDate, $endDate])->count(),
            'price_changes' => $this->getPriceChanges($startDate, $endDate),
            'top_products' => $this->getTopProducts($startDate, $endDate),
            'top_stores' => $this->getTopStores($startDate, $endDate),
            'price_trends' => $this->getPriceTrends($startDate, $endDate),
        ];
    }

    /**
     * Get price analysis for a product.
     *
     * @return array<string, mixed>
     */
    private function getPriceAnalysis(int $productId, Carbon $startDate, Carbon $endDate): array
    {
        $offers = PriceOffer::where('product_id', $productId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at')
            ->get();

        if ($offers->isEmpty()) {
            return [
                'total_offers' => 0,
                'price_range' => ['min' => 0, 'max' => 0],
                'average_price' => 0,
                'price_volatility' => 0,
            ];
        }

        $prices = $offers->pluck('price')->toArray();
        $priceChanges = [];

        for ($i = 1; $i < count($prices); $i++) {
            $priceChanges[] = $prices[$i] - $prices[$i - 1];
        }

        return [
            'total_offers' => $offers->count(),
            'price_range' => [
                'min' => ! empty($prices) ? min($prices) : 0,
                'max' => ! empty($prices) ? max($prices) : 0,
            ],
            'average_price' => array_sum($prices) / count($prices),
            'price_volatility' => count($priceChanges) > 0 ? array_sum($priceChanges) / count($priceChanges) : 0,
            'price_trend' => $this->calculatePriceTrend($prices),
        ];
    }

    /**
     * Get offer analysis for a product.
     *
     * @return array<string, mixed>
     */
    private function getOfferAnalysis(int $productId, Carbon $startDate, Carbon $endDate): array
    {
        $offers = PriceOffer::where('product_id', $productId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('store')
            ->get();

        $storeCounts = $offers->groupBy('store_id')->map->count();
        $availabilityCounts = $offers->groupBy('is_available')->map->count();

        return [
            'total_offers' => $offers->count(),
            'unique_stores' => $storeCounts->count(),
            'available_offers' => $availabilityCounts->get('1', 0),
            'unavailable_offers' => $availabilityCounts->get('0', 0),
            'top_stores' => $storeCounts->sortDesc()->take(5)->toArray(),
        ];
    }

    /**
     * Get user engagement for a product.
     *
     * @return array<string, mixed>
     */
    private function getUserEngagement(int $productId, Carbon $startDate, Carbon $endDate): array
    {
        $wishlists = Wishlist::where('product_id', $productId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $priceAlerts = PriceAlert::where('product_id', $productId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $reviews = Review::where('product_id', $productId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        return [
            'wishlist_adds' => $wishlists,
            'price_alerts' => $priceAlerts,
            'reviews' => $reviews,
            'total_engagement' => $wishlists + $priceAlerts + $reviews,
        ];
    }

    /**
     * Get reviews analysis for a product.
     *
     * @return array<string, mixed>
     */
    private function getReviewsAnalysis(int $productId, Carbon $startDate, Carbon $endDate): array
    {
        $reviews = Review::where('product_id', $productId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        if ($reviews->isEmpty()) {
            return [
                'total_reviews' => 0,
                'average_rating' => 0,
                'rating_distribution' => [],
            ];
        }

        $ratings = $reviews->pluck('rating')->toArray();
        $ratingDistribution = array_count_values($ratings);

        return [
            'total_reviews' => $reviews->count(),
            'average_rating' => array_sum($ratings) / count($ratings),
            'rating_distribution' => $ratingDistribution,
            'approved_reviews' => $reviews->where('is_approved', true)->count(),
            'pending_reviews' => $reviews->where('is_approved', false)->count(),
        ];
    }

    /**
     * Get user activity summary.
     *
     * @return array<string, mixed>
     */
    private function getUserActivitySummary(int $userId, Carbon $startDate, Carbon $endDate): array
    {
        $wishlists = Wishlist::where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $priceAlerts = PriceAlert::where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $reviews = Review::where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        return [
            'wishlist_adds' => $wishlists,
            'price_alerts_created' => $priceAlerts,
            'reviews_written' => $reviews,
            'total_activity' => $wishlists + $priceAlerts + $reviews,
        ];
    }

    /**
     * Get wishlist activity.
     *
     * @return array<string, mixed>
     */
    private function getWishlistActivity(int $userId, Carbon $startDate, Carbon $endDate): array
    {
        $wishlists = Wishlist::where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('product')
            ->get();

        return [
            'total_items' => $wishlists->count(),
            'products' => $wishlists->map(function ($wishlist) {
                return [
                    'id' => $wishlist->product->id ?? 0,
                    'name' => $wishlist->product->name ?? 'Unknown Product',
                    'price' => $wishlist->product->price ?? 0,
                    'added_at' => $wishlist->created_at?->format('Y-m-d H:i:s') ?? 'N/A',
                ];
            })->toArray(),
        ];
    }

    /**
     * Get price alerts activity.
     *
     * @return array<string, mixed>
     */
    private function getPriceAlertsActivity(int $userId, Carbon $startDate, Carbon $endDate): array
    {
        $alerts = PriceAlert::where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('product')
            ->get();

        return [
            'total_alerts' => $alerts->count(),
            'active_alerts' => $alerts->where('is_active', true)->count(),
            'alerts' => $alerts->map(function ($alert) {
                return [
                    'id' => $alert->id,
                    'product_name' => $alert->product->name ?? 'Unknown Product',
                    'target_price' => $alert->target_price,
                    'current_price' => $alert->product->price ?? 0,
                    'created_at' => $alert->created_at?->format('Y-m-d H:i:s') ?? 'N/A',
                ];
            })->toArray(),
        ];
    }

    /**
     * Get reviews activity.
     *
     * @return array<string, mixed>
     */
    private function getReviewsActivity(int $userId, Carbon $startDate, Carbon $endDate): array
    {
        $reviews = Review::where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('product')
            ->get();

        return [
            'total_reviews' => $reviews->count(),
            'average_rating' => $reviews->avg('rating'),
            'reviews' => $reviews->map(function ($review) {
                return [
                    'id' => $review->id,
                    'product_name' => $review->product->name ?? 'Unknown Product',
                    'rating' => $review->rating,
                    'content' => $review->content,
                    'is_approved' => $review->is_approved,
                    'created_at' => $review->created_at?->format('Y-m-d H:i:s') ?? 'N/A',
                ];
            })->toArray(),
        ];
    }

    /**
     * Get system overview.
     *
     * @return array<string, mixed>
     */
    private function getSystemOverview(Carbon $startDate, Carbon $endDate): array
    {
        return [
            'total_users' => User::count(),
            'new_users' => User::whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_products' => Product::count(),
            'new_products' => Product::whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_offers' => PriceOffer::count(),
            'new_offers' => PriceOffer::whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_reviews' => Review::count(),
            'new_reviews' => Review::whereBetween('created_at', [$startDate, $endDate])->count(),
        ];
    }

    /**
     * Get user metrics.
     *
     * @return array<string, mixed>
     */
    private function getUserMetrics(Carbon $startDate, Carbon $endDate): array
    {
        $users = User::whereBetween('created_at', [$startDate, $endDate])->get();

        return [
            'new_users' => $users->count(),
            'active_users' => User::whereHas('wishlists')->orWhereHas('priceAlerts')->orWhereHas('reviews')->count(),
            'admin_users' => User::where('is_admin', true)->count(),
            'user_growth_rate' => $this->calculateGrowthRate(
                User::where('created_at', '<', $startDate)->count(),
                $users->count()
            ),
        ];
    }

    /**
     * Get product metrics.
     *
     * @return array<string, mixed>
     */
    private function getProductMetrics(Carbon $startDate, Carbon $endDate): array
    {
        $products = Product::whereBetween('created_at', [$startDate, $endDate])->get();

        return [
            'new_products' => $products->count(),
            'active_products' => Product::where('is_active', true)->count(),
            'products_with_offers' => Product::whereHas('priceOffers')->count(),
            'products_with_reviews' => Product::whereHas('reviews')->count(),
            'average_price' => Product::avg('price'),
            'price_range' => [
                'min' => Product::min('price'),
                'max' => Product::max('price'),
            ],
        ];
    }

    /**
     * Get engagement metrics.
     *
     * @return array<string, mixed>
     */
    private function getEngagementMetrics(Carbon $startDate, Carbon $endDate): array
    {
        return [
            'total_wishlists' => Wishlist::count(),
            'new_wishlists' => Wishlist::whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_price_alerts' => PriceAlert::count(),
            'new_price_alerts' => PriceAlert::whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_reviews' => Review::count(),
            'new_reviews' => Review::whereBetween('created_at', [$startDate, $endDate])->count(),
            'average_rating' => Review::avg('rating'),
        ];
    }

    /**
     * Get performance metrics.
     *
     * @return array<string, mixed>
     */
    private function getPerformanceMetrics(Carbon $startDate, Carbon $endDate): array
    {
        return [
            'total_audit_logs' => AuditLog::count(),
            'new_audit_logs' => AuditLog::whereBetween('created_at', [$startDate, $endDate])->count(),
            'most_active_users' => $this->getMostActiveUsers($startDate, $endDate),
            'most_viewed_products' => $this->getMostViewedProducts($startDate, $endDate),
        ];
    }

    /**
     * Get price changes.
     *
     * @return array<string, mixed>
     */
    private function getPriceChanges(Carbon $startDate, Carbon $endDate): array
    {
        $priceChanges = DB::table('audit_logs')
            ->where('event', 'updated')
            ->where('auditable_type', 'App\Models\Product')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereJsonContains('metadata->reason', 'Updated from lowest price offer')
            ->count();

        return [
            'total_changes' => $priceChanges,
            'average_daily_changes' => $priceChanges / $startDate->diffInDays($endDate),
        ];
    }

    /**
     * Get top products.
     *
     * @return array<string, mixed>
     */
    private function getTopProducts(Carbon $startDate, Carbon $endDate): array
    {
        return Product::withCount(['wishlists', 'priceAlerts', 'reviews'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('wishlists_count', 'desc')
            ->orderBy('price_alerts_count', 'desc')
            ->orderBy('reviews_count', 'desc')
            ->take(10)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'wishlists_count' => $product->wishlists_count,
                    'price_alerts_count' => $product->price_alerts_count,
                    'reviews_count' => $product->reviews_count,
                ];
            })
            ->toArray();
    }

    /**
     * Get top stores.
     *
     * @return array<string, mixed>
     */
    private function getTopStores(Carbon $startDate, Carbon $endDate): array
    {
        return DB::table('price_offers')
            ->join('stores', 'price_offers.store_id', '=', 'stores.id')
            ->whereBetween('price_offers.created_at', [$startDate, $endDate])
            ->select('stores.id', 'stores.name', DB::raw('COUNT(*) as offers_count'))
            ->groupBy('stores.id', 'stores.name')
            ->orderBy('offers_count', 'desc')
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
        $trends = DB::table('price_offers')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('AVG(price) as average_price'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $trends->map(function ($trend) {
            return [
                'date' => $trend->date,
                'average_price' => round($trend->average_price, 2),
            ];
        })->toArray();
    }

    /**
     * Get most active users.
     *
     * @return array<string, mixed>
     */
    private function getMostActiveUsers(Carbon $startDate, Carbon $endDate): array
    {
        return User::withCount(['wishlists', 'priceAlerts', 'reviews'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('wishlists_count', 'desc')
            ->orderBy('price_alerts_count', 'desc')
            ->orderBy('reviews_count', 'desc')
            ->take(10)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'wishlists_count' => $user->wishlists_count,
                    'price_alerts_count' => $user->price_alerts_count,
                    'reviews_count' => $user->reviews_count,
                ];
            })
            ->toArray();
    }

    /**
     * Get most viewed products.
     *
     * @return array<string, mixed>
     */
    private function getMostViewedProducts(Carbon $startDate, Carbon $endDate): array
    {
        return DB::table('audit_logs')
            ->join('products', 'audit_logs.auditable_id', '=', 'products.id')
            ->where('audit_logs.event', 'viewed')
            ->where('audit_logs.auditable_type', 'App\Models\Product')
            ->whereBetween('audit_logs.created_at', [$startDate, $endDate])
            ->select('products.id', 'products.name', DB::raw('COUNT(*) as view_count'))
            ->groupBy('products.id', 'products.name')
            ->orderBy('view_count', 'desc')
            ->take(10)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'view_count' => $item->view_count,
                ];
            })
            ->toArray();
    }

    /**
     * Calculate price trend.
     *
     * @param  array<float>  $prices
     */
    private function calculatePriceTrend(array $prices): string
    {
        if (count($prices) < 2) {
            return 'stable';
        }

        $firstPrice = $prices[0];
        $lastPrice = end($prices);
        $change = $lastPrice - $firstPrice;
        $percentage = ($change / $firstPrice) * 100;

        if ($percentage > 5) {
            return 'increasing';
        } elseif ($percentage < -5) {
            return 'decreasing';
        } else {
            return 'stable';
        }
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
