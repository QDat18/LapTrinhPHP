<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\VolunteerProfile;
use App\Models\Organization;
use App\Models\Category;
use App\Models\VolunteerOpportunity;
use App\Models\Application;
use App\Models\VolunteerActivity;
use App\Models\Review;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Favorite;
use App\Models\Notification;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create categories first
        $categories = [
            ['category_name' => 'Education', 'description' => 'Teaching and training activities', 'icon' => 'fas fa-graduation-cap', 'color' => '#3B82F6', 'display_order' => 1],
            ['category_name' => 'Healthcare', 'description' => 'Medical and health support', 'icon' => 'fas fa-heartbeat', 'color' => '#EF4444', 'display_order' => 2],
            ['category_name' => 'Environment', 'description' => 'Environmental protection', 'icon' => 'fas fa-leaf', 'color' => '#10B981', 'display_order' => 3],
            ['category_name' => 'Community', 'description' => 'Community development', 'icon' => 'fas fa-users', 'color' => '#8B5CF6', 'display_order' => 4],
            ['category_name' => 'Children', 'description' => 'Child care and support', 'icon' => 'fas fa-child', 'color' => '#F59E0B', 'display_order' => 5],
            ['category_name' => 'Elderly', 'description' => 'Elder care services', 'icon' => 'fas fa-user-friends', 'color' => '#6B7280', 'display_order' => 6],
            ['category_name' => 'Disaster Relief', 'description' => 'Emergency response', 'icon' => 'fas fa-hands-helping', 'color' => '#DC2626', 'display_order' => 7],
            ['category_name' => 'Animals', 'description' => 'Animal welfare', 'icon' => 'fas fa-paw', 'color' => '#059669', 'display_order' => 8],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        // Create admin user
        $admin = User::factory()->admin()->create([
            'email' => 'admin@volunteer.com',
            'first_name' => 'Admin',
            'last_name' => 'User',
        ]);

        // Create 50 volunteers with profiles
        User::factory(50)
            ->volunteer()
            ->verified()
            ->create()
            ->each(function ($user) {
                VolunteerProfile::factory()->create(['user_id' => $user->user_id]);
            });

        // Create 20 organizations
        $organizations = User::factory(20)
            ->organization()
            ->create()
            ->each(function ($user) {
                Organization::factory()->verified()->create(['user_id' => $user->user_id]);
            });

        // Create 100 opportunities
        $opportunities = collect();
        Organization::all()->each(function ($org) use (&$opportunities) {
            $count = fake()->numberBetween(3, 8);
            for ($i = 0; $i < $count; $i++) {
                $opportunity = VolunteerOpportunity::factory()->create([
                    'org_id' => $org->org_id,
                    'category_id' => Category::inRandomOrder()->first()->category_id,  // Đã đúng
                ]);
                $opportunities->push($opportunity);
            }
        });

        // Create applications
        $volunteers = User::volunteers()->get();
        $opportunities->random(50)->each(function ($opportunity) use ($volunteers) {
            $applicantCount = fake()->numberBetween(1, 5);
            $volunteers->random($applicantCount)->each(function ($volunteer) use ($opportunity) {
                try {
                    Application::factory()->create([
                        'opportunity_id' => $opportunity->opportunity_id,
                        'volunteer_id' => $volunteer->user_id,
                    ]);
                } catch (\Exception $e) {
                    // Skip duplicate applications
                }
            });
        });

        // Create volunteer activities
        Application::accepted()->get()->each(function ($application) {
            $activityCount = fake()->numberBetween(1, 5);
            for ($i = 0; $i < $activityCount; $i++) {
                VolunteerActivity::factory()->verified()->create([
                    'volunteer_id' => $application->volunteer_id,
                    'opportunity_id' => $application->opportunity_id,
                    'org_id' => $application->opportunity->org_id,
                    'verified_by' => $application->opportunity->organization->user_id,
                ]);
            }
        });

        // Create reviews
        // Create reviews
        VolunteerActivity::verified()->limit(30)->get()->each(function ($activity) {
            // Organization reviews volunteer
            try {
                Review::factory()->create([
                    'reviewer_id' => $activity->organization->user_id,
                    'reviewee_id' => $activity->volunteer_id,
                    'opportunity_id' => $activity->opportunity_id,
                    'review_type' => 'Organization to Volunteer',
                ]);
            } catch (\Exception $e) {
                // Skip duplicate reviews
            }

            // Volunteer reviews organization
            if (fake()->boolean(70)) {
                try {
                    Review::factory()->create([
                        'reviewer_id' => $activity->volunteer_id,
                        'reviewee_id' => $activity->organization->user_id,
                        'opportunity_id' => $activity->opportunity_id,
                        'review_type' => 'Volunteer to Organization',
                    ]);
                } catch (\Exception $e) {
                    // Skip duplicate reviews
                }
            }
        });

        // Create some conversations and messages
        for ($i = 0; $i < 20; $i++) {
            $conversation = Conversation::factory()->create();
            Message::factory(fake()->numberBetween(5, 20))->create([
                'conversation_id' => $conversation->conversation_id,
            ]);
        }

        // Create favorites
        $volunteers->random(30)->each(function ($volunteer) use ($opportunities) {
            $favCount = fake()->numberBetween(1, 5);
            $opportunities->random($favCount)->each(function ($opportunity) use ($volunteer) {
                try {
                    Favorite::factory()->create([
                        'user_id' => $volunteer->user_id,
                        'opportunity_id' => $opportunity->opportunity_id,
                    ]);
                } catch (\Exception $e) {
                    // Skip duplicates
                }
            });
        });

        // Create notifications for users
        User::inRandomOrder()->limit(30)->get()->each(function ($user) {
            Notification::factory(fake()->numberBetween(3, 10))->create([
                'user_id' => $user->user_id,
            ]);
        });

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin credentials: admin@volunteer.com / password');
    }
}
