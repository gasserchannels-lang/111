<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function priceOffers(): HasMany
    {
        return $this->hasMany(PriceOffer::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function generateAffiliateUrl(string $productUrl): string
    {
        if (empty($this->affiliate_base_url) || empty($this->api_config['affiliate_code'])) {
            return $productUrl;
        }

        $affiliateUrl = str_replace('{AFFILIATE_CODE}', $this->api_config['affiliate_code'], $this->affiliate_base_url);
        $affiliateUrl = str_replace('{URL}', urlencode($productUrl), $affiliateUrl);

        return $affiliateUrl;
    }
}
