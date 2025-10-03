<?php
namespace Database\Factories;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    public function definition(): array
    {
        // Tạo tên động từ các thành phần
        $prefixes = ['Green', 'Hope', 'Community', 'Education', 'Healthcare', 'Environmental', 'Youth', 'Senior', 'Animal', 'Disaster', 'Future', 'Global', 'Local', 'United', 'National'];
        
        $cores = ['Earth', 'Children', 'Care', 'Development', 'Heroes', 'Warriors', 'Society', 'Citizens', 'Welfare', 'Relief', 'Change', 'Impact', 'Action', 'Support', 'Alliance'];
        
        $suffixes = ['Foundation', 'Center', 'Organization', 'Association', 'Network', 'Group', 'Institute', 'Society', 'Trust', 'Initiative'];

        $orgName = fake()->randomElement($prefixes) . ' ' . 
                   fake()->randomElement($cores) . ' ' . 
                   fake()->randomElement($suffixes) . ' - ' . 
                   fake()->city();

        return [
            'user_id' => User::factory()->organization(),
            'organization_name' => $orgName,
            'organization_type' => fake()->randomElement(['NGO', 'NPO', 'Charity', 'School', 'Hospital', 'Community Group']),
            'description' => fake()->paragraph(4),
            'mission_statement' => fake()->sentence(12),
            'website' => fake()->optional()->url(),
            'contact_person' => fake()->name(),
            'registration_number' => fake()->bothify('ORG-####-????'),
            'verification_status' => fake()->randomElement(['Pending', 'Verified', 'Rejected']),
            'founded_year' => fake()->numberBetween(1990, 2023),
            'volunteer_count' => fake()->numberBetween(0, 100),
            'rating' => fake()->randomFloat(2, 0, 5),
            'total_opportunities' => fake()->numberBetween(0, 50),
        ];
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_status' => 'Verified',
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_status' => 'Pending',
        ]);
    }
}