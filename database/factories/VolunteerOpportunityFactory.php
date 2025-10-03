<?php
namespace Database\Factories;

use App\Models\VolunteerOpportunity;
use App\Models\Organization;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class VolunteerOpportunityFactory extends Factory
{
    protected $model = VolunteerOpportunity::class;

    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('+3 days', '+60 days');
        $endDate = fake()->optional()->dateTimeBetween($startDate, '+90 days');
        
        $cities = ['Hanoi', 'Ho Chi Minh City', 'Da Nang', 'Hai Phong', 'Can Tho'];
        $city = fake()->randomElement($cities);

        $skills = ['Teaching', 'Programming', 'Marketing', 'Design', 'Photography', 
                   'Writing', 'Translation', 'First Aid', 'Cooking'];

        return [
            'org_id' => Organization::factory()->verified(),
            'category_id' => Category::factory(),
            'title' => fake()->sentence(6),
            'description' => fake()->paragraph(5),
            'requirements' => fake()->optional()->paragraph(2),
            'benefits' => fake()->optional()->paragraph(2),
            'location' => $city . ', ' . fake()->streetAddress(),
            'latitude' => fake()->latitude(8, 23),
            'longitude' => fake()->longitude(102, 110),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'time_commitment' => fake()->randomElement(['1-2 hours', '3-5 hours', '6-8 hours', 'Full day', 'Multiple days']),
            'schedule_type' => fake()->randomElement(['One-time', 'Weekly', 'Monthly', 'Flexible']),
            'volunteers_needed' => fake()->numberBetween(1, 20),
            'volunteers_registered' => fake()->numberBetween(0, 5),
            'min_age' => fake()->randomElement([16, 18, 21]),
            'required_skills' => fake()->randomElements($skills, fake()->numberBetween(1, 4)),
            'experience_needed' => fake()->randomElement(['No experience', 'Some experience', 'Experienced']),
            'status' => fake()->randomElement(['Active', 'Paused', 'Completed', 'Cancelled']),
            'application_deadline' => fake()->optional()->dateTimeBetween('now', $startDate),
            'view_count' => fake()->numberBetween(0, 500),
            'application_count' => fake()->numberBetween(0, 50),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Active',
        ]);
    }

    public function upcoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Active',
            'start_date' => fake()->dateTimeBetween('+3 days', '+30 days'),
        ]);
    }
}