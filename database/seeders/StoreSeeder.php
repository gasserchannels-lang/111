<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stores = [
            [
                'name' => 'Amazon',
                'slug' => 'amazon',
                'logo' => 'https://logo.clearbit.com/amazon.com',
                'website_url' => 'https://amazon.com',
                'affiliate_base_url' => null, // سيتم إضافته لاحقاً
                'supported_countries' => ['US', 'CA', 'UK', 'DE', 'FR', 'IT', 'ES', 'JP', 'AU', 'IN', 'BR', 'MX', 'AE', 'SA', 'EG'],
                'api_config' => [
                    'api_key' => null,
                    'secret_key' => null,
                    'associate_tag' => null,
                ],
                'is_active' => true,
                'priority' => 10,
            ],
            [
                'name' => 'eBay',
                'slug' => 'ebay',
                'logo' => 'https://logo.clearbit.com/ebay.com',
                'website_url' => 'https://ebay.com',
                'affiliate_base_url' => null,
                'supported_countries' => ['US', 'CA', 'UK', 'DE', 'FR', 'IT', 'ES', 'AU', 'AT', 'BE', 'CH', 'IE', 'NL'],
                'api_config' => [
                    'app_id' => null,
                    'cert_id' => null,
                    'dev_id' => null,
                ],
                'is_active' => true,
                'priority' => 9,
            ],
            [
                'name' => 'AliExpress',
                'slug' => 'aliexpress',
                'logo' => 'https://logo.clearbit.com/aliexpress.com',
                'website_url' => 'https://aliexpress.com',
                'affiliate_base_url' => null,
                'supported_countries' => ['US', 'CA', 'UK', 'DE', 'FR', 'IT', 'ES', 'RU', 'BR', 'AU', 'NL', 'PL', 'AE', 'SA', 'EG'],
                'api_config' => [
                    'app_key' => null,
                    'app_secret' => null,
                    'tracking_id' => null,
                ],
                'is_active' => true,
                'priority' => 8,
            ],
            [
                'name' => 'Walmart',
                'slug' => 'walmart',
                'logo' => 'https://logo.clearbit.com/walmart.com',
                'website_url' => 'https://walmart.com',
                'affiliate_base_url' => null,
                'supported_countries' => ['US', 'CA', 'MX'],
                'api_config' => [
                    'consumer_id' => null,
                    'private_key' => null,
                ],
                'is_active' => true,
                'priority' => 7,
            ],
            [
                'name' => 'Best Buy',
                'slug' => 'bestbuy',
                'logo' => 'https://logo.clearbit.com/bestbuy.com',
                'website_url' => 'https://bestbuy.com',
                'affiliate_base_url' => null,
                'supported_countries' => ['US', 'CA'],
                'api_config' => [
                    'api_key' => null,
                ],
                'is_active' => true,
                'priority' => 6,
            ],
            [
                'name' => 'Newegg',
                'slug' => 'newegg',
                'logo' => 'https://logo.clearbit.com/newegg.com',
                'website_url' => 'https://newegg.com',
                'affiliate_base_url' => null,
                'supported_countries' => ['US', 'CA'],
                'api_config' => [
                    'authorization' => null,
                    'secretkey' => null,
                ],
                'is_active' => true,
                'priority' => 5,
            ],
            [
                'name' => 'Target',
                'slug' => 'target',
                'logo' => 'https://logo.clearbit.com/target.com',
                'website_url' => 'https://target.com',
                'affiliate_base_url' => null,
                'supported_countries' => ['US'],
                'api_config' => [
                    'api_key' => null,
                ],
                'is_active' => true,
                'priority' => 4,
            ],
            [
                'name' => 'Noon',
                'slug' => 'noon',
                'logo' => 'https://logo.clearbit.com/noon.com',
                'website_url' => 'https://noon.com',
                'affiliate_base_url' => null,
                'supported_countries' => ['AE', 'SA', 'EG'],
                'api_config' => [
                    'api_key' => null,
                    'secret_key' => null,
                ],
                'is_active' => true,
                'priority' => 3,
            ],
            [
                'name' => 'Jumia',
                'slug' => 'jumia',
                'logo' => 'https://logo.clearbit.com/jumia.com',
                'website_url' => 'https://jumia.com',
                'affiliate_base_url' => null,
                'supported_countries' => ['EG', 'NG', 'KE', 'UG', 'GH', 'CI', 'SN', 'TN', 'MA', 'DZ'],
                'api_config' => [
                    'api_key' => null,
                    'secret_key' => null,
                ],
                'is_active' => true,
                'priority' => 2,
            ],
            [
                'name' => 'B&H Photo',
                'slug' => 'bhphotovideo',
                'logo' => 'https://logo.clearbit.com/bhphotovideo.com',
                'website_url' => 'https://bhphotovideo.com',
                'affiliate_base_url' => null,
                'supported_countries' => ['US', 'CA', 'UK', 'DE', 'FR', 'IT', 'ES', 'AU', 'JP'],
                'api_config' => [
                    'api_key' => null,
                ],
                'is_active' => true,
                'priority' => 1,
            ],
        ];

        foreach ($stores as $store) {
            \App\Models\Store::create($store);
        }

        $this->command->info('Stores seeded successfully!');
    }
}
