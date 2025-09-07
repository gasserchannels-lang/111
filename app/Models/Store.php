<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\StoreFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $logo
 * @property string $affiliate_base_url
 * @property array $api_config
 * @property int $currency_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PriceOffer> $priceOffers
 * @property-read Currency $currency
 *
 * @method static StoreFactory factory(...$parameters)
 *
 * @mixin \Eloquent
 */
/**
 * @template TFactory of StoreFactory
 *
 * @mixin TFactory
 */
class Store extends Model
{
    /**
     * @use HasFactory<StoreFactory>
     */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'logo',
        'website_url',
        'country_code',
        'supported_countries',
        'is_active',
        'priority',
        'affiliate_base_url',
        'api_config',
        'currency_id',
    ];

    protected $casts = [
        'api_config' => 'array',
        'supported_countries' => 'array',
    ];

    /**
     * @return HasMany<PriceOffer, Store>
     */
    public function priceOffers(): HasMany
    {
        return $this->hasMany(PriceOffer::class);
    }

    /**
     * @return BelongsTo<Currency, Store>
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function generateAffiliateUrl(string $productUrl): string
    {
        if (empty($this->affiliate_base_url) ||
            empty($this->api_config) ||
            empty($this->api_config['affiliate_code'])) {
            return $productUrl;
        }

        $affiliateCode = (string) $this->api_config['affiliate_code'];
        $affiliateBaseUrl = (string) $this->affiliate_base_url;

        $affiliateUrl = str_replace('{AFFILIATE_CODE}', $affiliateCode, $affiliateBaseUrl);
        $affiliateUrl = str_replace('{URL}', urlencode($productUrl), $affiliateUrl);

        return $affiliateUrl;
    }
}
