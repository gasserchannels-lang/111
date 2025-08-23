<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'logo',
        'website_url',
        'affiliate_base_url',
        'supported_countries',
        'api_config',
        'is_active',
        'priority',
    ];

    protected $casts = [
        'supported_countries' => 'array',
        'api_config' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * علاقة مع عروض الأسعار
     */
    public function priceOffers(): HasMany
    {
        return $this->hasMany(PriceOffer::class);
    }

    /**
     * التحقق من دعم المتجر لدولة معينة
     */
    public function supportsCountry(string $countryCode): bool
    {
        return in_array($countryCode, $this->supported_countries ?? []);
    }

    /**
     * الحصول على المتاجر النشطة المدعومة لدولة معينة
     */
    public static function getActiveForCountry(string $countryCode)
    {
        return static::where('is_active', true)
            ->whereJsonContains('supported_countries', $countryCode)
            ->orderBy('priority', 'desc')
            ->get();
    }

    /**
     * إنشاء رابط الأفلييت للمنتج
     */
    public function generateAffiliateUrl(string $productUrl): string
    {
        if (! $this->affiliate_base_url) {
            return $productUrl;
        }

        // يمكن تخصيص هذه الدالة حسب نظام الأفلييت لكل متجر
        return $this->affiliate_base_url.'?url='.urlencode($productUrl);
    }
}
