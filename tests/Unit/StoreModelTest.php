public function test_generate_affiliate_url_appends_affiliate_code()
{
    $store = new Store([
        'name' => 'Test Store',
        // تم تصحيح الرابط هنا ليكون مثالاً صحيحاً
        'affiliate_base_url' => 'http://aff.example.com?ref={AFFILIATE_CODE}&product_url={URL}',
        'api_config' => ['affiliate_code' => 'MY-CODE-123'],
    ] );

    $productUrl = 'http://original-site.com/product/abc';

    // هذا هو الرابط المتوقع الصحيح بعد التعويض
    $expectedUrl = 'http://aff.example.com?ref=MY-CODE-123&product_url='.urlencode($productUrl );

    $this->assertEquals($expectedUrl, $store->generateAffiliateUrl($productUrl));
}
