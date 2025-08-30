<?php

namespace Database\Factories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class StoreFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->company;

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'website_url' => $this->faker->url,
            'country_code' => $this->faker->randomElement(['EG', 'US', 'UK', 'DE', 'FR']),
            'is_active' => $this->faker->boolean(80),
            'priority' => $this->faker->numberBetween(0, 100),
            'affiliate_base_url' => $this->faker->optional()->url,

            // ✅ *** هذا هو السطر الذي تم إصلاحه ***
            // تم استبدال ->json() الخاطئة بـ json_encode لإنشاء نص JSON صحيح
            'api_config' => $this->faker->optional()->passthrough(json_encode(['key' => $this->faker->uuid, 'secret' => $this->faker->sha256])),

            'currency_id' => Currency::factory(),
        ];
    }
}
