<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Organization;
use App\Models\VolunteerOpportunity;
use App\Models\Application;
use App\Models\VolunteerActivity;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Events\Verified;


class AdminDashboardController extends Controller
{
    public function __construct(){
        $this->middleware(['auth', 'role:Admin']);
    }

    public function index(){
        $stats = [
            'total_users' => User::count(),
            'total_volunteers' => User::where('user_type', 'Volunteer')->count(),
            'total_organizations' => Organization::count(),
            'verified_organizations' => Organization::where('verification_status', 'Verified')->count(),
            'pending_organizations' => Organization::where('verification_status', 'Pending')->count(),
            'total_opportunities' => VolunteerOpportunity::count(),
            'active_opportunities' => VolunteerActivity::where('status', 'Active')->count(),
            'completed_opportunities' => VolunteerOpportunity::where('status', 'Completed')->count(),
            'total_applications' => Application::count(),
            'accepted_applications' => Application::where('status', 'Accepted')->count(),
            'pending_applications' => Application::where('status', 'Pending')->count(),
            'total_volunteer_hours' => VolunteerActivity::where('status', 'Verified')->sum('hours_worked'),
            'pending_hour_verifications' => VolunteerActivity::where('status', 'Pending')->count(),
            'total_reviews' => Review::count(),
            'average_rating' => Review::avg('rating'),
            'pending_review_approvals' => Review::where('is_approved', false)->count(),
        ];

        $stats['new_users_this_month'] = User::whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->subMonth()->year)->count();

        $lastMonthUsers = User::whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->count();
        
        $stats['user_growth_percentage'] = $lastMonthUsers > 0 ? round((($stats['new_users_this_month'] - $lastMonthUsers) / $lastMonthUsers) * 100, 2) : ($stats['new_users_this_month'] > 0 ? 100 : 0);

        $recentActivity = [
            'new_opportunities' => VolunteerOpportunity::where('created_at', '>=', Carbon::now()->subDays(7))->count(),
            'new_applications' => Application::where('created_at', '>=', Carbon::now()->subDays(7))->count(),
            'verified_hours' => VolunteerActivity::where('verified_date', '>=', Carbon::now()->subDays(7))->sum('hours_worked'),
        ];
        $topVolunteers = User::select('users.*', DB::raw('SUM(volunteer_activities.hours_worked) as total_hours'))
            ->join('volunteer_activities', 'users.user_id', '=', 'volunteer_activities.volunteer_id')
            ->where('volunteer_activities.status', 'Verified')
            ->where('users.user_type', 'Volunteer')
            ->groupBy('users.user_id')
            ->orderByDesc('total_hours')
            ->limit(10)
            ->get();


        $topOrganizations = Organization::select('organizations.*', DB::raw('COUNT(DISTINCT applications.volunteer_id) as volunteer_count'))
            ->join('volunteer_opportunities', 'organizations.org_id', '=', 'volunteer_opportunities.org_id')
            ->join('applications', 'volunteer_opportunities.opportunity_id', '=', 'applications.opportunity_id')
            ->where('applications.status', 'Accepted')
            ->groupBy('organizations.org_id')
            ->orderByDesc('volunteer_count')
            ->limit(10)
            ->get();
        
        $userRegistrationData = $this->getUserRegistrationChartData();
        $applicationData = $this->getApplicationStatusChartData();
        $opportunityCategoryData = $this->getOpportunityCategoryChartData();

