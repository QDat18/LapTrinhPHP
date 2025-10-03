<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'category_name' => fake()->unique()->word(),
            'description' => fake()->sentence(),
            'icon' => 'fas fa-' . fake()->word(),
            'color' => fake()->hexColor(),
            'is_active' => true,
            'display_order' => fake()->numberBetween(1, 100),
        ];
    }
}