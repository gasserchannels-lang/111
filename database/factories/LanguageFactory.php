<?php

namespace Database\Factories;

use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

class LanguageFactory extends Factory
{
    protected $model = Language::class;

    public function definition(): array
    {
        // A basic, default definition
        return [
            'name' => 'English',
            'code' => 'en',
            'native_name' => 'English',
            'is_default' => true,
        ];
    }
}
