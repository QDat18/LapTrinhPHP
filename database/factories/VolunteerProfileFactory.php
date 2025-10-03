<?php
namespace Database\Factories;

use App\Models\VolunteerProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class VolunteerProfileFactory extends Factory
{
    protected $model = VolunteerProfile::class;

    public function definition(): array
    {
        $skills = ['Teaching', 'Programming', 'Marketing', 'Design', 'Photography', 
                   'Writing', 'Translation', 'First Aid', 'Cooking', 'Music', 
                   'Sports', 'Gardening', 'Counseling', 'Data Entry'];

        return [
            'user_id' => User::factory()->volunteer(),
            'occupation' => fake()->optional()->jobTitle(),
            'education_level' => fake()->randomElement(['High School', 'Diploma', 'Bachelor', 'Master', 'PhD']),
            'university' => fake()->optional()->company() . ' University',
            'bio' => fake()->optional()->paragraph(3),
            'skills' => fake()->randomElements($skills, fake()->numberBetween(2, 6)),
            'interests' => fake()->optional()->sentence(),
            'availability' => fake()->randomElement(['Weekdays', 'Weekends', 'Flexible', 'Full-time']),
            'volunteer_experience' => fake()->optional()->paragraph(2),
            'total_volunteer_hours' => fake()->numberBetween(0, 500),
            'volunteer_rating' => fake()->randomFloat(2, 0, 5),
            'preferred_location' => fake()->randomElement(['Hanoi', 'Ho Chi Minh', 'Da Nang', 'Any']),
            'transportation' => fake()->randomElement(['Motorbike', 'Car', 'Public Transport', 'Walking']),
        ];
    }

    public function experienced(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_volunteer_hours' => fake()->numberBetween(100, 1000),
            'volunteer_rating' => fake()->randomFloat(2, 4, 5),
        ]);
    }

    public function beginner(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_volunteer_hours' => fake()->numberBetween(0, 50),
            'volunteer_rating' => fake()->randomFloat(2, 0, 3),
        ]);
    }
}
