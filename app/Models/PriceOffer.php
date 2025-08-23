<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'product_name',
        'product_code',
        'product_sku',
        'store_id',
        'price',
        'currency',
        'product_url',
        'affiliate_url',
        'in_stock',
        'stock_quantity',
        'condition',
        'rating',
        'reviews_count',
        'image_url',
        'specifications',
        'last_updated_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'rating' => 'decimal:2',
        'in_stock' => 'boolean',
        'specifications' => 'array',
        'last_updated_at' => 'datetime',
    ];

    /**
     * علاقة مع المنتج
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * علاقة مع المتجر
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * البحث عن المنتجات بالاسم أو الكود
     */
    public function scopeSearchProduct(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('product_name', 'LIKE', "%{$search}%")
                ->orWhere('product_code', 'LIKE', "%{$search}%")
                ->orWhere('product_sku', 'LIKE', "%{$search}%");
        });
    }

    /**
     * فلترة المنتجات المتوفرة في المخزن
     */
    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('in_stock', true);
    }

    /**
     * فلترة حسب المتاجر المدعومة لدولة معينة
     */
    public function scopeForCountry(Builder $query, string $countryCode): Builder
    {
        return $query->whereHas('store', function ($q) use ($countryCode) {
            $q->where('is_active', true)
                ->whereJsonContains('supported_countries', $countryCode);
        });
    }

    /**
     * ترتيب حسب السعر من الأرخص
     */
    public function scopeOrderByPrice(Builder $query): Builder
    {
        return $query->orderBy('price', 'asc');
    }

    /**
     * البحث الذكي عن المنتجات مع ترتيب الأسعار
     */
    public static function searchProductOffers(string $search, string $countryCode = 'US', int $limit = 20)
    {
        return static::searchProduct($search)
            ->inStock()
            ->forCountry($countryCode)
            ->with('store')
            ->orderByPrice()
            ->limit($limit)
            ->get();
    }

    /**
     * الحصول على أفضل عرض سعر لمنتج معين
     */
    public static function getBestOffer(string $productIdentifier, string $countryCode = 'US')
    {
        return static::searchProduct($productIdentifier)
            ->inStock()
            ->forCountry($countryCode)
            ->with('store')
            ->orderByPrice()
            ->first();
    }
}
