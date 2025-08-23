<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PriceOffer;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PriceSearchController extends Controller
{
    /**
     * البحث الذكي عن المنتجات مع ترتيب الأسعار
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2|max:255',
            'country' => 'nullable|string|size:2',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $query = $request->input('query');
        $country = $request->input('country', $this->detectUserCountry($request));
        $limit = $request->input('limit', 20);

        try {
            // البحث عن المنتجات
            $offers = PriceOffer::searchProductOffers($query, $country, $limit);

            // تجميع النتائج حسب اسم المنتج
            $groupedOffers = $this->groupOffersByProduct($offers);

            return response()->json([
                'success' => true,
                'data' => [
                    'query' => $query,
                    'country' => $country,
                    'total_products' => count($groupedOffers),
                    'total_offers' => $offers->count(),
                    'products' => $groupedOffers,
                ],
                'message' => 'Search completed successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * الحصول على أفضل عرض سعر لمنتج معين
     */
    public function bestOffer(Request $request): JsonResponse
    {
        $request->validate([
            'product' => 'required|string|min:2|max:255',
            'country' => 'nullable|string|size:2',
        ]);

        $product = $request->input('product');
        $country = $request->input('country', $this->detectUserCountry($request));

        try {
            $bestOffer = PriceOffer::getBestOffer($product, $country);

            if (! $bestOffer) {
                return response()->json([
                    'success' => false,
                    'message' => 'No offers found for this product',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'product' => $product,
                    'country' => $country,
                    'best_offer' => $this->formatOffer($bestOffer),
                ],
                'message' => 'Best offer found successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * الحصول على قائمة المتاجر المدعومة لدولة معينة
     */
    public function supportedStores(Request $request): JsonResponse
    {
        $request->validate([
            'country' => 'nullable|string|size:2',
        ]);

        $country = $request->input('country', $this->detectUserCountry($request));

        try {
            $stores = Store::getActiveForCountry($country);

            return response()->json([
                'success' => true,
                'data' => [
                    'country' => $country,
                    'stores' => $stores->map(function ($store) {
                        return [
                            'id' => $store->id,
                            'name' => $store->name,
                            'slug' => $store->slug,
                            'logo' => $store->logo,
                            'website_url' => $store->website_url,
                            'priority' => $store->priority,
                        ];
                    }),
                ],
                'message' => 'Supported stores retrieved successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve stores: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * تجميع العروض حسب اسم المنتج
     */
    private function groupOffersByProduct($offers): array
    {
        $grouped = [];

        foreach ($offers as $offer) {
            $productKey = $this->normalizeProductName($offer->product_name);

            if (! isset($grouped[$productKey])) {
                $grouped[$productKey] = [
                    'product_name' => $offer->product_name,
                    'product_code' => $offer->product_code,
                    'image_url' => $offer->image_url,
                    'offers' => [],
                ];
            }

            $grouped[$productKey]['offers'][] = $this->formatOffer($offer);
        }

        // ترتيب المنتجات حسب أقل سعر
        uasort($grouped, function ($a, $b) {
            $minPriceA = min(array_column($a['offers'], 'price'));
            $minPriceB = min(array_column($b['offers'], 'price'));

            return $minPriceA <=> $minPriceB;
        });

        return array_values($grouped);
    }

    /**
     * تنسيق بيانات العرض
     */
    private function formatOffer($offer): array
    {
        return [
            'id' => $offer->id,
            'price' => (float) $offer->price,
            'currency' => $offer->currency,
            'product_url' => $offer->product_url,
            'affiliate_url' => $offer->affiliate_url ?: $offer->product_url,
            'in_stock' => $offer->in_stock,
            'stock_quantity' => $offer->stock_quantity,
            'condition' => $offer->condition,
            'rating' => $offer->rating ? (float) $offer->rating : null,
            'reviews_count' => $offer->reviews_count,
            'store' => [
                'id' => $offer->store->id,
                'name' => $offer->store->name,
                'slug' => $offer->store->slug,
                'logo' => $offer->store->logo,
                'website_url' => $offer->store->website_url,
            ],
            'last_updated_at' => $offer->last_updated_at->toISOString(),
        ];
    }

    /**
     * تطبيع اسم المنتج للتجميع
     */
    private function normalizeProductName(string $name): string
    {
        // إزالة الأحرف الخاصة والمسافات الزائدة
        $normalized = preg_replace('/[^\w\s-]/u', '', $name);
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        return strtolower(trim($normalized));
    }

    /**
     * اكتشاف دولة المستخدم من IP أو Headers
     */
    private function detectUserCountry(Request $request): string
    {
        // يمكن استخدام خدمة GeoIP أو CloudFlare headers
        $cloudflareCountry = $request->header('CF-IPCountry');
        if ($cloudflareCountry && strlen($cloudflareCountry) === 2) {
            return strtoupper($cloudflareCountry);
        }

        // افتراضي
        return 'US';
    }
}
