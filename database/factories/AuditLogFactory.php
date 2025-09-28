<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AuditLog>
 */
class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event' => $this->faker->randomElement(['created', 'updated', 'deleted', 'viewed']),
            'auditable_type' => Product::class,
            'auditable_id' => 1,
            'user_id' => 1,
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'old_values' => $this->faker->optional()->randomElements([
                'name' => $this->faker->sentence(),
                'price' => $this->faker->randomFloat(2, 10, 1000),
                'description' => $this->faker->paragraph(),
            ]),
            'new_values' => $this->faker->optional()->randomElements([
                'name' => $this->faker->sentence(),
                'price' => $this->faker->randomFloat(2, 10, 1000),
                'description' => $this->faker->paragraph(),
            ]),
            'metadata' => $this->faker->optional()->randomElements([
                'source' => $this->faker->randomElement(['web', 'api', 'admin']),
                'browser' => $this->faker->randomElement(['Chrome', 'Firefox', 'Safari']),
                'device' => $this->faker->randomElement(['desktop', 'mobile', 'tablet']),
            ]),
            'url' => $this->faker->optional()->url(),
            'method' => $this->faker->randomElement(['GET', 'POST', 'PUT', 'DELETE', 'PATCH']),
        ];
    }

    /**
     * Indicate that the audit log is for a product.
     */
    public function forProduct(): static
    {
        return $this->state(fn (array $attributes) => [
            'auditable_type' => Product::class,
            'auditable_id' => Product::factory(),
        ]);
    }

    /**
     * Indicate that the audit log is for a user.
     */
    public function forUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'auditable_type' => User::class,
            'auditable_id' => User::factory(),
        ]);
    }

    /**
     * Indicate that the audit log has no user.
     */
    public function withoutUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
        ]);
    }

    /**
     * Indicate that the audit log has no old values.
     */
    public function withoutOldValues(): static
    {
        return $this->state(fn (array $attributes) => [
            'old_values' => null,
        ]);
    }

    /**
     * Indicate that the audit log has no new values.
     */
    public function withoutNewValues(): static
    {
        return $this->state(fn (array $attributes) => [
            'new_values' => null,
        ]);
    }

    /**
     * Indicate that the audit log has no metadata.
     */
    public function withoutMetadata(): static
    {
        return $this->state(fn (array $attributes) => [
            'metadata' => null,
        ]);
    }
}
