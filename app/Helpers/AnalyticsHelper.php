<?php
namespace App\Helpers;

use App\Models\SystemAnalytics;
use App\Models\User;
use App\Models\VolunteerOpportunity;
use App\Models\Application;
use App\Models\VolunteerActivity;

class AnalyticsHelper
{
    /**
     * Record daily platform metrics
     */
    public static function recordDailyMetrics()
    {
        $today = now()->toDateString();

        // User metrics
        SystemAnalytics::recordMetric('total_users', User::count(), 'users');
        SystemAnalytics::recordMetric('active_volunteers', 
            User::where('user_type', 'Volunteer')->where('is_active', true)->count(), 
            'users');
        SystemAnalytics::recordMetric('verified_organizations', 
            User::where('user_type', 'Organization')->where('is_verified', true)->count(), 
            'users');

        // Opportunity metrics
        SystemAnalytics::recordMetric('active_opportunities', 
            VolunteerOpportunity::where('status', 'Active')->count(), 
            'opportunities');
        SystemAnalytics::recordMetric('new_opportunities_today', 
            VolunteerOpportunity::whereDate('created_at', $today)->count(), 
            'opportunities');

        // Application metrics
        SystemAnalytics::recordMetric('total_applications', 
            Application::count(), 
            'applications');
        SystemAnalytics::recordMetric('new_applications_today', 
            Application::whereDate('created_at', $today)->count(), 
            'applications');

        // Activity metrics
        SystemAnalytics::recordMetric('total_volunteer_hours', 
            VolunteerActivity::where('status', 'Verified')->sum('hours_worked'), 
            'activities');
        SystemAnalytics::recordMetric('volunteer_hours_today', 
            VolunteerActivity::where('status', 'Verified')
                ->whereDate('activity_date', $today)
                ->sum('hours_worked'), 
            'activities');
    }

    /**
     * Get growth rate for a metric
     */
    public static function getGrowthRate($metricName, $days = 30, $category = 'general')
    {
        $trend = SystemAnalytics::getMetricTrend($metricName, $days, $category);
        
        if (count($trend) < 2) {
            return 0;
        }

        $values = array_values($trend);
        $firstValue = $values[0];
        $lastValue = end($values);

        if ($firstValue == 0) {
            return $lastValue > 0 ? 100 : 0;
        }

        return round((($lastValue - $firstValue) / $firstValue) * 100, 2);
    }

    /**
     * Get engagement score for a user
     */
    public static function calculateUserEngagementScore($userId)
    {
        $user = User::find($userId);
        if (!$user) return 0;

        $score = 0;

        // Profile completeness (30 points)
        if ($user->user_type == 'Volunteer') {
            $profile = $user->volunteerProfile;
            if ($profile) {
                $score += $profile->bio ? 5 : 0;
                $score += $profile->skills ? 10 : 0;
                $score += $profile->interests ? 5 : 0;
                $score += $profile->volunteer_experience ? 5 : 0;
                $score += $user->avatar_url ? 5 : 0;
            }
        }

        // Activity level (40 points)
        if ($user->user_type == 'Volunteer') {
            $applicationCount = Application::where('volunteer_id', $userId)->count();
            $score += min($applicationCount * 5, 20); // Max 20 points

            $activityCount = VolunteerActivity::where('volunteer_id', $userId)
                ->where('status', 'Verified')
                ->count();
            $score += min($activityCount * 4, 20); // Max 20 points
        }

        // Social engagement (30 points)
        $reviewsGiven = \App\Models\Review::where('reviewer_id', $userId)->count();
        $score += min($reviewsGiven * 5, 15); // Max 15 points

        // Recency (bonus)
        if ($user->last_login_at && $user->last_login_at->diffInDays(now()) < 7) {
            $score += 15;
        }

        return min($score, 100); // Cap at 100
    }
}