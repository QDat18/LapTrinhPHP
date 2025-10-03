<?php
namespace Database\Factories;

use App\Models\Message;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition(): array
    {
        $type = fake()->randomElement(['text', 'image', 'file', 'video']);

        return [
            'conversation_id' => Conversation::factory(),
            'sender_id' => User::factory(),
            'message_type' => $type,
            'content' => $type === 'text' ? fake()->paragraph() : null,
            'attachment_url' => $type !== 'text' ? fake()->imageUrl() : null,
            'attachment_name' => $type !== 'text' ? fake()->word() . '.' . fake()->fileExtension() : null,
            'is_deleted' => fake()->boolean(5),
            'sent_at' => fake()->dateTimeBetween('-7 days', 'now'),
        ];
    }
}
