<?php

namespace Database\Factories;

use App\Models\Currency;
use App\Models\Language;
use App\Models\User;
use App\Models\UserLocaleSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserLocaleSettingFactory extends Factory
{
    protected $model = UserLocaleSetting::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'language_id' => Language::factory(),
            'currency_id' => Currency::factory(),
        ];
    }
}