        return view('admin.dashboard', compact('stats', 'recentActivity', 'topVolunteers', 'topOrganizations', 'userRegistrationData', 'applicationData', 'opportunityCategoryData'));
    }
    
    private function getUserRegistrationChartData(){
        $data = [
            'labels' => [],
            'volunteers' => [],
            'organizations' => [],
        ];
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);

            $data['labels'][] = $month->format('M Y');
            $data['volunteers'][] = User::where('user_type', 'Volunteer')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
            $data['organizations'][] = User::where('user_type', 'Organization')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        }
        return $data;
    }

    private function getApplicationStatusChartData(){
        $statuses = ['Pending', 'Under Review', 'Accepted', 'Rejected', 'Withdrawn'];
        $data = [];
        foreach($statuses as $status){
            $data['labels'][] = $status;
            $data['values'][] = Application::where('status', $status)->count();
        }
        return $data;
    }

    private function getOpportunityCategoryChartData(){
        $categoryData = DB::table('volunteer_opportunities')
            ->join('categories', 'volunteer_opportunities.category_id', '=', 'categories.id')
            ->select('categories.category_name', DB::raw('COUNT(*) as count'))
            ->groupBy('categories.category_id', 'categories.category_name')
            ->orderByDesc('count')
            ->get();
        $data = [
            'labels' => $categoryData->pluck('category_name')->toArray(),
            'values' => $categoryData->pluck('count')->toArray()
        ];
        return $data;
    }

    public function analytics()
    {
        $analytics = [
            'engagement' => [
                'avg_applications_per_opportunity' => round(Application::count() / max(VolunteerOpportunity::count(), 1), 2),
                'application_acceptance_rate' => $this->calculateAcceptanceRate(),
                'volunteer_retention_rate' => $this->calculateRetentionRate(),
                'avg_volunteer_hours_per_person' => $this->calculateAvgHoursPerVolunteer(),
            ],

            'geographic' => $this->getGeographicDistribution(),

            'trends' => [
                'daily_active_users' => $this->getDailyActiveUsers(30),
                'opportunity_creation_trend' => $this->getOpportunityCreationTrend(90),
            ],

            'performance' => [
                'avg_response_time' => $this->calculateAvgResponseTime(),
                'completion_rate' => $this->calculateCompletionRate(),
            ],
        ];

        return view('admin.analytics', compact('analytics'));
    }

    private function calculateAcceptanceRate()
    {
        $total = Application::count();
        if ($total == 0) return 0;
        $accepted = Application::where('status', 'Accepted')->count();
        return round(($accepted / $total) * 100, 2);
    }
    private function calculateRetentionRate()
    {
        $repeatedVolunteers = DB::table('volunteer_activities')
            ->select('volunteer_id')
            ->groupBy('volunteer_id')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        $totalVolunteers = User::where('user_type', 'Volunteer')->count();
        
        return $totalVolunteers > 0 
            ? round(($repeatedVolunteers / $totalVolunteers) * 100, 2)
            : 0;
    }

    private function calculateAvgHoursPerVolunteer()
    {
        $totalHours = VolunteerActivity::where('status', 'Verified')->sum('hours_worked');
        $totalVolunteers = User::where('user_type', 'Volunteer')->count();
        
        return $totalVolunteers > 0 
            ? round($totalHours / $totalVolunteers, 2)
            : 0;
    }

    private function getGeographicDistribution()
    {
        return User::select('city', DB::raw('COUNT(*) as count'))
            ->whereNotNull('city')
            ->groupBy('city')
            ->orderByDesc('count')
            ->limit(10)
            ->get();
    }

    private function getDailyActiveUsers($days)
    {
        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $count = User::whereDate('last_login_at', $date)->count();
            $data[$date] = $count;
        }
        return $data;
    }

    private function getOpportunityCreationTrend($days)
    {
        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $count = VolunteerOpportunity::whereDate('created_at', $date)->count();
            $data[$date] = $count;
        }
        return $data;
    }
    private function calculateAvgResponseTime()
    {
        $avgSeconds = Application::whereNotNull('reviewed_date')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, applied_date, reviewed_date)) as avg_time')
            ->value('avg_time');

        if (!$avgSeconds) return 'N/A';

        $hours = floor($avgSeconds / 3600);
        return $hours . ' hours';
    }

    private function calculateCompletionRate()
    {
        $total = VolunteerOpportunity::whereIn('status', ['Active', 'Completed', 'Cancelled'])->count();
        if ($total == 0) return 0;
        
        $completed = VolunteerOpportunity::where('status', 'Completed')->count();
        return round(($completed / $total) * 100, 2);
    }
}
?>