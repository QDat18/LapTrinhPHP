<?php
namespace Database\Factories;

use App\Models\VideoCall;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class VideoCallFactory extends Factory
{
    protected $model = VideoCall::class;

    public function definition(): array
    {
        $status = fake()->randomElement(['initiated', 'ringing', 'active', 'ended', 'missed', 'declined']);
        $startedAt = in_array($status, ['active', 'ended']) ? fake()->dateTimeBetween('-7 days', 'now') : null;
        $endedAt = $status === 'ended' ? fake()->dateTimeBetween($startedAt, 'now') : null;
        $duration = $endedAt ? fake()->numberBetween(60, 3600) : 0;

        return [
            'conversation_id' => Conversation::factory(),
            'initiated_by' => User::factory(),
            'call_type' => fake()->randomElement(['audio', 'video']),
            'call_status' => $status,
            'room_id' => fake()->uuid(),
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
            'duration' => $duration,
        ];
    }

    public function ended(): static
    {
        $startedAt = fake()->dateTimeBetween('-7 days', 'now');
        $endedAt = fake()->dateTimeBetween($startedAt, 'now');

        return $this->state(fn (array $attributes) => [
            'call_status' => 'ended',
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
            'duration' => fake()->numberBetween(60, 3600),
        ]);
    }
}