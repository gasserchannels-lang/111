<?php
namespace Database\Factories;
use App\Models\UserLocaleSetting;
use Illuminate\Database\Eloquent\Factories\Factory;
class UserLocaleSettingFactory extends Factory
{
    protected $model = UserLocaleSetting::class;
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'language_id' => \App\Models\Language::factory(),
            'currency_id' => \App\Models\Currency::factory(),
        ];
    }
}
