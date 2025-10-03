<?php
namespace Database\Factories;

use App\Models\SystemAnalytics;
use Illuminate\Database\Eloquent\Factories\Factory;

class SystemAnalyticsFactory extends Factory
{
    protected $model = SystemAnalytics::class;

    public function definition(): array
    {
        $metrics = [
            'active_users',
            'new_registrations',
            'opportunities_posted',
            'applications_submitted',
            'volunteer_hours_logged',
            'organizations_joined',
        ];

        return [
            'metric_name' => fake()->randomElement($metrics),
            'metric_value' => fake()->numberBetween(1, 1000),
            'record_date' => fake()->dateTimeBetween('-90 days', 'now'),
            'category' => fake()->randomElement(['general', 'users', 'opportunities', 'activities']),
            'metadata' => [
                'source' => fake()->randomElement(['web', 'mobile', 'api']),
                'region' => fake()->randomElement(['north', 'south', 'central']),
            ],
        ];
    }
}
