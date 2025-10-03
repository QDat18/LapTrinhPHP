<?php
namespace Database\Factories;

use App\Models\Application;
use App\Models\VolunteerOpportunity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicationFactory extends Factory
{
    protected $model = Application::class;

    public function definition(): array
    {
        $appliedDate = fake()->dateTimeBetween('-30 days', 'now');
        $status = fake()->randomElement(['Pending', 'Under Review', 'Accepted', 'Rejected', 'Withdrawn']);
        $reviewedDate = in_array($status, ['Accepted', 'Rejected']) 
            ? fake()->dateTimeBetween($appliedDate, 'now')
            : null;

        return [
            'opportunity_id' => VolunteerOpportunity::factory(),
            'volunteer_id' => User::factory()->volunteer(),
            'motivation_letter' => fake()->paragraph(4),
            'relevant_experience' => fake()->optional()->paragraph(2),
            'availability_note' => fake()->optional()->sentence(),
            'status' => $status,
            'applied_date' => $appliedDate,
            'reviewed_date' => $reviewedDate,
            'organization_notes' => $reviewedDate ? fake()->optional()->sentence() : null,
            'interview_scheduled' => fake()->optional()->dateTimeBetween('now', '+14 days'),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Pending',
            'reviewed_date' => null,
            'organization_notes' => null,
        ]);
    }

    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Accepted',
            'reviewed_date' => fake()->dateTimeBetween('-7 days', 'now'),
            'organization_notes' => fake()->sentence(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Rejected',
            'reviewed_date' => fake()->dateTimeBetween('-7 days', 'now'),
            'organization_notes' => fake()->sentence(),
        ]);
    }
}
