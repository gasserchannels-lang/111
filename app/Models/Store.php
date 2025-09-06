<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'api_config' => 'array',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\PriceOffer, \App\Models\Store>
     */
    public function priceOffers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\PriceOffer::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Currency, \App\Models\Store>
     */
    public function currency(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Currency::class);
    }

    public function generateAffiliateUrl(string $productUrl): string
    {
        if (empty($this->affiliate_base_url) ||
            empty($this->api_config) ||
            empty($this->api_config['affiliate_code'])) {
            return $productUrl;
        }

        $affiliateCode = (string) $this->api_config['affiliate_code'];
        $affiliateUrl = str_replace('{AFFILIATE_CODE}', $affiliateCode, $this->affiliate_base_url);
        $affiliateUrl = str_replace('{URL}', urlencode($productUrl), $affiliateUrl);

        return $affiliateUrl;
    }
}
