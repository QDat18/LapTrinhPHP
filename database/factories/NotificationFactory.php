<?php
namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        $type = fake()->randomElement(['Application', 'Message', 'Video Call', 'Review', 'System', 'Opportunity']);
        $relatedType = fake()->randomElement(['application', 'opportunity', 'message', 'call', 'user']);

        return [
            'user_id' => User::factory(),
            'notification_type' => $type,
            'title' => fake()->sentence(5),
            'content' => fake()->optional()->sentence(10),
            'related_id' => fake()->optional()->numberBetween(1, 100),
            'related_type' => fake()->optional()->randomElement(['application', 'opportunity', 'message', 'call', 'user']),
            'action_url' => fake()->optional()->url(),
            'is_read' => fake()->boolean(30),
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
        ];
    }

    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_read' => false,
        ]);
    }

    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'high',
        ]);
    }
}
