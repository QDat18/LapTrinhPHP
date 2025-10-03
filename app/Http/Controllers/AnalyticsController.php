<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\VolunteerOpportunity;
use App\Models\Application;
use App\Models\VolunteerActivity;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    // Main analytics dashboard
    public function index(Request $request)
    {
        $user = Auth::user();

        // Route to appropriate dashboard based on user type
        if ($user->user_type === 'Admin') {
            return $this->adminDashboard($request);
        } elseif ($user->user_type === 'Organization') {
            return $this->organizationDashboard($request);
        } else {
            return $this->volunteerDashboard($request);
        }
    }

    // Admin analytics dashboard
    private function adminDashboard(Request $request)
    {
        $period = $request->get('period', '30days'); // 7days, 30days, 90days, year
        $startDate = $this->getStartDate($period);

        // Platform-wide metrics
        $metrics = [
            'total_users' => User::where('is_active', true)->count(),
            'total_volunteers' => User::where('user_type', 'Volunteer')->where('is_active', true)->count(),
            'total_organizations' => Organization::where('verification_status', 'Verified')->count(),
            'total_opportunities' => VolunteerOpportunity::count(),
            'active_opportunities' => VolunteerOpportunity::where('status', 'Active')->count(),
            'total_applications' => Application::count(),
            'accepted_applications' => Application::where('status', 'Accepted')->count(),
            'total_volunteer_hours' => VolunteerActivity::where('status', 'Verified')->sum('hours_worked'),
            'total_activities' => VolunteerActivity::where('status', 'Verified')->count(),
        ];

        // Growth metrics for selected period
        $growth = [
            'new_users' => User::where('created_at', '>=', $startDate)->count(),
            'new_opportunities' => VolunteerOpportunity::where('created_at', '>=', $startDate)->count(),
            'new_applications' => Application::where('created_at', '>=', $startDate)->count(),
            'hours_logged' => VolunteerActivity::where('activity_date', '>=', $startDate)
                ->where('status', 'Verified')
                ->sum('hours_worked'),
        ];

        // User registration trend (last 30 days)
        $userTrend = DB::table('users')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Applications by status
        $applicationsByStatus = Application::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        // Top categories by opportunity count
        $topCategories = DB::table('volunteer_opportunities')
            ->join('categories', 'volunteer_opportunities.category_id', '=', 'categories.category_id')
            ->select('categories.category_name', DB::raw('COUNT(*) as count'))
            ->groupBy('categories.category_id', 'categories.category_name')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        // Top volunteers by hours
        $topVolunteers = DB::table('volunteer_activities')
            ->join('users', 'volunteer_activities.volunteer_id', '=', 'users.user_id')
            ->select(
                'users.user_id',
                'users.first_name',
                'users.last_name',
                DB::raw('SUM(volunteer_activities.hours_worked) as total_hours')
            )
            ->where('volunteer_activities.status', 'Verified')
            ->groupBy('users.user_id', 'users.first_name', 'users.last_name')
            ->orderBy('total_hours', 'desc')
            ->limit(10)
            ->get();

        // Top organizations by volunteer count
        $topOrganizations = DB::table('organizations')
            ->select('organization_name', 'volunteer_count', 'rating')
            ->orderBy('volunteer_count', 'desc')
            ->limit(10)
            ->get();

        // Monthly volunteer hours trend (last 12 months)
        $monthlyHours = DB::table('volunteer_activities')
            ->select(
                DB::raw('YEAR(activity_date) as year'),
                DB::raw('MONTH(activity_date) as month'),
                DB::raw('SUM(hours_worked) as total_hours')
            )
            ->where('activity_date', '>=', now()->subMonths(12))
            ->where('status', 'Verified')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        return view('admin.analytics.index', compact(
            'metrics',
            'growth',
            'userTrend',
            'applicationsByStatus',
            'topCategories',
            'topVolunteers',
            'topOrganizations',
            'monthlyHours',
            'period'
        ));
    }

    // Organization analytics dashboard
    private function organizationDashboard(Request $request)
    {
        $user = Auth::user();
        $orgId = $user->organization->org_id;
        $period = $request->get('period', '30days');
        $startDate = $this->getStartDate($period);

        // Organization metrics
        $metrics = [
            'total_opportunities' => VolunteerOpportunity::where('org_id', $orgId)->count(),
            'active_opportunities' => VolunteerOpportunity::where('org_id', $orgId)
                ->where('status', 'Active')->count(),
            'total_applications' => Application::whereHas('opportunity', function ($q) use ($orgId) {
                $q->where('org_id', $orgId);
            })->count(),
            'pending_applications' => Application::whereHas('opportunity', function ($q) use ($orgId) {
                $q->where('org_id', $orgId);
            })->where('status', 'Pending')->count(),
            'accepted_volunteers' => Application::whereHas('opportunity', function ($q) use ($orgId) {
                $q->where('org_id', $orgId);
            })->where('status', 'Accepted')->count(),
            'total_volunteer_hours' => VolunteerActivity::where('org_id', $orgId)
                ->where('status', 'Verified')->sum('hours_worked'),
            'unique_volunteers' => VolunteerActivity::where('org_id', $orgId)
                ->where('status', 'Verified')
                ->distinct('volunteer_id')
                ->count('volunteer_id'),
            'average_rating' => $user->organization->rating,
            'total_reviews' => DB::table('reviews')
                ->where('reviewee_id', $user->user_id)
                ->where('is_approved', true)
                ->count(),
        ];

        // Growth metrics
        $growth = [
            'new_applications' => Application::whereHas('opportunity', function ($q) use ($orgId) {
                $q->where('org_id', $orgId);
            })->where('applied_date', '>=', $startDate)->count(),
            'new_volunteers' => Application::whereHas('opportunity', function ($q) use ($orgId) {
                $q->where('org_id', $orgId);
            })->where('status', 'Accepted')
                ->where('reviewed_date', '>=', $startDate)->count(),
            'hours_contributed' => VolunteerActivity::where('org_id', $orgId)
                ->where('activity_date', '>=', $startDate)
                ->where('status', 'Verified')
                ->sum('hours_worked'),
        ];

        // Applications trend (last 30 days)
        $applicationsTrend = DB::table('applications')
            ->join('volunteer_opportunities', 'applications.opportunity_id', '=', 'volunteer_opportunities.opportunity_id')
            ->select(DB::raw('DATE(applications.applied_date) as date'), DB::raw('COUNT(*) as count'))
            ->where('volunteer_opportunities.org_id', $orgId)
            ->where('applications.applied_date', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top performing opportunities
        $topOpportunities = VolunteerOpportunity::where('org_id', $orgId)
            ->withCount('applications')
            ->orderBy('applications_count', 'desc')
            ->limit(10)
            ->get();

        // Volunteer hours by month (last 6 months)
        $monthlyHours = DB::table('volunteer_activities')
            ->select(
                DB::raw('YEAR(activity_date) as year'),
                DB::raw('MONTH(activity_date) as month'),
                DB::raw('SUM(hours_worked) as total_hours'),
                DB::raw('COUNT(DISTINCT volunteer_id) as volunteer_count')
            )
            ->where('org_id', $orgId)
            ->where('activity_date', '>=', now()->subMonths(6))
            ->where('status', 'Verified')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Application conversion rate
        $conversionRate = [
            'total' => Application::whereHas('opportunity', function ($q) use ($orgId) {
                $q->where('org_id', $orgId);
            })->count(),
            'accepted' => Application::whereHas('opportunity', function ($q) use ($orgId) {
                $q->where('org_id', $orgId);
            })->where('status', 'Accepted')->count(),
        ];
        $conversionRate['rate'] = $conversionRate['total'] > 0
            ? round(($conversionRate['accepted'] / $conversionRate['total']) * 100, 2)
            : 0;

        // Top volunteer contributors
        $topContributors = DB::table('volunteer_activities')
            ->join('users', 'volunteer_activities.volunteer_id', '=', 'users.user_id')
            ->select(
                'users.user_id',
                'users.first_name',
                'users.last_name',
                DB::raw('SUM(volunteer_activities.hours_worked) as total_hours'),
                DB::raw('COUNT(*) as activity_count')
            )
            ->where('volunteer_activities.org_id', $orgId)
            ->where('volunteer_activities.status', 'Verified')
            ->groupBy('users.user_id', 'users.first_name', 'users.last_name')
            ->orderBy('total_hours', 'desc')
            ->limit(10)
            ->get();

        return view('analytics.organization', compact(
            'metrics',
            'growth',
            'applicationsTrend',
            'topOpportunities',
            'monthlyHours',
            'conversionRate',
            'topContributors',
            'period'
        ));
    }

    // Volunteer analytics dashboard
    private function volunteerDashboard(Request $request)
    {
        $user = Auth::user();
        $period = $request->get('period', '30days');
        $startDate = $this->getStartDate($period);

        // Volunteer metrics
        $metrics = [
            'total_applications' => Application::where('volunteer_id', $user->user_id)->count(),
            'accepted_applications' => Application::where('volunteer_id', $user->user_id)
                ->where('status', 'Accepted')->count(),
            'pending_applications' => Application::where('volunteer_id', $user->user_id)
                ->where('status', 'Pending')->count(),
            'total_volunteer_hours' => VolunteerActivity::where('volunteer_id', $user->user_id)
                ->where('status', 'Verified')->sum('hours_worked'),
            'total_activities' => VolunteerActivity::where('volunteer_id', $user->user_id)
                ->where('status', 'Verified')->count(),
            'organizations_worked_with' => VolunteerActivity::where('volunteer_id', $user->user_id)
                ->where('status', 'Verified')
                ->distinct('org_id')
                ->count('org_id'),
            'average_rating' => $user->volunteerProfile->volunteer_rating ?? 0,
            'total_reviews' => DB::table('reviews')
                ->where('reviewee_id', $user->user_id)
                ->where('is_approved', true)
                ->count(),
        ];

        // Growth metrics for selected period
        $growth = [
            'new_applications' => Application::where('volunteer_id', $user->user_id)
                ->where('applied_date', '>=', $startDate)->count(),
            'hours_contributed' => VolunteerActivity::where('volunteer_id', $user->user_id)
                ->where('activity_date', '>=', $startDate)
                ->where('status', 'Verified')
                ->sum('hours_worked'),
            'activities_completed' => VolunteerActivity::where('volunteer_id', $user->user_id)
                ->where('activity_date', '>=', $startDate)
                ->where('status', 'Verified')
                ->count(),
        ];

        // Volunteer hours by month (last 12 months)
        $monthlyHours = DB::table('volunteer_activities')
            ->select(
                DB::raw('YEAR(activity_date) as year'),
                DB::raw('MONTH(activity_date) as month'),
                DB::raw('SUM(hours_worked) as total_hours')
            )
            ->where('volunteer_id', $user->user_id)
            ->where('activity_date', '>=', now()->subMonths(12))
            ->where('status', 'Verified')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Activities by category
        $activitiesByCategory = DB::table('volunteer_activities')
            ->join('volunteer_opportunities', 'volunteer_activities.opportunity_id', '=', 'volunteer_opportunities.opportunity_id')
            ->join('categories', 'volunteer_opportunities.category_id', '=', 'categories.category_id')
            ->select('categories.category_name', DB::raw('SUM(volunteer_activities.hours_worked) as total_hours'))
            ->where('volunteer_activities.volunteer_id', $user->user_id)
            ->where('volunteer_activities.status', 'Verified')
            ->groupBy('categories.category_id', 'categories.category_name')
            ->get();

        // Organizations worked with
        $organizationsWorkedWith = DB::table('volunteer_activities')
            ->join('organizations', 'volunteer_activities.org_id', '=', 'organizations.org_id')
            ->select(
                'organizations.organization_name',
                DB::raw('SUM(volunteer_activities.hours_worked) as total_hours'),
                DB::raw('COUNT(*) as activity_count')
            )
            ->where('volunteer_activities.volunteer_id', $user->user_id)
            ->where('volunteer_activities.status', 'Verified')
            ->groupBy('organizations.org_id', 'organizations.organization_name')
            ->orderBy('total_hours', 'desc')
            ->get();

        // Application success rate
        $applicationRate = [
            'total' => Application::where('volunteer_id', $user->user_id)->count(),
            'accepted' => Application::where('volunteer_id', $user->user_id)
                ->where('status', 'Accepted')->count(),
            'rejected' => Application::where('volunteer_id', $user->user_id)
                ->where('status', 'Rejected')->count(),
        ];
        $applicationRate['success_rate'] = $applicationRate['total'] > 0
            ? round(($applicationRate['accepted'] / $applicationRate['total']) * 100, 2)
            : 0;

        // Recent achievements/milestones
        $achievements = $this->getVolunteerAchievements($user->user_id, $metrics);

        return view('analytics.volunteer', compact(
            'metrics',
            'growth',
            'monthlyHours',
            'activitiesByCategory',
            'organizationsWorkedWith',
            'applicationRate',
            'achievements',
            'period'
        ));
    }

    // Custom report generation
    public function customReport(Request $request)
    {
        $this->authorize('admin');

        $validated = $request->validate([
            'report_type' => 'required|in:users,opportunities,applications,activities,organizations',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'filters' => 'nullable|array'
        ]);

        $data = [];

        switch ($validated['report_type']) {
            case 'users':
                $data = $this->getUsersReport($validated['start_date'], $validated['end_date'], $validated['filters'] ?? []);
                break;
            case 'opportunities':
                $data = $this->getOpportunitiesReport($validated['start_date'], $validated['end_date'], $validated['filters'] ?? []);
                break;
            case 'applications':
                $data = $this->getApplicationsReport($validated['start_date'], $validated['end_date'], $validated['filters'] ?? []);
                break;
            case 'activities':
                $data = $this->getActivitiesReport($validated['start_date'], $validated['end_date'], $validated['filters'] ?? []);
                break;
            case 'organizations':
                $data = $this->getOrganizationsReport($validated['start_date'], $validated['end_date'], $validated['filters'] ?? []);
                break;
        }

        return view('analytics.custom-report', compact('data', 'validated'));
    }

    // Export report to CSV/Excel
    public function exportReport(Request $request)
    {
        $validated = $request->validate([
            'report_type' => 'required|in:users,opportunities,applications,activities,impact',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'format' => 'required|in:csv,excel'
        ]);

        $data = $this->generateReportData($validated);

        $filename = $validated['report_type'] . '_report_' . date('Y-m-d') . '.' . $validated['format'];

        if ($validated['format'] === 'csv') {
            return $this->exportToCSV($data, $filename);
        } else {
            return $this->exportToExcel($data, $filename);
        }
    }

    // Impact report
    public function impactReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->subMonths(3)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $impact = [
            'total_volunteer_hours' => VolunteerActivity::where('activity_date', '>=', $startDate)
                ->where('activity_date', '<=', $endDate)
                ->where('status', 'Verified')
                ->sum('hours_worked'),

            'total_volunteers' => VolunteerActivity::where('activity_date', '>=', $startDate)
                ->where('activity_date', '<=', $endDate)
                ->where('status', 'Verified')
                ->distinct('volunteer_id')
                ->count('volunteer_id'),

            'total_organizations' => VolunteerActivity::where('activity_date', '>=', $startDate)
                ->where('activity_date', '<=', $endDate)
                ->where('status', 'Verified')
                ->distinct('org_id')
                ->count('org_id'),

            'total_activities' => VolunteerActivity::where('activity_date', '>=', $startDate)
                ->where('activity_date', '<=', $endDate)
                ->where('status', 'Verified')
                ->count(),

            'economic_value' => 0, // Calculate based on average hourly rate
        ];

        // Calculate economic value (average VN hourly rate: 50,000 VND)
        $impact['economic_value'] = $impact['total_volunteer_hours'] * 50000;

        // Impact by category
        $impactByCategory = DB::table('volunteer_activities')
            ->join('volunteer_opportunities', 'volunteer_activities.opportunity_id', '=', 'volunteer_opportunities.opportunity_id')
            ->join('categories', 'volunteer_opportunities.category_id', '=', 'categories.category_id')
            ->select(
                'categories.category_name',
                DB::raw('SUM(volunteer_activities.hours_worked) as total_hours'),
                DB::raw('COUNT(DISTINCT volunteer_activities.volunteer_id) as volunteer_count')
            )
            ->where('volunteer_activities.activity_date', '>=', $startDate)
            ->where('volunteer_activities.activity_date', '<=', $endDate)
            ->where('volunteer_activities.status', 'Verified')
            ->groupBy('categories.category_id', 'categories.category_name')
            ->get();

        // Geographic distribution
        $geographicDistribution = DB::table('volunteer_activities')
            ->join('volunteer_opportunities', 'volunteer_activities.opportunity_id', '=', 'volunteer_opportunities.opportunity_id')
            ->join('users', 'volunteer_activities.volunteer_id', '=', 'users.user_id')
            ->select(
                'users.city',
                DB::raw('SUM(volunteer_activities.hours_worked) as total_hours'),
                DB::raw('COUNT(DISTINCT volunteer_activities.volunteer_id) as volunteer_count')
            )
            ->where('volunteer_activities.activity_date', '>=', $startDate)
            ->where('volunteer_activities.activity_date', '<=', $endDate)
            ->where('volunteer_activities.status', 'Verified')
            ->whereNotNull('users.city')
            ->groupBy('users.city')
            ->orderBy('total_hours', 'desc')
            ->limit(10)
            ->get();

        return view('admin.analytics.impact', compact(
            'impact',
            'impactByCategory',
            'geographicDistribution',
            'startDate',
            'endDate'
        ));
    }

    // Helper: Get start date based on period
    private function getStartDate($period)
    {
        switch ($period) {
            case '7days':
                return now()->subDays(7);
            case '30days':
                return now()->subDays(30);
            case '90days':
                return now()->subDays(90);
            case 'year':
                return now()->subYear();
            default:
                return now()->subDays(30);
        }
    }

    // Helper: Get volunteer achievements
    private function getVolunteerAchievements($volunteerId, $metrics)
    {
        $achievements = [];

        // Hour milestones
        $hourMilestones = [10, 50, 100, 500, 1000];
        foreach ($hourMilestones as $milestone) {
            if ($metrics['total_volunteer_hours'] >= $milestone) {
                $achievements[] = [
                    'title' => "$milestone+ Giờ Tình Nguyện",
                    'icon' => '⏰',
                    'achieved' => true
                ];
            }
        }

        // Activity count milestones
        if ($metrics['total_activities'] >= 10) {
            $achievements[] = [
                'title' => '10+ Hoạt Động Hoàn Thành',
                'icon' => '🎯',
                'achieved' => true
            ];
        }

        // Multiple organizations
        if ($metrics['organizations_worked_with'] >= 5) {
            $achievements[] = [
                'title' => 'Làm Việc Với 5+ Tổ Chức',
                'icon' => '🤝',
                'achieved' => true
            ];
        }

        // High rating
        if ($metrics['average_rating'] >= 4.5) {
            $achievements[] = [
                'title' => 'Đánh Giá Xuất Sắc (4.5+)',
                'icon' => '⭐',
                'achieved' => true
            ];
        }

        return $achievements;
    }

    // Helper methods for custom reports
    private function getUsersReport($startDate, $endDate, $filters)
    {
        $query = User::whereBetween('created_at', [$startDate, $endDate]);

        if (isset($filters['user_type'])) {
            $query->where('user_type', $filters['user_type']);
        }

        return $query->get();
    }

    private function getOpportunitiesReport($startDate, $endDate, $filters)
    {
        $query = VolunteerOpportunity::whereBetween('created_at', [$startDate, $endDate]);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->with(['organization', 'category'])->get();
    }

    private function getApplicationsReport($startDate, $endDate, $filters)
    {
        $query = Application::whereBetween('applied_date', [$startDate, $endDate]);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->with(['volunteer', 'opportunity'])->get();
    }

    private function getActivitiesReport($startDate, $endDate, $filters)
    {
        $query = VolunteerActivity::whereBetween('activity_date', [$startDate, $endDate]);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->with(['volunteer', 'opportunity', 'organization'])->get();
    }

    private function getOrganizationsReport($startDate, $endDate, $filters)
    {
        $query = Organization::whereBetween('created_at', [$startDate, $endDate]);

        if (isset($filters['verification_status'])) {
            $query->where('verification_status', $filters['verification_status']);
        }

        return $query->with('user')->get();
    }

    // Export helpers
    private function exportToCSV($data, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Add headers and data based on report type
            // Implementation depends on data structure

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportToExcel($data, $filename)
    {
        // Would use PHPSpreadsheet library in production
        return response()->download($filename);
    }

    private function generateReportData($validated)
    {
        // Generate report data based on type and date range
        return [];
    }
}
