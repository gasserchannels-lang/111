<?php

namespace App\Services;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RecommendationService
{
    public function getRecommendations(User $user, int $limit = 10): array
    {
        $cacheKey = "recommendations_user_{$user->id}";

        return Cache::remember($cacheKey, 3600, function () use ($user, $limit) {
            $recommendations = collect();

            // Collaborative Filtering
            $collaborativeRecs = $this->getCollaborativeRecommendations($user, $limit);
            $recommendations = $recommendations->merge($collaborativeRecs);

            // Content-Based Filtering
            $contentRecs = $this->getContentBasedRecommendations($user, $limit);
            $recommendations = $recommendations->merge($contentRecs);

            // Trending Products
            $trendingRecs = $this->getTrendingRecommendations($limit);
            $recommendations = $recommendations->merge($trendingRecs);

            // Remove duplicates and user's purchased items
            $purchasedProductIds = $this->getPurchasedProductIds($user);

            return $recommendations
                ->unique('id')
                ->reject(fn ($product) => in_array($product->id, $purchasedProductIds))
                ->take($limit)
                ->values()
                ->toArray();
        });
    }

    private function getCollaborativeRecommendations(User $user, int $limit): array
    {
        // Find users with similar purchase patterns
        $similarUsers = $this->findSimilarUsers($user);

        if ($similarUsers->isEmpty()) {
            return [];
        }

        $similarUserIds = $similarUsers->pluck('user_id')->toArray();

        // Get products purchased by similar users but not by current user
        $purchasedProductIds = $this->getPurchasedProductIds($user);

        return Product::whereHas('orderItems.order', function ($query) use ($similarUserIds) {
            $query->whereIn('user_id', $similarUserIds);
        })
            ->whereNotIn('id', $purchasedProductIds)
            ->withCount(['orderItems as purchase_count' => function ($query) use ($similarUserIds) {
                $query->whereHas('order', function ($q) use ($similarUserIds) {
                    $q->whereIn('user_id', $similarUserIds);
                });
            }])
            ->orderBy('purchase_count', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    private function getContentBasedRecommendations(User $user, int $limit): array
    {
        $userPreferences = $this->getUserPreferences($user);

        if (empty($userPreferences)) {
            return [];
        }

        $query = Product::query();

        // Filter by preferred categories
        if (! empty($userPreferences['categories'])) {
            $query->whereIn('category_id', $userPreferences['categories']);
        }

        // Filter by price range
        if (isset($userPreferences['price_range'])) {
            $query->whereBetween('price', $userPreferences['price_range']);
        }

        // Filter by preferred brands
        if (! empty($userPreferences['brands'])) {
            $query->whereIn('brand_id', $userPreferences['brands']);
        }

        return $query
            ->where('is_active', true)
            ->orderBy('rating', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    private function getTrendingRecommendations(int $limit): array
    {
        return Product::where('is_active', true)
            ->withCount(['orderItems as recent_purchases' => function ($query) {
                $query->whereHas('order', function ($q) {
                    $q->where('created_at', '>=', now()->subDays(7));
                });
            }])
            ->orderBy('recent_purchases', 'desc')
            ->orderBy('rating', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    private function findSimilarUsers(User $user): \Illuminate\Support\Collection
    {
        $userPurchases = $this->getUserPurchaseHistory($user);

        if ($userPurchases->isEmpty()) {
            return collect();
        }

        $userProductIds = $userPurchases->pluck('product_id')->toArray();

        return DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.user_id', '!=', $user->id)
            ->whereIn('order_items.product_id', $userProductIds)
            ->select('orders.user_id')
            ->selectRaw('COUNT(DISTINCT order_items.product_id) as common_products')
            ->groupBy('orders.user_id')
            ->having('common_products', '>=', 2)
            ->orderBy('common_products', 'desc')
            ->limit(10)
            ->get();
    }

    private function getUserPurchaseHistory(User $user): \Illuminate\Support\Collection
    {
        return OrderItem::whereHas('order', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->select('product_id')
            ->distinct()
            ->get();
    }

    private function getPurchasedProductIds(User $user): array
    {
        return OrderItem::whereHas('order', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->pluck('product_id')
            ->toArray();
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

        $categories = $purchases->pluck('product.category_id')->filter()->unique()->toArray();
        $brands = $purchases->pluck('product.brand_id')->filter()->unique()->toArray();
        $prices = $purchases->pluck('product.price')->filter()->toArray();

        return [
            'categories' => $categories,
            'brands' => $brands,
            'price_range' => $prices ? [min($prices), max($prices)] : null,
        ];
    }

    public function getSimilarProducts(Product $product, int $limit = 5): array
    {
        return Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->orderBy('rating', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getFrequentlyBoughtTogether(Product $product, int $limit = 5): array
    {
        $productIds = OrderItem::whereHas('order', function ($query) use ($product) {
            $query->whereHas('items', function ($q) use ($product) {
                $q->where('product_id', $product->id);
            });
        })
            ->where('product_id', '!=', $product->id)
            ->select('product_id')
            ->selectRaw('COUNT(*) as frequency')
            ->groupBy('product_id')
            ->orderBy('frequency', 'desc')
            ->limit($limit)
            ->pluck('product_id')
            ->toArray();

        return Product::whereIn('id', $productIds)
            ->where('is_active', true)
            ->get()
            ->toArray();
    }
}
