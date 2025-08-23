<?php

namespace Tests\Unit;

use App\Models\Store;
use Tests\TestCase;

class StoreModelTest extends TestCase
{
    public function test_generate_affiliate_url_returns_original_url_when_no_config()
    {
        $store = new Store([
            'name' => 'Test Store',
            'affiliate_base_url' => null,
            'api_config' => null,
        ]);
        $productUrl = 'http://example.com/product/123';
        $this->assertEquals($productUrl, $store->generateAffiliateUrl($productUrl ));
    }

    public function test_generate_affiliate_url_appends_affiliate_code()
    {
        $store = new Store([
            'name' => 'Test Store',
            'affiliate_base_url' => 'http://aff.example.com?ref={AFFILIATE_CODE}&url={URL}',
            'api_config' => ['affiliate_code' => 'MY-CODE'],
        ] );
        $productUrl = 'http://example.com/product/123';
        $expectedUrl = 'http://aff.example.com?ref=MY-CODE&url='.urlencode($productUrl );
        $this->assertEquals($expectedUrl, $store->generateAffiliateUrl($productUrl));
    }
}
