<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BehaviorAnalysisService
{
    public function trackUserBehavior(User $user, string $action, array $data = []): void
    {
        DB::table('user_behaviors')->insert([
            'user_id' => $user->id,
            'action' => $action,
            'data' => json_encode($data),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function getUserAnalytics(User $user): array
    {
        $cacheKey = "user_analytics_{$user->id}";

        return Cache::remember($cacheKey, 1800, function () use ($user) {
            return [
                'purchase_history' => $this->getPurchaseHistory($user),
                'browsing_patterns' => $this->getBrowsingPatterns($user),
                'preferences' => $this->getUserPreferences($user),
                'engagement_score' => $this->calculateEngagementScore($user),
                'lifetime_value' => $this->calculateLifetimeValue($user),
                'recommendation_score' => $this->calculateRecommendationScore($user),
            ];
        });
    }

    public function getSiteAnalytics(): array
    {
        $cacheKey = 'site_analytics';

        return Cache::remember($cacheKey, 3600, function () {
            return [
                'total_users' => User::count(),
                'active_users' => $this->getActiveUsersCount(),
                'total_orders' => Order::count(),
                'total_revenue' => Order::sum('total_amount'),
                'average_order_value' => Order::avg('total_amount'),
                'conversion_rate' => $this->getConversionRate(),
                'top_products' => $this->getTopProducts(),
                'user_segments' => $this->getUserSegments(),
            ];
        });
    }

    private function getPurchaseHistory(User $user): array
    {
        return Order::where('user_id', $user->id)
            ->with(['items.product'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($order) {
                return [
                    'order_number' => $order->order_number,
                    'total_amount' => $order->total_amount,
                    'status' => $order->status,
                    'created_at' => $order->created_at,
                    'products' => $order->items->map(function ($item) {
                        return [
                            'name' => $item->product->name,
                            'price' => $item->unit_price,
                            'quantity' => $item->quantity,
                        ];
                    }),
                ];
            })
            ->toArray();
    }

    private function getBrowsingPatterns(User $user): array
    {
        $behaviors = DB::table('user_behaviors')
            ->where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->get();

        $patterns = [
            'page_views' => $behaviors->where('action', 'page_view')->count(),
            'product_views' => $behaviors->where('action', 'product_view')->count(),
            'search_queries' => $behaviors->where('action', 'search')->count(),
            'cart_additions' => $behaviors->where('action', 'cart_add')->count(),
            'wishlist_additions' => $behaviors->where('action', 'wishlist_add')->count(),
        ];

        $patterns['most_viewed_categories'] = $this->getMostViewedCategories($user);
        $patterns['peak_activity_hours'] = $this->getPeakActivityHours($user);

        return $patterns;
    }

    private function getUserPreferences(User $user): array
    {
        $purchases = OrderItem::whereHas('order', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->with('product')
            ->get();

        if ($purchases->isEmpty()) {
            return [];
        }

        $categories = $purchases->groupBy('product.category_id')
            ->map(function ($items) {
                return $items->sum('quantity');
            })
            ->sortDesc()
            ->take(5);

        $brands = $purchases->groupBy('product.brand_id')
            ->map(function ($items) {
                return $items->sum('quantity');
            })
            ->sortDesc()
            ->take(5);

        $priceRange = $purchases->pluck('product.price');

        return [
            'preferred_categories' => $categories->keys()->toArray(),
            'preferred_brands' => $brands->keys()->toArray(),
            'price_range' => [
                'min' => $priceRange->min(),
                'max' => $priceRange->max(),
                'average' => $priceRange->avg(),
            ],
        ];
    }

    private function calculateEngagementScore(User $user): float
    {
        $behaviors = DB::table('user_behaviors')
            ->where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->get();

        $score = 0;

        // Page views (weight: 1)
        $score += $behaviors->where('action', 'page_view')->count() * 1;

        // Product views (weight: 2)
        $score += $behaviors->where('action', 'product_view')->count() * 2;

        // Search queries (weight: 3)
        $score += $behaviors->where('action', 'search')->count() * 3;

        // Cart additions (weight: 5)
        $score += $behaviors->where('action', 'cart_add')->count() * 5;

        // Purchases (weight: 10)
        $score += Order::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->count() * 10;

        return min($score / 100, 1.0); // Normalize to 0-1
    }

    private function calculateLifetimeValue(User $user): float
    {
        return Order::where('user_id', $user->id)
            ->where('status', '!=', 'cancelled')
            ->sum('total_amount');
    }

    private function calculateRecommendationScore(User $user): float
    {
        $engagementScore = $this->calculateEngagementScore($user);
        $lifetimeValue = $this->calculateLifetimeValue($user);
        $purchaseFrequency = $this->getPurchaseFrequency($user);

        return ($engagementScore * 0.4) + (min($lifetimeValue / 1000, 1) * 0.3) + (min($purchaseFrequency, 1) * 0.3);
    }

    private function getPurchaseFrequency(User $user): float
    {
        $firstPurchase = Order::where('user_id', $user->id)->min('created_at');

        if (! $firstPurchase) {
            return 0;
        }

        $daysSinceFirstPurchase = now()->diffInDays($firstPurchase);
        $totalPurchases = Order::where('user_id', $user->id)->count();

        return $daysSinceFirstPurchase > 0 ? $totalPurchases / $daysSinceFirstPurchase : 0;
    }

    private function getActiveUsersCount(): int
    {
        return User::whereHas('orders', function ($query) {
            $query->where('created_at', '>=', now()->subDays(30));
        })
            ->count();
    }

    private function getConversionRate(): float
    {
        $totalVisitors = DB::table('user_behaviors')
            ->where('action', 'page_view')
            ->where('created_at', '>=', now()->subDays(30))
            ->distinct('user_id')
            ->count();

        $totalPurchases = Order::where('created_at', '>=', now()->subDays(30))->count();

        return $totalVisitors > 0 ? ($totalPurchases / $totalVisitors) * 100 : 0;
    }

    private function getTopProducts(): array
    {
        return Product::withCount(['orderItems as purchase_count' => function ($query) {
            $query->whereHas('order', function ($q) {
                $q->where('created_at', '>=', now()->subDays(30));
            });
        }])
            ->orderBy('purchase_count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'purchase_count' => $product->purchase_count,
                ];
            })
            ->toArray();
    }

    private function getUserSegments(): array
    {
        return [
            'high_value' => User::whereHas('orders', function ($query) {
                $query->where('total_amount', '>', 500);
            })->count(),
            'frequent_buyers' => User::whereHas('orders', function ($query) {
                $query->where('created_at', '>=', now()->subDays(30));
            }, '>=', 3)->count(),
            'new_customers' => User::where('created_at', '>=', now()->subDays(30))->count(),
        ];
    }

    private function getMostViewedCategories(User $user): array
    {
        return DB::table('user_behaviors')
            ->where('user_id', $user->id)
            ->where('action', 'product_view')
            ->where('created_at', '>=', now()->subDays(30))
            ->join('products', function ($join) {
                $join->whereRaw("JSON_EXTRACT(user_behaviors.data, '$.product_id') = products.id");
            })
            ->select('products.category_id')
            ->selectRaw('COUNT(*) as view_count')
            ->groupBy('products.category_id')
            ->orderBy('view_count', 'desc')
            ->limit(5)
            ->pluck('category_id')
            ->toArray();
    }

    private function getPeakActivityHours(User $user): array
    {
        return DB::table('user_behaviors')
            ->where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('HOUR(created_at) as hour')
            ->selectRaw('COUNT(*) as activity_count')
            ->groupBy('hour')
            ->orderBy('activity_count', 'desc')
            ->limit(5)
            ->pluck('hour')
            ->toArray();
    }
}
