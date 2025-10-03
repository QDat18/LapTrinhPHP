<?php
namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'), // default password
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'phone' => fake()->optional()->numerify('09########'),
            'date_of_birth' => fake()->optional()->dateTimeBetween('-60 years', '-18 years'),
            'gender' => fake()->randomElement(['Male', 'Female', 'Other']),
            'city' => fake()->randomElement(['Hanoi', 'Ho Chi Minh', 'Da Nang', 'Hai Phong', 'Can Tho']),
            'district' => fake()->optional()->city(),
            'address' => fake()->optional()->address(),
            'user_type' => 'Volunteer',
            'avatar_url' => fake()->optional()->imageUrl(200, 200, 'people'),
            'is_verified' => fake()->boolean(70),
            'is_active' => true,
            'last_login_at' => fake()->optional()->dateTimeBetween('-30 days', 'now'),
            'remember_token' => Str::random(10),
        ];
    }

    public function volunteer(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_type' => 'Volunteer',
        ]);
    }

    public function organization(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_type' => 'Organization',
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_type' => 'Admin',
            'is_verified' => true,
        ]);
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => true,
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => false,
        ]);
    }
}

