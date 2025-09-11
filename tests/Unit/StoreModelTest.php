<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Store;
use Tests\TestCase;

class StoreModelTest extends TestCase
{
    public function test_generate_affiliate_url_returns_original_url_when_no_config(): void
    {
        $store = new Store(['affiliate_base_url' => null, 'api_config' => null]);
        $productUrl = 'http://example.com/product/123';
        $this->assertEquals($productUrl, $store->generateAffiliateUrl($productUrl));
    }

    public function test_generate_affiliate_url_appends_affiliate_code(): void
    {
        $store = new Store([
            'affiliate_base_url' => 'http://aff.example.com?ref={AFFILIATE_CODE}&product_url={URL}',
            'affiliate_code' => 'MY-CODE-123',
        ]);

        $productUrl = 'http://original-site.com/product/abc';
        $expectedUrl = 'http://aff.example.com?ref=MY-CODE-123&product_url='.urlencode($productUrl);

        $this->assertEquals($expectedUrl, $store->generateAffiliateUrl($productUrl));
    }
}
