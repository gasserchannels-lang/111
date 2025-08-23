<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguagesAndCurrenciesSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('languages')->truncate();
        DB::table('currencies')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $languages = [
            ['code' => 'zh', 'name' => 'Chinese', 'native_name' => '中文', 'direction' => 'ltr', 'is_active' => true, 'sort_order' => 1],
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'direction' => 'ltr', 'is_active' => true, 'sort_order' => 2, 'is_default' => true],
            ['code' => 'hi', 'name' => 'Hindi', 'native_name' => 'हिन्दी', 'direction' => 'ltr', 'is_active' => true, 'sort_order' => 3],
            ['code' => 'es', 'name' => 'Spanish', 'native_name' => 'Español', 'direction' => 'ltr', 'is_active' => true, 'sort_order' => 4],
            ['code' => 'ar', 'name' => 'Arabic', 'native_name' => 'العربية', 'direction' => 'rtl', 'is_active' => true, 'sort_order' => 5],
            ['code' => 'bn', 'name' => 'Bengali', 'native_name' => 'বাংলা', 'direction' => 'ltr', 'is_active' => true, 'sort_order' => 6],
            ['code' => 'pt', 'name' => 'Portuguese', 'native_name' => 'Português', 'direction' => 'ltr', 'is_active' => true, 'sort_order' => 7],
            ['code' => 'ru', 'name' => 'Russian', 'native_name' => 'Русский', 'direction' => 'ltr', 'is_active' => true, 'sort_order' => 8],
            ['code' => 'ja', 'name' => 'Japanese', 'native_name' => '日本語', 'direction' => 'ltr', 'is_active' => true, 'sort_order' => 9],
            ['code' => 'pa', 'name' => 'Punjabi', 'native_name' => 'ਪੰਜਾਬੀ', 'direction' => 'ltr', 'is_active' => true, 'sort_order' => 10],
            ['code' => 'de', 'name' => 'German', 'native_name' => 'Deutsch', 'direction' => 'ltr', 'is_active' => true, 'sort_order' => 11],
            ['code' => 'jv', 'name' => 'Javanese', 'native_name' => 'Basa Jawa', 'direction' => 'ltr', 'is_active' => true, 'sort_order' => 12],
            ['code' => 'ko', 'name' => 'Korean', 'native_name' => '한국어', 'direction' => 'ltr', 'is_active' => true, 'sort_order' => 13],
            ['code' => 'fr', 'name' => 'French', 'native_name' => 'Français', 'direction' => 'ltr', 'is_active' => true, 'sort_order' => 14],
            ['code' => 'te', 'name' => 'Telugu', 'native_name' => 'తెలుగు', 'direction' => 'ltr', 'is_active' => true, 'sort_order' => 15],
            ['code' => 'mr', 'name' => 'Marathi', 'native_name' => 'मराठी', 'direction' => 'ltr', 'is_active' => true, 'sort_order' => 16],
            ['code' => 'tr', 'name' => 'Turkish', 'native_name' => 'Türkçe', 'direction' => 'ltr', 'is_active' => true, 'sort_order' => 17],
            ['code' => 'ta', 'name' => 'Tamil', 'native_name' => 'தமிழ்', 'direction' => 'ltr', 'is_active' => true, 'sort_order' => 18],
            ['code' => 'vi', 'name' => 'Vietnamese', 'native_name' => 'Tiếng Việt', 'direction' => 'ltr', 'is_active' => true, 'sort_order' => 19],
            ['code' => 'ur', 'name' => 'Urdu', 'native_name' => 'اردو', 'direction' => 'rtl', 'is_active' => true, 'sort_order' => 20],
        ];
        DB::table('languages')->insert($languages);

        $currencies = [
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'is_active' => true, 'sort_order' => 1, 'is_default' => true],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'is_active' => true, 'sort_order' => 2],
            ['code' => 'JPY', 'name' => 'Japanese Yen', 'symbol' => '¥', 'is_active' => true, 'sort_order' => 3],
            ['code' => 'GBP', 'name' => 'British Pound', 'symbol' => '£', 'is_active' => true, 'sort_order' => 4],
            ['code' => 'AUD', 'name' => 'Australian Dollar', 'symbol' => 'A$', 'is_active' => true, 'sort_order' => 5],
            ['code' => 'CAD', 'name' => 'Canadian Dollar', 'symbol' => 'C$', 'is_active' => true, 'sort_order' => 6],
            ['code' => 'CHF', 'name' => 'Swiss Franc', 'symbol' => 'CHF', 'is_active' => true, 'sort_order' => 7],
            ['code' => 'CNY', 'name' => 'Chinese Yuan', 'symbol' => '¥', 'is_active' => true, 'sort_order' => 8],
            ['code' => 'INR', 'name' => 'Indian Rupee', 'symbol' => '₹', 'is_active' => true, 'sort_order' => 9],
            ['code' => 'BRL', 'name' => 'Brazilian Real', 'symbol' => 'R$', 'is_active' => true, 'sort_order' => 10],
            ['code' => 'RUB', 'name' => 'Russian Ruble', 'symbol' => '₽', 'is_active' => true, 'sort_order' => 11],
            ['code' => 'ZAR', 'name' => 'South African Rand', 'symbol' => 'R', 'is_active' => true, 'sort_order' => 12],
            ['code' => 'MXN', 'name' => 'Mexican Peso', 'symbol' => '$', 'is_active' => true, 'sort_order' => 13],
            ['code' => 'SGD', 'name' => 'Singapore Dollar', 'symbol' => 'S$', 'is_active' => true, 'sort_order' => 14],
            ['code' => 'NZD', 'name' => 'New Zealand Dollar', 'symbol' => 'NZ$', 'is_active' => true, 'sort_order' => 15],
            ['code' => 'SAR', 'name' => 'Saudi Riyal', 'symbol' => 'ر.س', 'is_active' => true, 'sort_order' => 16],
            ['code' => 'AED', 'name' => 'UAE Dirham', 'symbol' => 'د.إ', 'is_active' => true, 'sort_order' => 17],
            ['code' => 'EGP', 'name' => 'Egyptian Pound', 'symbol' => 'ج.م', 'is_active' => true, 'sort_order' => 18],
            ['code' => 'KWD', 'name' => 'Kuwaiti Dinar', 'symbol' => 'د.ك', 'is_active' => true, 'sort_order' => 19],
            ['code' => 'QAR', 'name' => 'Qatari Riyal', 'symbol' => 'ر.ق', 'is_active' => true, 'sort_order' => 20],
        ];
        DB::table('currencies')->insert($currencies);
    }
}
