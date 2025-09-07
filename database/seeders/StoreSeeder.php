<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Store;
use App\Models\Currency;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        Store::truncate();
        
        // Get USD currency ID
        $usdCurrency = Currency::where('code', 'USD')->first();
        if (!$usdCurrency) {
            $this->command->error('USD currency not found. Please run LanguagesAndCurrenciesSeeder first!');
            return;
        }
        
        $stores = [
            [
                'name' => 'Amazon',
                'slug' => 'amazon',
                'logo' => 'https://logo.clearbit.com/amazon.com',
                'website_url' => 'https://amazon.com',
                'country_code' => 'US',
                'affiliate_base_url' => null,
                'supported_countries' => [
                    'US', 'CA', 'UK', 'DE', 'FR', 'IT', 'ES', 'JP', 'AU', 'IN', 'BR', 'MX', 'AE', 'SA', 'EG',
                ],
                'api_config' => ['api_key' => null, 'secret_key' => null, 'associate_tag' => null],
                'is_active' => true,
                'priority' => 10,
                'currency_id' => $usdCurrency->id,
            ],
            [
                'name' => 'eBay',
                'slug' => 'ebay',
                'logo' => 'https://logo.clearbit.com/ebay.com',
                'website_url' => 'https://ebay.com',
                'country_code' => 'US',
                'affiliate_base_url' => null,
                'supported_countries' => ['US', 'CA', 'UK', 'DE', 'FR', 'IT', 'ES', 'AU'],
                'api_config' => ['campaign_id' => null],
                'is_active' => true,
                'priority' => 9,
                'currency_id' => $usdCurrency->id,
            ],
            [
                'name' => 'AliExpress',
                'slug' => 'aliexpress',
                'logo' => 'https://logo.clearbit.com/aliexpress.com',
                'website_url' => 'https://aliexpress.com',
                'country_code' => 'CN',
                'affiliate_base_url' => null,
                'supported_countries' => [
                    'US', 'CA', 'UK', 'DE', 'FR', 'IT', 'ES', 'RU', 'BR', 'AU', 'NL', 'PL', 'AE', 'SA', 'EG',
                ],
                'api_config' => ['app_key' => null, 'app_secret' => null, 'tracking_id' => null],
                'is_active' => true,
                'priority' => 8,
                'currency_id' => $usdCurrency->id,
            ],
        ];

        foreach ($stores as $storeData) {
            Store::create($storeData);
        }
    }
}
