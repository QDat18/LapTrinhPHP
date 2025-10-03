<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemAnalytics;
use Carbon\Carbon;

class AnalyticsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Seed historical analytics data (last 90 days)
        for ($i = 90; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            
            // User metrics
            SystemAnalytics::create([
                'metric_name' => 'daily_active_users',
                'metric_value' => rand(50, 200),
                'record_date' => $date,
                'category' => 'users',
            ]);

            SystemAnalytics::create([
                'metric_name' => 'new_registrations',
                'metric_value' => rand(5, 25),
                'record_date' => $date,
                'category' => 'users',
            ]);

            // Opportunity metrics
            SystemAnalytics::create([
                'metric_name' => 'opportunities_posted',
                'metric_value' => rand(2, 15),
                'record_date' => $date,
                'category' => 'opportunities',
            ]);

            // Application metrics
            SystemAnalytics::create([
                'metric_name' => 'applications_submitted',
                'metric_value' => rand(10, 50),
                'record_date' => $date,
                'category' => 'applications',
            ]);

            // Activity metrics
            SystemAnalytics::create([
                'metric_name' => 'volunteer_hours_logged',
                'metric_value' => rand(20, 150),
                'record_date' => $date,
                'category' => 'activities',
            ]);
        }

        $this->command->info('Analytics data seeded for last 90 days');
    }
}