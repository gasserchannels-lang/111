<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguagesAndCurrenciesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data to prevent duplicates on re-run
        DB::table('user_locale_settings')->truncate();
        DB::table('language_currency')->truncate();
        DB::table('languages')->truncate();
        DB::table('currencies')->truncate();

        // Add Top 20 Languages by number of speakers
        $languages = [
            ['code' => 'zh', 'name' => 'Chinese', 'native_name' => '中文', 'direction' => 'ltr', 'is_active' => true, 'sort_order' => 1],
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'direction' => 'ltr', 'is_active' => true, 'sort_order' => 2],
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

        // Add Major World Currencies
        $currencies = [
            // Major Global Currencies
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'exchange_rate' => 1.0000, 'is_active' => true, 'sort_order' => 1],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'exchange_rate' => 0.9200, 'is_active' => true, 'sort_order' => 2],
            ['code' => 'CNY', 'name' => 'Chinese Yuan', 'symbol' => '¥', 'exchange_rate' => 7.2500, 'is_active' => true, 'sort_order' => 3],
            ['code' => 'JPY', 'name' => 'Japanese Yen', 'symbol' => '¥', 'exchange_rate' => 149.5000, 'is_active' => true, 'sort_order' => 4],
            ['code' => 'GBP', 'name' => 'British Pound', 'symbol' => '£', 'exchange_rate' => 0.7900, 'is_active' => true, 'sort_order' => 5],

            // Regional Currencies
            ['code' => 'INR', 'name' => 'Indian Rupee', 'symbol' => '₹', 'exchange_rate' => 83.2500, 'is_active' => true, 'sort_order' => 6],
            ['code' => 'BRL', 'name' => 'Brazilian Real', 'symbol' => 'R$', 'exchange_rate' => 5.1500, 'is_active' => true, 'sort_order' => 7],
            ['code' => 'RUB', 'name' => 'Russian Ruble', 'symbol' => '₽', 'exchange_rate' => 92.5000, 'is_active' => true, 'sort_order' => 8],
            ['code' => 'KRW', 'name' => 'South Korean Won', 'symbol' => '₩', 'exchange_rate' => 1325.0000, 'is_active' => true, 'sort_order' => 9],
            ['code' => 'CAD', 'name' => 'Canadian Dollar', 'symbol' => 'C$', 'exchange_rate' => 1.3600, 'is_active' => true, 'sort_order' => 10],

            // Middle East & Africa
            ['code' => 'SAR', 'name' => 'Saudi Riyal', 'symbol' => 'ر.س', 'exchange_rate' => 3.7500, 'is_active' => true, 'sort_order' => 11],
            ['code' => 'AED', 'name' => 'UAE Dirham', 'symbol' => 'د.إ', 'exchange_rate' => 3.6700, 'is_active' => true, 'sort_order' => 12],
            ['code' => 'EGP', 'name' => 'Egyptian Pound', 'symbol' => 'E£', 'exchange_rate' => 30.9000, 'is_active' => true, 'sort_order' => 13],
            ['code' => 'ZAR', 'name' => 'South African Rand', 'symbol' => 'R', 'exchange_rate' => 18.7500, 'is_active' => true, 'sort_order' => 14],

            // Asia Pacific
            ['code' => 'AUD', 'name' => 'Australian Dollar', 'symbol' => 'A$', 'exchange_rate' => 1.5200, 'is_active' => true, 'sort_order' => 15],
            ['code' => 'SGD', 'name' => 'Singapore Dollar', 'symbol' => 'S$', 'exchange_rate' => 1.3500, 'is_active' => true, 'sort_order' => 16],
            ['code' => 'THB', 'name' => 'Thai Baht', 'symbol' => '฿', 'exchange_rate' => 35.8000, 'is_active' => true, 'sort_order' => 17],
            ['code' => 'MYR', 'name' => 'Malaysian Ringgit', 'symbol' => 'RM', 'exchange_rate' => 4.6500, 'is_active' => true, 'sort_order' => 18],

            // Europe
            ['code' => 'CHF', 'name' => 'Swiss Franc', 'symbol' => 'CHF', 'exchange_rate' => 0.8800, 'is_active' => true, 'sort_order' => 19],
            ['code' => 'SEK', 'name' => 'Swedish Krona', 'symbol' => 'kr', 'exchange_rate' => 10.8500, 'is_active' => true, 'sort_order' => 20],
            ['code' => 'NOK', 'name' => 'Norwegian Krone', 'symbol' => 'kr', 'exchange_rate' => 10.9500, 'is_active' => true, 'sort_order' => 21],
            ['code' => 'DKK', 'name' => 'Danish Krone', 'symbol' => 'kr', 'exchange_rate' => 6.8500, 'is_active' => true, 'sort_order' => 22],

            // Americas
            ['code' => 'MXN', 'name' => 'Mexican Peso', 'symbol' => '$', 'exchange_rate' => 17.2500, 'is_active' => true, 'sort_order' => 23],
            ['code' => 'CLP', 'name' => 'Chilean Peso', 'symbol' => '$', 'exchange_rate' => 895.0000, 'is_active' => true, 'sort_order' => 24],
            ['code' => 'ARS', 'name' => 'Argentine Peso', 'symbol' => '$', 'exchange_rate' => 365.0000, 'is_active' => true, 'sort_order' => 25],
        ];
        DB::table('currencies')->insert($currencies);

        // Language-Currency Relationships
        $languageIds = DB::table('languages')->pluck('id', 'code');
        $currencyIds = DB::table('currencies')->pluck('id', 'code');

        $languageCurrencyMappings = [
            // Chinese - Asian currencies
            'zh' => ['CNY', 'USD', 'SGD'],
            // English - Global currencies (most comprehensive)
            'en' => ['USD', 'GBP', 'EUR', 'CAD', 'AUD', 'SGD', 'ZAR'],
            // Hindi - Indian subcontinent
            'hi' => ['INR', 'USD', 'EUR'],
            // Spanish - Latin America + Spain
            'es' => ['EUR', 'USD', 'MXN', 'ARS', 'CLP'],
            // Arabic - Middle East & North Africa
            'ar' => ['SAR', 'AED', 'EGP', 'USD', 'EUR'],
            // Bengali - Bangladesh & India
            'bn' => ['INR', 'USD'],
            // Portuguese - Brazil & Portugal
            'pt' => ['BRL', 'EUR', 'USD'],
            // Russian - Russia & CIS
            'ru' => ['RUB', 'USD', 'EUR'],
            // Japanese
            'ja' => ['JPY', 'USD'],
            // Punjabi - India & Pakistan
            'pa' => ['INR', 'USD'],
            // German - Germany & Austria
            'de' => ['EUR', 'CHF', 'USD'],
            // Javanese - Indonesia
            'jv' => ['USD'],
            // Korean
            'ko' => ['KRW', 'USD'],
            // French - France & Francophone countries
            'fr' => ['EUR', 'USD', 'CAD', 'CHF'],
            // Telugu - India
            'te' => ['INR', 'USD'],
            // Marathi - India
            'mr' => ['INR', 'USD'],
            // Turkish
            'tr' => ['USD', 'EUR'],
            // Tamil - India & Sri Lanka
            'ta' => ['INR', 'USD'],
            // Vietnamese
            'vi' => ['USD'],
            // Urdu - Pakistan & India
            'ur' => ['INR', 'USD'],
        ];

        foreach ($languageCurrencyMappings as $langCode => $currencyCodes) {
            $langId = $languageIds[$langCode];
            foreach ($currencyCodes as $index => $currencyCode) {
                if (isset($currencyIds[$currencyCode])) {
                    DB::table('language_currency')->insert([
                        'language_id' => $langId,
                        'currency_id' => $currencyIds[$currencyCode],
                        'is_default' => $index === 0, // First currency is default
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // For languages without specific mappings, add USD and EUR as fallback
        $mappedLanguages = array_keys($languageCurrencyMappings);
        $unmappedLanguages = array_diff(array_keys($languageIds->toArray()), $mappedLanguages);

        foreach ($unmappedLanguages as $langCode) {
            $langId = $languageIds[$langCode];
            DB::table('language_currency')->insert([
                ['language_id' => $langId, 'currency_id' => $currencyIds['USD'], 'is_default' => true, 'created_at' => now(), 'updated_at' => now()],
                ['language_id' => $langId, 'currency_id' => $currencyIds['EUR'], 'is_default' => false, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }
}
