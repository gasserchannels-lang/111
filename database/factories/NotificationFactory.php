<?php

namespace Database\Factories;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Notification>
     */
    protected $model = Notification::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => 1,
            'type' => $this->faker->randomElement(['info', 'warning', 'error', 'success']),
            'title' => $this->faker->sentence(3),
            'message' => $this->faker->paragraph(2),
            'data' => [
                'key' => $this->faker->word,
                'value' => $this->faker->sentence,
            ],
            'read_at' => null,
            'sent_at' => null,
            'priority' => $this->faker->numberBetween(1, 5),
            'channel' => $this->faker->randomElement(['email', 'sms', 'push', 'database']),
            'status' => $this->faker->randomElement(['pending', 'sent', 'failed']),
            'metadata' => [
                'source' => $this->faker->word,
                'timestamp' => $this->faker->unixTime,
            ],
            'tags' => $this->faker->words(3),
        ];
    }

    /**
     * Indicate that the notification is read.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Indicate that the notification is sent.
     */
    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'sent_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'status' => 'sent',
        ]);
    }

    /**
     * Indicate that the notification is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'sent_at' => null,
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate that the notification is failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
        ]);
    }

    /**
     * Indicate that the notification has high priority.
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 5,
        ]);
    }

    /**
     * Indicate that the notification has low priority.
     */
    public function lowPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 1,
        ]);
    }
}
