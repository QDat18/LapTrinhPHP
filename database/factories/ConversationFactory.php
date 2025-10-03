<?php
namespace Database\Factories;

use App\Models\Conversation;
use App\Models\User;
use App\Models\VolunteerOpportunity;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConversationFactory extends Factory
{
    protected $model = Conversation::class;

    public function definition(): array
    {
        $type = fake()->randomElement(['direct', 'group', 'opportunity_chat']);

        return [
            'conversation_type' => $type,
            'title' => $type === 'group' ? fake()->sentence(3) : null,
            'opportunity_id' => $type === 'opportunity_chat' ? VolunteerOpportunity::factory() : null,
            'created_by' => User::factory(),
            'last_message_at' => fake()->dateTimeBetween('-7 days', 'now'),
            'is_active' => true,
        ];
    }

    public function direct(): static
    {
        return $this->state(fn (array $attributes) => [
            'conversation_type' => 'direct',
            'title' => null,
            'opportunity_id' => null,
        ]);
    }

    public function group(): static
    {
        return $this->state(fn (array $attributes) => [
            'conversation_type' => 'group',
            'title' => fake()->sentence(3),
            'opportunity_id' => null,
        ]);
    }
}
