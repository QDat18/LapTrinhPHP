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
use Illuminate\Support\Facades\Cache;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
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

    // Admin analytics dashboard - OPTIMIZED
    private function adminDashboard(Request $request)
    {
        $period = $request->get('period', '30days');
        $cacheKey = "admin_analytics_{$period}";

        // Cache for 15 minutes
        $data = Cache::remember($cacheKey, 900, function () use ($period) {
            $startDate = $this->getStartDate($period);

            // Platform-wide metrics - Single queries with indexes
            $metrics = [
                'total_users' => User::where('is_active', true)->count(),
                'total_volunteers' => User::where('user_type', 'Volunteer')->where('is_active', true)->count(),
                'total_organizations' => Organization::where('verification_status', 'Verified')->count(),
                'total_opportunities' => VolunteerOpportunity::count(),
                'active_opportunities' => VolunteerOpportunity::where('status', 'Active')->count(),
                'total_applications' => Application::count(),
                'accepted_applications' => Application::where('status', 'Accepted')->count(),
                'total_volunteer_hours' => VolunteerActivity::where('status', 'Verified')->sum('hours_worked') ?? 0,
                'total_activities' => VolunteerActivity::where('status', 'Verified')->count(),
            ];

            // Growth metrics
            $growth = [
                'new_users' => User::where('created_at', '>=', $startDate)->count(),
                'new_opportunities' => VolunteerOpportunity::where('created_at', '>=', $startDate)->count(),
                'new_applications' => Application::where('created_at', '>=', $startDate)->count(),
                'hours_logged' => VolunteerActivity::where('activity_date', '>=', $startDate)
                    ->where('status', 'Verified')
                    ->sum('hours_worked') ?? 0,
            ];

            // User trend - Grouped by week for better performance
            $userTrend = DB::table('users')
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as count')
                )
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Applications by status
            $applicationsByStatus = Application::select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status')
                ->toArray();

            // Top categories - Limited to 8
            $topCategories = DB::table('volunteer_opportunities')
                ->join('categories', 'volunteer_opportunities.category_id', '=', 'categories.category_id')
                ->select('categories.category_name', DB::raw('COUNT(*) as count'))
                ->groupBy('categories.category_id', 'categories.category_name')
                ->orderBy('count', 'desc')
                ->limit(8)
                ->get();

            // Top volunteers - Limited to 5
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
                ->limit(5)
                ->get();

            // Top organizations - Limited to 5
            $topOrganizations = DB::table('organizations')
                ->select('organization_name', 'volunteer_count', 'rating')
                ->orderBy('volunteer_count', 'desc')
                ->limit(5)
                ->get();

            // Monthly hours - Last 6 months only
            $monthlyHours = DB::table('volunteer_activities')
                ->select(
                    DB::raw('YEAR(activity_date) as year'),
                    DB::raw('MONTH(activity_date) as month'),
                    DB::raw('SUM(hours_worked) as total_hours')
                )
                ->where('activity_date', '>=', now()->subMonths(6))
                ->where('status', 'Verified')
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get();

            return [
                'metrics' => $metrics,
                'growth' => $growth,
                'userTrend' => $userTrend,
                'applicationsByStatus' => $applicationsByStatus,
                'topCategories' => $topCategories,
                'topVolunteers' => $topVolunteers,
                'topOrganizations' => $topOrganizations,
                'monthlyHours' => $monthlyHours,
            ];
        });

        return view('admin.analytics.index', array_merge($data, ['period' => $period]));
    }

    // Chart Data API - Separate endpoint for lazy loading
    public function getChartData(Request $request)
    {
        $period = $request->get('period', '30days');
        $cacheKey = "chart_data_{$period}";

        return Cache::remember($cacheKey, 900, function () use ($period) {
            $startDate = $this->getStartDate($period);

            return response()->json([
                'userTrend' => DB::table('users')
                    ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
                    ->where('created_at', '>=', $startDate)
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get(),
                'applicationsByStatus' => Application::select('status', DB::raw('COUNT(*) as count'))
                    ->groupBy('status')
                    ->pluck('count', 'status'),
                'monthlyHours' => DB::table('volunteer_activities')
                    ->select(
                        DB::raw('YEAR(activity_date) as year'),
                        DB::raw('MONTH(activity_date) as month'),
                        DB::raw('SUM(hours_worked) as total_hours')
                    )
                    ->where('activity_date', '>=', now()->subMonths(6))
                    ->where('status', 'Verified')
                    ->groupBy('year', 'month')
                    ->orderBy('year')
                    ->orderBy('month')
                    ->get()
            ]);
        });
    }

    // Organization analytics dashboard - OPTIMIZED
    private function organizationDashboard(Request $request)
    {
        $user = Auth::user();
        $orgId = $user->organization->org_id;
        $period = $request->get('period', '30days');
        $cacheKey = "org_analytics_{$orgId}_{$period}";

        $data = Cache::remember($cacheKey, 900, function () use ($orgId, $period) {
            $startDate = $this->getStartDate($period);

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
                'total_volunteer_hours' => VolunteerActivity::where('org_id', $orgId)
                    ->where('status', 'Verified')->sum('hours_worked') ?? 0,
            ];

            return ['metrics' => $metrics];
        });

        return view('analytics.organization', array_merge($data, ['period' => $period]));
    }

    // Volunteer analytics dashboard - OPTIMIZED
    private function volunteerDashboard(Request $request)
    {
        $user = Auth::user();
        $period = $request->get('period', '30days');
        $cacheKey = "volunteer_analytics_{$user->user_id}_{$period}";

        $data = Cache::remember($cacheKey, 900, function () use ($user, $period) {
            $startDate = $this->getStartDate($period);

            $metrics = [
                'total_applications' => Application::where('volunteer_id', $user->user_id)->count(),
                'accepted_applications' => Application::where('volunteer_id', $user->user_id)
                    ->where('status', 'Accepted')->count(),
                'total_volunteer_hours' => VolunteerActivity::where('volunteer_id', $user->user_id)
                    ->where('status', 'Verified')->sum('hours_worked') ?? 0,
            ];

            return ['metrics' => $metrics];
        });

        return view('analytics.volunteer', array_merge($data, ['period' => $period]));
    }

    // Impact report - OPTIMIZED
    public function impactReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->subMonths(3)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $cacheKey = "impact_report_{$startDate}_{$endDate}";

        $data = Cache::remember($cacheKey, 1800, function () use ($startDate, $endDate) {
            $impact = [
                'total_volunteer_hours' => VolunteerActivity::whereBetween('activity_date', [$startDate, $endDate])
                    ->where('status', 'Verified')
                    ->sum('hours_worked') ?? 0,
                'total_volunteers' => VolunteerActivity::whereBetween('activity_date', [$startDate, $endDate])
                    ->where('status', 'Verified')
                    ->distinct('volunteer_id')
                    ->count('volunteer_id'),
                'total_organizations' => VolunteerActivity::whereBetween('activity_date', [$startDate, $endDate])
                    ->where('status', 'Verified')
                    ->distinct('org_id')
                    ->count('org_id'),
                'total_activities' => VolunteerActivity::whereBetween('activity_date', [$startDate, $endDate])
                    ->where('status', 'Verified')
                    ->count(),
            ];

            $impact['economic_value'] = $impact['total_volunteer_hours'] * 50000;

            $impactByCategory = DB::table('volunteer_activities')
                ->join('volunteer_opportunities', 'volunteer_activities.opportunity_id', '=', 'volunteer_opportunities.opportunity_id')
                ->join('categories', 'volunteer_opportunities.category_id', '=', 'categories.category_id')
                ->select(
                    'categories.category_name',
                    DB::raw('SUM(volunteer_activities.hours_worked) as total_hours'),
                    DB::raw('COUNT(DISTINCT volunteer_activities.volunteer_id) as volunteer_count')
                )
                ->whereBetween('volunteer_activities.activity_date', [$startDate, $endDate])
                ->where('volunteer_activities.status', 'Verified')
                ->groupBy('categories.category_id', 'categories.category_name')
                ->orderBy('total_hours', 'desc')
                ->limit(10)
                ->get();

            $geographicDistribution = DB::table('volunteer_activities')
                ->join('users', 'volunteer_activities.volunteer_id', '=', 'users.user_id')
                ->select(
                    'users.city',
                    DB::raw('SUM(volunteer_activities.hours_worked) as total_hours'),
                    DB::raw('COUNT(DISTINCT volunteer_activities.volunteer_id) as volunteer_count')
                )
                ->whereBetween('volunteer_activities.activity_date', [$startDate, $endDate])
                ->where('volunteer_activities.status', 'Verified')
                ->whereNotNull('users.city')
                ->groupBy('users.city')
                ->orderBy('total_hours', 'desc')
                ->limit(10)
                ->get();

            return [
                'impact' => $impact,
                'impactByCategory' => $impactByCategory,
                'geographicDistribution' => $geographicDistribution,
            ];
        });

        return view('admin.analytics.impact', array_merge($data, [
            'startDate' => $startDate,
            'endDate' => $endDate
        ]));
    }


    public function reports()
    {
        return view('admin.analytics.reports');
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

    // Thêm vào AnalyticsController.php

    /**
     * Custom report generation
     */
    public function customReport(Request $request)
    {
        $validated = $request->validate([
            'report_type' => 'required|in:users,opportunities,applications,activities,organizations',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'format' => 'required|in:csv,excel',
        ]);

        $reportType = $validated['report_type'];
        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];
        $format = $validated['format'];

        // Generate report data
        $data = $this->generateReportData($reportType, $startDate, $endDate);

        // Export based on format
        if ($format === 'excel') {
            return $this->exportToExcel($data, $reportType);
        } else {
            return $this->exportToCSV($data, $reportType);
        }
    }

    /**
     * Generate report data
     */
    private function generateReportData($type, $startDate, $endDate)
    {
        switch ($type) {
            case 'users':
                return User::whereBetween('created_at', [$startDate, $endDate])
                    ->select('user_id', 'first_name', 'last_name', 'email', 'user_type', 'created_at')
                    ->get();

            case 'opportunities':
                return VolunteerOpportunity::whereBetween('created_at', [$startDate, $endDate])
                    ->with('organization:org_id,organization_name')
                    ->get();

            case 'applications':
                return Application::whereBetween('applied_date', [$startDate, $endDate])
                    ->with('volunteer:user_id,first_name,last_name', 'opportunity:opportunity_id,title')
                    ->get();

            case 'activities':
                return VolunteerActivity::whereBetween('activity_date', [$startDate, $endDate])
                    ->where('status', 'Verified')
                    ->with('volunteer:user_id,first_name,last_name')
                    ->get();

            case 'organizations':
                return Organization::whereBetween('created_at', [$startDate, $endDate])
                    ->with('user:user_id,email')
                    ->get();

            default:
                return [];
        }
    }

    /**
     * Export to CSV
     */
    private function exportToCSV($data, $type)
    {
        $filename = "{$type}_report_" . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($data, $type) {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Headers based on type
            if ($data->isNotEmpty()) {
                fputcsv($file, array_keys($data->first()->toArray()));

                foreach ($data as $row) {
                    fputcsv($file, $row->toArray());
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export to Excel with formatting
     */
    private function exportToExcel($data, $type)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $titles = [
            'users' => 'Users Report',
            'opportunities' => 'Opportunities Report',
            'applications' => 'Applications Report',
            'activities' => 'Volunteer Activities Report',
            'organizations' => 'Organizations Report',
        ];

        $sheet->setTitle(ucfirst($type));

        // Define headers based on type
        $headers = $this->getExcelHeaders($type);

        // Write headers
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }

        // Style headers
        $lastCol = chr(ord('A') + count($headers) - 1);
        $headerRange = 'A1:' . $lastCol . '1';

        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5'], // Indigo
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Auto-size columns
        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Write data
        $row = 2;
        foreach ($data as $item) {
            $rowData = $this->getExcelRowData($item, $type);
            $col = 'A';

            foreach ($rowData as $value) {
                $sheet->setCellValue($col . $row, $value);
                $col++;
            }

            // Alternate row colors
            if ($row % 2 == 0) {
                $sheet->getStyle('A' . $row . ':' . $lastCol . $row)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F3F4F6'],
                    ],
                ]);
            }

            $row++;
        }

        // Add borders to all data
        $dataRange = 'A1:' . $lastCol . ($row - 1);
        $sheet->getStyle($dataRange)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
        ]);

        // Set row height
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Output
        $filename = "{$type}_report_" . date('Y-m-d_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Get Excel headers based on report type
     */
    private function getExcelHeaders($type)
    {
        switch ($type) {
            case 'users':
                return ['ID', 'First Name', 'Last Name', 'Email', 'Phone', 'User Type', 'City', 'Status', 'Created At'];

            case 'opportunities':
                return ['ID', 'Title', 'Organization', 'Category', 'Status', 'Location', 'Start Date', 'Volunteers Needed', 'Applications', 'Created At'];

            case 'applications':
                return ['ID', 'Volunteer Name', 'Opportunity', 'Status', 'Applied Date', 'Reviewed Date'];

            case 'activities':
                return ['ID', 'Volunteer Name', 'Opportunity', 'Date', 'Hours', 'Status', 'Verified By', 'Verified Date'];

            case 'organizations':
                return ['ID', 'Name', 'Type', 'Email', 'Phone', 'Verification Status', 'Volunteers', 'Opportunities', 'Rating', 'Created At'];

            default:
                return [];
        }
    }

    /**
     * Get Excel row data based on report type
     */
    private function getExcelRowData($item, $type)
    {
        switch ($type) {
            case 'users':
                return [
                    $item->user_id,
                    $item->first_name,
                    $item->last_name,
                    $item->email,
                    $item->phone ?? 'N/A',
                    $item->user_type,
                    $item->city ?? 'N/A',
                    $item->is_active ? 'Active' : 'Inactive',
                    $item->created_at->format('Y-m-d H:i'),
                ];

            case 'opportunities':
                return [
                    $item->opportunity_id,
                    $item->title,
                    $item->organization->organization_name ?? 'N/A',
                    $item->category->category_name ?? 'N/A',
                    $item->status,
                    $item->location,
                    $item->start_date ? $item->start_date->format('Y-m-d') : 'N/A',
                    $item->volunteers_needed,
                    $item->application_count,
                    $item->created_at->format('Y-m-d H:i'),
                ];

            case 'applications':
                return [
                    $item->application_id,
                    ($item->volunteer->first_name ?? '') . ' ' . ($item->volunteer->last_name ?? ''),
                    $item->opportunity->title ?? 'N/A',
                    $item->status,
                    $item->applied_date->format('Y-m-d H:i'),
                    $item->reviewed_date ? $item->reviewed_date->format('Y-m-d H:i') : 'N/A',
                ];

            case 'activities':
                return [
                    $item->activity_id,
                    ($item->volunteer->first_name ?? '') . ' ' . ($item->volunteer->last_name ?? ''),
                    $item->opportunity->title ?? 'N/A',
                    $item->activity_date->format('Y-m-d'),
                    $item->hours_worked,
                    $item->status,
                    $item->verifiedBy->first_name ?? 'N/A',
                    $item->verified_date ? $item->verified_date->format('Y-m-d H:i') : 'N/A',
                ];

            case 'organizations':
                return [
                    $item->org_id,
                    $item->organization_name,
                    $item->organization_type,
                    $item->user->email ?? 'N/A',
                    $item->user->phone ?? 'N/A',
                    $item->verification_status,
                    $item->volunteer_count,
                    $item->total_opportunities,
                    number_format($item->rating, 2),
                    $item->created_at->format('Y-m-d H:i'),
                ];

            default:
                return [];
        }
    }

}
