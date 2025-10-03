<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\VolunteerOpportunity;
use App\Models\Application;
use App\Models\VolunteerActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->isOrganization()) {
            return $this->organizationDashboard();
        } else {
            return $this->volunteerDashboard();
        }
    }

    public function volunteerDashboard()
    {
        $user = Auth::user();
        $profile = $user->volunteerProfile;

        $stats = [
            'total_hours' => $profile->total_volunteer_hours ?? 0,
            'rating' => $profile->volunteer_rating ?? 0,
            'applications' => $user->applications()->count(),
            'accepted' => $user->applications()->where('status', 'Accepted')->count(),
            'pending' => $user->applications()->where('status', 'Pending')->count(),
            'completed_activities' => $user->volunteerActivities()->where('status', 'Verified')->count(),
        ];

        $recentApplications = $user->applications()
            ->with(['opportunity.organization'])
            ->latest()
            ->take(5)
            ->get();

        $recommendations = VolunteerOpportunity::where('status', 'Active')
            ->where('application_deadline', '>', now())
            ->when($user->city, function ($query) use ($user) {
                $query->where('location', 'LIKE', "%{$user->city}%");
            })
            ->latest()
            ->take(6)
            ->get();

        $upcomingActivities = $user->applications()
            ->where('status', 'Accepted')
            ->whereHas('opportunity', function ($q) {
                $q->where('status', 'Active')
                    ->where('start_date', '>=', now());
            })
            ->with(['opportunity'])
            ->orderBy('opportunity.start_date')
            ->take(5)
            ->get();
        $activityHistory = $user->volunteerActivities()
            ->where('status', 'Verified')
            ->with(['opportunity', 'organization'])
            ->latest()
            ->take(10)
            ->get();

        // Chart data - Hours by month
        $chartData = [
            'labels' => collect(range(5, 0))->map(function ($months) {
                return now()->subMonths($months)->format('M');
            })->toArray(),
            'data' => collect(range(5, 0))->map(function ($months) use ($user) {
                return $user->volunteerActivities()
                    ->whereYear('activity_date', now()->subMonths($months)->year)
                    ->whereMonth('activity_date', now()->subMonths($months)->month)
                    ->where('status', 'Verified')
                    ->sum('hours_worked');
            })->toArray(),
        ];
        return view('volunteer.dashboard', compact('user', 'stats', 'recentApplications', 'recommendations', 'upcomingActivities', 'activityHistory', 'chartData'));
    }
    public function organizationDashboard()
    {
        $user = Auth::user();
        $organization = $user->organization;

        // Statistics
        $stats = [
            'total_opportunities' => $organization->total_opportunities,
            'active_opportunities' => $organization->opportunities()->where('status', 'Active')->count(),
            'volunteer_count' => $organization->volunteer_count,
            'rating' => $organization->rating,
            'pending_applications' => Application::whereHas('opportunity', function ($q) use ($organization) {
                $q->where('org_id', $organization->org_id);
            })->where('status', 'Pending')->count(),
            'total_applications' => Application::whereHas('opportunity', function ($q) use ($organization) {
                $q->where('org_id', $organization->org_id);
            })->count(),
        ];

        // Recent opportunities
        $recentOpportunities = $organization->opportunities()
            ->withCount('applications')
            ->latest()
            ->take(5)
            ->get();

        // Pending applications
        $pendingApplications = Application::whereHas('opportunity', function ($q) use ($organization) {
            $q->where('org_id', $organization->org_id);
        })
            ->where('status', 'Pending')
            ->with(['volunteer', 'opportunity'])
            ->latest()
            ->take(10)
            ->get();

        // Top volunteers
        $topVolunteers = VolunteerActivity::where('org_id', $organization->org_id)
            ->where('status', 'Verified')
            ->select('volunteer_id', DB::raw('SUM(hours_worked) as total_hours'))
            ->groupBy('volunteer_id')
            ->orderByDesc('total_hours')
            ->take(5)
            ->with('volunteer')
            ->get();

        // Upcoming opportunities
        $upcomingOpportunities = $organization->opportunities()
            ->where('status', 'Active')
            ->where('start_date', '>=', now())
            ->orderBy('start_date')
            ->take(5)
            ->get();

        // Chart data - Applications by status
        $chartData = [
            'labels' => ['Pending', 'Accepted', 'Rejected', 'Under Review'],
            'data' => [
                Application::whereHas('opportunity', function ($q) use ($organization) {
                    $q->where('org_id', $organization->org_id);
                })->where('status', 'Pending')->count(),
                Application::whereHas('opportunity', function ($q) use ($organization) {
                    $q->where('org_id', $organization->org_id);
                })->where('status', 'Accepted')->count(),
                Application::whereHas('opportunity', function ($q) use ($organization) {
                    $q->where('org_id', $organization->org_id);
                })->where('status', 'Rejected')->count(),
                Application::whereHas('opportunity', function ($q) use ($organization) {
                    $q->where('org_id', $organization->org_id);
                })->where('status', 'Under Review')->count(),
            ]
        ];

        return view('organization.dashboard', compact('organization', 'stats', 'recentOpportunities', 'pendingApplications', 'topVolunteers', 'upcomingOpportunities', 'chartData'));
    }

    public function statistics()
    {
        $user = Auth::user();

        if ($user->isVolunteer()) {
            $profile = $user->volunteerProfile;
            return response()->json([
                'user_type' => 'volunteer',
                'total_hours' => $profile->total_volunteer_hours ?? 0,
                'rating' => $profile->volunteer_rating ?? 0,
                'applications' => $user->applications()->count(),
                'accepted_applications' => $user->applications()->where('status', 'Accepted')->count(),
                'completed_activities' => $user->volunteerActivities()->where('status', 'Verified')->count(),
            ]);
        } elseif ($user->isOrganization()) {
            $organization = $user->organization;
            return response()->json([
                'user_type' => 'organization',
                'total_opportunities' => $organization->total_opportunities,
                'active_opportunities' => $organization->opportunities()->where('status', 'Active')->count(),
                'volunteer_count' => $organization->volunteer_count,
                'rating' => $organization->rating,
                'pending_applications' => Application::whereHas('opportunity', function ($q) use ($organization) {
                    $q->where('org_id', $organization->org_id);
                })->where('status', 'Pending')->count(),
            ]);
        }
        return response()->json([
            'error' => 'Invalid user type'
        ], 400);
    }

    public function activityFeed()
    {
        $user = Auth::user();
        $activities = [];

        if ($user->isVolunteer()) {
            // Get volunteer activities
            $recentActivities = $user->volunteerActivities()
                ->where('status', 'Verified')
                ->with(['opportunity', 'organization'])
                ->latest()
                ->take(10)
                ->get();

            foreach ($recentActivities as $activity) {
                $activities[] = [
                    'type' => 'activity',
                    'title' => 'Volunteered at ' . $activity->opportunity->title,
                    'description' => $activity->hours_worked . ' hours with ' . $activity->organization->organization_name,
                    'date' => $activity->activity_date,
                    'icon' => 'fa-calendar-check',
                    'color' => 'green'
                ];
            }

            // Get recent applications
            $recentApplications = $user->applications()
                ->with(['opportunity'])
                ->latest()
                ->take(5)
                ->get();

            foreach ($recentApplications as $app) {
                $activities[] = [
                    'type' => 'application',
                    'title' => 'Applied to ' . $app->opportunity->title,
                    'description' => 'Status: ' . $app->status,
                    'date' => $app->applied_date,
                    'icon' => 'fa-file-alt',
                    'color' => $app->status == 'Accepted' ? 'green' : ($app->status == 'Pending' ? 'yellow' : 'red')
                ];
            }
        } elseif ($user->isOrganization()) {
            $organization = $user->organization;

            // Get recent opportunities
            $recentOpportunities = $organization->opportunities()
                ->latest()
                ->take(5)
                ->get();

            foreach ($recentOpportunities as $opp) {
                $activities[] = [
                    'type' => 'opportunity',
                    'title' => 'Posted: ' . $opp->title,
                    'description' => $opp->application_count . ' applications received',
                    'date' => $opp->created_at,
                    'icon' => 'fa-clipboard-list',
                    'color' => 'blue'
                ];
            }

            // Get recent applications
            $recentApplications = Application::whereHas('opportunity', function ($q) use ($organization) {
                $q->where('org_id', $organization->org_id);
            })
                ->with(['volunteer', 'opportunity'])
                ->latest()
                ->take(10)
                ->get();

            foreach ($recentApplications as $app) {
                $activities[] = [
                    'type' => 'application',
                    'title' => 'New application from ' . $app->volunteer->first_name,
                    'description' => 'For: ' . $app->opportunity->title,
                    'date' => $app->applied_date,
                    'icon' => 'fa-user-plus',
                    'color' => 'purple'
                ];
            }
        }

        // Sort by date
        usort($activities, function ($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return response()->json(array_slice($activities, 0, 15));
    }

    public function quickStats()
    {
        $user = Auth::user();

        if ($user->isVolunteer()) {
            $profile = $user->volunteerProfile;

            return response()->json([
                'cards' => [
                    [
                        'title' => 'Total Hours',
                        'value' => $profile->total_volunteer_hours ?? 0,
                        'icon' => 'fa-clock',
                        'color' => 'blue'
                    ],
                    [
                        'title' => 'Rating',
                        'value' => number_format($profile->volunteer_rating ?? 0, 1),
                        'icon' => 'fa-star',
                        'color' => 'yellow'
                    ],
                    [
                        'title' => 'Applications',
                        'value' => $user->applications()->count(),
                        'icon' => 'fa-file-alt',
                        'color' => 'purple'
                    ],
                    [
                        'title' => 'Completed',
                        'value' => $user->volunteerActivities()->where('status', 'Verified')->count(),
                        'icon' => 'fa-check-circle',
                        'color' => 'green'
                    ]
                ]
            ]);
        } elseif ($user->isOrganization()) {
            $organization = $user->organization;

            return response()->json([
                'cards' => [
                    [
                        'title' => 'Opportunities',
                        'value' => $organization->total_opportunities,
                        'icon' => 'fa-clipboard-list',
                        'color' => 'blue'
                    ],
                    [
                        'title' => 'Volunteers',
                        'value' => $organization->volunteer_count,
                        'icon' => 'fa-users',
                        'color' => 'green'
                    ],
                    [
                        'title' => 'Applications',
                        'value' => Application::whereHas('opportunity', function ($q) use ($organization) {
                            $q->where('org_id', $organization->org_id);
                        })->where('status', 'Pending')->count(),
                        'icon' => 'fa-inbox',
                        'color' => 'yellow'
                    ],
                    [
                        'title' => 'Rating',
                        'value' => number_format($organization->rating, 1),
                        'icon' => 'fa-star',
                        'color' => 'purple'
                    ]
                ]
            ]);
        }

        return response()->json(['error' => 'Invalid user type'], 400);
    }
}
