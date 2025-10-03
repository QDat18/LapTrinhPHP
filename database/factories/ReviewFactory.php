<?php
namespace Database\Factories;

use App\Models\Review;
use App\Models\User;
use App\Models\VolunteerOpportunity;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'reviewer_id' => User::factory(),
            'reviewee_id' => User::factory(),
            'opportunity_id' => fake()->optional()->randomElement(VolunteerOpportunity::pluck('opportunity_id')->toArray()),
            'rating' => fake()->numberBetween(1, 5),
            'review_title' => fake()->optional()->sentence(5),
            'review_text' => fake()->optional()->paragraph(3),
            'review_type' => fake()->randomElement(['Volunteer to Organization', 'Organization to Volunteer']),
            'is_approved' => fake()->boolean(80),
            'helpful_count' => fake()->numberBetween(0, 50),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_approved' => true,
        ]);
    }

    public function positive(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => fake()->numberBetween(4, 5),
        ]);
    }
}