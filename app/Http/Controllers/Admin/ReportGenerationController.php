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
use PDF; // Assuming you're using barryvdh/laravel-dompdf

class ReportGenerationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:Admin']);
    }

    /**
     * Display report generation page
     */
    public function index()
    {
        // Pre-defined report types
        $reportTypes = [
            'user_summary' => 'User Summary Report',
            'opportunity_summary' => 'Opportunity Summary Report',
            'volunteer_activity' => 'Volunteer Activity Report',
            'organization_performance' => 'Organization Performance Report',
            'platform_overview' => 'Platform Overview Report',
            'financial_summary' => 'Financial Summary Report',
        ];

        return view('admin.reports.index', compact('reportTypes'));
    }

    /**
     * Generate report based on type
     */
    public function generate(Request $request)
    {
        $reportType = $request->get('report_type', 'platform_overview');
        $dateFrom = $request->get('date_from', Carbon::now()->subMonth()->toDateString());
        $dateTo = $request->get('date_to', Carbon::now()->toDateString());

        $data = [];

        switch ($reportType) {
            case 'user_summary':
                $data = $this->generateUserSummary($dateFrom, $dateTo);
                break;
            case 'opportunity_summary':
                $data = $this->generateOpportunitySummary($dateFrom, $dateTo);
                break;
            case 'volunteer_activity':
                $data = $this->generateVolunteerActivityReport($dateFrom, $dateTo);
                break;
            case 'organization_performance':
                $data = $this->generateOrganizationPerformance($dateFrom, $dateTo);
                break;
            case 'platform_overview':
                $data = $this->generatePlatformOverview($dateFrom, $dateTo);
                break;
            case 'financial_summary':
                $data = $this->generateFinancialSummary($dateFrom, $dateTo);
                break;
        }

        $data['report_type'] = $reportType;
        $data['date_from'] = $dateFrom;
        $data['date_to'] = $dateTo;
        $data['generated_at'] = now();

        return view('admin.reports.view', compact('data', 'reportType'));
    }

    /**
     * Generate User Summary Report
     */
    private function generateUserSummary($dateFrom, $dateTo)
    {
        return [
            'total_users' => User::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'volunteers' => User::where('user_type', 'Volunteer')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->count(),
            'organizations' => User::where('user_type', 'Organization')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->count(),
            'verified_users' => User::where('is_verified', true)
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->count(),
            'active_users' => User::where('is_active', true)
                ->whereBetween('last_login_at', [$dateFrom, $dateTo])
                ->count(),
            'user_by_city' => User::select('city', DB::raw('COUNT(*) as count'))
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->whereNotNull('city')
                ->groupBy('city')
                ->orderByDesc('count')
                ->get(),
            'registrations_by_day' => User::select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as count')
                )
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
        ];
    }

    /**
     * Generate Opportunity Summary Report
     */
    private function generateOpportunitySummary($dateFrom, $dateTo)
    {
        return [
            'total_opportunities' => VolunteerOpportunity::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'active_opportunities' => VolunteerOpportunity::where('status', 'Active')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->count(),
            'completed_opportunities' => VolunteerOpportunity::where('status', 'Completed')
                ->whereBetween('updated_at', [$dateFrom, $dateTo])
                ->count(),
            'opportunities_by_category' => VolunteerOpportunity::select('categories.category_name', DB::raw('COUNT(*) as count'))
                ->join('categories', 'volunteer_opportunities.category_id', '=', 'categories.category_id')
                ->whereBetween('volunteer_opportunities.created_at', [$dateFrom, $dateTo])
                ->groupBy('categories.category_name')
                ->orderByDesc('count')
                ->get(),
            'opportunities_by_location' => VolunteerOpportunity::select('location', DB::raw('COUNT(*) as count'))
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->whereNotNull('location')
                ->groupBy('location')
                ->orderByDesc('count')
                ->limit(10)
                ->get(),
            'avg_applications_per_opportunity' => Application::whereBetween('created_at', [$dateFrom, $dateTo])
                ->count() / max(VolunteerOpportunity::whereBetween('created_at', [$dateFrom, $dateTo])->count(), 1),
        ];
    }

    /**
     * Generate Volunteer Activity Report
     */
    private function generateVolunteerActivityReport($dateFrom, $dateTo)
    {
        return [
            'total_hours' => VolunteerActivity::where('status', 'Verified')
                ->whereBetween('activity_date', [$dateFrom, $dateTo])
                ->sum('hours_worked'),
            'total_activities' => VolunteerActivity::whereBetween('activity_date', [$dateFrom, $dateTo])->count(),
            'verified_activities' => VolunteerActivity::where('status', 'Verified')
                ->whereBetween('activity_date', [$dateFrom, $dateTo])
                ->count(),
            'pending_activities' => VolunteerActivity::where('status', 'Pending')
                ->whereBetween('activity_date', [$dateFrom, $dateTo])
                ->count(),
            'active_volunteers' => VolunteerActivity::whereBetween('activity_date', [$dateFrom, $dateTo])
                ->distinct('volunteer_id')
                ->count('volunteer_id'),
            'top_volunteers' => User::select('users.*', DB::raw('SUM(volunteer_activities.hours_worked) as total_hours'))
                ->join('volunteer_activities', 'users.user_id', '=', 'volunteer_activities.volunteer_id')
                ->where('volunteer_activities.status', 'Verified')
                ->whereBetween('volunteer_activities.activity_date', [$dateFrom, $dateTo])
                ->groupBy('users.user_id')
                ->orderByDesc('total_hours')
                ->limit(20)
                ->get(),
            'hours_by_category' => DB::table('volunteer_activities')
                ->join('volunteer_opportunities', 'volunteer_activities.opportunity_id', '=', 'volunteer_opportunities.opportunity_id')
                ->join('categories', 'volunteer_opportunities.category_id', '=', 'categories.category_id')
                ->select('categories.category_name', DB::raw('SUM(volunteer_activities.hours_worked) as total_hours'))
                ->where('volunteer_activities.status', 'Verified')
                ->whereBetween('volunteer_activities.activity_date', [$dateFrom, $dateTo])
                ->groupBy('categories.category_name')
                ->orderByDesc('total_hours')
                ->get(),
        ];
    }

    /**
     * Generate Organization Performance Report
     */
    private function generateOrganizationPerformance($dateFrom, $dateTo)
    {
        return [
            'total_organizations' => Organization::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'verified_organizations' => Organization::where('verification_status', 'Verified')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->count(),
            'top_organizations' => Organization::select('organizations.*', 
                    DB::raw('COUNT(DISTINCT volunteer_activities.volunteer_id) as volunteer_count'),
                    DB::raw('SUM(volunteer_activities.hours_worked) as total_hours')
                )
                ->join('volunteer_opportunities', 'organizations.org_id', '=', 'volunteer_opportunities.org_id')
                ->join('volunteer_activities', 'volunteer_opportunities.opportunity_id', '=', 'volunteer_activities.opportunity_id')
                ->where('volunteer_activities.status', 'Verified')
                ->whereBetween('volunteer_activities.activity_date', [$dateFrom, $dateTo])
                ->groupBy('organizations.org_id')
                ->orderByDesc('total_hours')
                ->limit(20)
                ->get(),
            'organizations_by_type' => Organization::select('organization_type', DB::raw('COUNT(*) as count'))
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->groupBy('organization_type')
                ->get(),
            'avg_rating' => Organization::where('verification_status', 'Verified')
                ->avg('rating'),
        ];
    }

    /**
     * Generate Platform Overview Report
     */
    private function generatePlatformOverview($dateFrom, $dateTo)
    {
        return [
            'users' => $this->generateUserSummary($dateFrom, $dateTo),
            'opportunities' => $this->generateOpportunitySummary($dateFrom, $dateTo),
            'activities' => $this->generateVolunteerActivityReport($dateFrom, $dateTo),
            'organizations' => $this->generateOrganizationPerformance($dateFrom, $dateTo),
            'applications' => [
                'total' => Application::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                'accepted' => Application::where('status', 'Accepted')
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->count(),
                'pending' => Application::where('status', 'Pending')
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->count(),
            ],
            'reviews' => [
                'total' => Review::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                'average_rating' => Review::whereBetween('created_at', [$dateFrom, $dateTo])->avg('rating'),
            ],
        ];
    }

    /**
     * Generate Financial Summary Report (if applicable)
     */
    private function generateFinancialSummary($dateFrom, $dateTo)
    {
        // This would include payment/donation tracking if implemented
        return [
            'total_volunteer_hours_value' => VolunteerActivity::where('status', 'Verified')
                ->whereBetween('activity_date', [$dateFrom, $dateTo])
                ->sum('hours_worked') * 15, // Assuming $15/hour value
            'platform_savings_for_organizations' => 'Calculated based on volunteer contributions',
            // Add more financial metrics as needed
        ];
    }

    /**
     * Download report as PDF/CSV
     */
    public function download(Request $request, $type)
    {
        $reportType = $request->get('report_type', 'platform_overview');
        $dateFrom = $request->get('date_from', Carbon::now()->subMonth()->toDateString());
        $dateTo = $request->get('date_to', Carbon::now()->toDateString());

        if ($type == 'pdf') {
            return $this->downloadPDF($reportType, $dateFrom, $dateTo);
        } elseif ($type == 'csv') {
            return $this->downloadCSV($reportType, $dateFrom, $dateTo);
        }

        return redirect()->back()->with('error', 'Invalid download type');
    }

    private function downloadPDF($reportType, $dateFrom, $dateTo)
    {
        // Generate report data
        $data = [];
        // ... generate data based on reportType ...

        // Generate PDF
        $pdf = PDF::loadView('admin.reports.pdf', compact('data', 'reportType', 'dateFrom', 'dateTo'));
        
        $filename = $reportType . '_' . date('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }

    private function downloadCSV($reportType, $dateFrom, $dateTo)
    {
        // Generate CSV based on report type
        $filename = $reportType . '_' . date('Y-m-d') . '.csv';
        
        // Implementation depends on specific report type
        // Return CSV download
    }
}