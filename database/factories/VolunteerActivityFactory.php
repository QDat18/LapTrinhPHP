<?php
namespace Database\Factories;

use App\Models\VolunteerActivity;
use App\Models\User;
use App\Models\VolunteerOpportunity;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class VolunteerActivityFactory extends Factory
{
    protected $model = VolunteerActivity::class;

    public function definition(): array
    {
        $status = fake()->randomElement(['Pending', 'Verified', 'Disputed']);
        $activityDate = fake()->dateTimeBetween('-60 days', '-1 day');
        $verifiedDate = $status === 'Verified' 
            ? fake()->dateTimeBetween($activityDate, 'now')
            : null;

        return [
            'volunteer_id' => User::factory()->volunteer(),
            'opportunity_id' => VolunteerOpportunity::factory(),
            'org_id' => Organization::factory(),
            'activity_date' => $activityDate,
            'hours_worked' => fake()->randomFloat(2, 1, 12),
            'activity_description' => fake()->optional()->paragraph(),
            'status' => $status,
            'verified_by' => $status === 'Verified' ? User::factory() : null,
            'verified_date' => $verifiedDate,
            'impact_notes' => fake()->optional()->sentence(),
        ];
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Verified',
            'verified_by' => User::factory(),
            'verified_date' => fake()->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Pending',
            'verified_by' => null,
            'verified_date' => null,
        ]);
    }
}

