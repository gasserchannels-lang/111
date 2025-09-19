<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create test user
        User::updateOrCreate(
            ['email' => 'testuser@example.com'],
            [
                'name' => 'Test User',
                'email' => 'testuser@example.com',
                'password' => Hash::make('TestPass123!'),
                'is_admin' => false,
                'is_active' => true,
                'is_blocked' => false,
            ]
        );

        // Create admin user
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('adminpassword'),
                'is_admin' => true,
                'is_active' => true,
                'is_blocked' => false,
            ]
        );

        $this->command->info('Test users seeded successfully!');
    }
}
