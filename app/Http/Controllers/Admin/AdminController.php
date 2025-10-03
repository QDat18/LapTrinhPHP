<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Organization;
use App\Models\VolunteerOpportunity;
use App\Models\Application;
use App\Models\Category;
use App\Models\VolunteerActivity;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Display admin dashboard
     */
    public function dashboard()
    {
        // Statistics
        $stats = [
            'total_users' => User::count(),
            'new_users_this_month' => User::whereMonth('created_at', now()->month)->count(),
            'total_orgs' => Organization::count(),
            'pending_verifications' => Organization::where('verification_status', 'Pending')->count(),
            'active_opportunities' => VolunteerOpportunity::where('status', 'Active')->count(),
            'upcoming' => VolunteerOpportunity::where('status', 'Active')
                ->where('start_date', '>', now())
                ->count(),
            'total_applications' => Application::count(),
            'pending_applications' => Application::where('status', 'Pending')->count(),
        ];
        
        // Recent Users
        $recentUsers = User::latest()->take(5)->get();
        
        // Pending Organizations
        $pendingOrgs = Organization::where('verification_status', 'Pending')
            ->with('user')
            ->latest()
            ->take(5)
            ->get();
        
        // Chart Data
        $chartData = [
            'userGrowth' => [
                'labels' => collect(range(6, 0))->map(function($days) {
                    return now()->subDays($days)->format('M d');
                })->toArray(),
                'data' => collect(range(6, 0))->map(function($days) {
                    return User::whereDate('created_at', now()->subDays($days))->count();
                })->toArray(),
            ],
            'applicationStatus' => [
                Application::where('status', 'Pending')->count(),
                Application::where('status', 'Accepted')->count(),
                Application::where('status', 'Rejected')->count(),
                Application::where('status', 'Under Review')->count(),
            ],
        ];
        
        // Recent Activities
        $recentActivities = [
            [
                'description' => 'New user registered: John Doe',
                'time' => '5 minutes ago',
                'icon' => 'user-plus',
                'color' => 'blue'
            ],
            [
                'description' => 'Organization verified: Green Earth',
                'time' => '1 hour ago',
                'icon' => 'check-circle',
                'color' => 'green'
            ],
            [
                'description' => 'New opportunity posted',
                'time' => '2 hours ago',
                'icon' => 'clipboard-list',
                'color' => 'purple'
            ],
            [
                'description' => 'Application approved',
                'time' => '3 hours ago',
                'icon' => 'file-alt',
                'color' => 'indigo'
            ],
        ];
        
        return view('admin.dashboard', compact('stats', 'recentUsers', 'pendingOrgs', 'chartData', 'recentActivities'));
    }
    
    /**
     * Display users list
     */
    public function usersIndex(Request $request)
    {
        $query = User::query();
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }
        
        // Filter by user type
        if ($request->filled('user_type')) {
            $query->where('user_type', $request->user_type);
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        
        $users = $query->latest()->paginate(15);
        
        return view('admin.users.index', compact('users'));
    }
    
    /**
     * Show user details
     */
    public function usersShow($id)
    {
        $user = User::with(['volunteerProfile', 'organization', 'applications', 'reviews'])
            ->findOrFail($id);
        
        return response()->json($user);
    }
    
    /**
     * Store new user
     */
    public function usersStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:15|unique:users,phone',
            'user_type' => 'required|in:Volunteer,Organization,Admin',
            'city' => 'required|string',
            'password' => 'required|string|min:8',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }
        
        try {
            DB::beginTransaction();
            
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'user_type' => $request->user_type,
                'city' => $request->city,
                'password' => Hash::make($request->password),
                'is_active' => true,
                'is_verified' => true,
            ]);
            
            // Create profile based on type
            if ($request->user_type === 'Volunteer') {
                \App\Models\VolunteerProfile::create([
                    'user_id' => $user->user_id,
                ]);
            } elseif ($request->user_type === 'Organization') {
                \App\Models\Organization::create([
                    'user_id' => $user->user_id,
                    'organization_name' => $request->first_name . ' ' . $request->last_name,
                    'verification_status' => 'Verified',
                ]);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'User created successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update user
     */
    public function usersUpdate(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|email|unique:users,email,' . $id . ',user_id',
            'phone' => 'required|string|max:15|unique:users,phone,' . $id . ',user_id',
            'user_type' => 'required|in:Volunteer,Organization,Admin',
            'city' => 'required|string',
            'password' => 'nullable|string|min:8',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }
        
        try {
            $data = [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'user_type' => $request->user_type,
                'city' => $request->city,
            ];
            
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }
            
            $user->update($data);
            
            return response()->json([
                'success' => true,
                'message' => 'User updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user'
            ], 500);
        }
    }
    
    /**
     * Update user status
     */
    public function usersUpdateStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $user->update([
            'is_active' => $request->is_active
        ]);
        
        return response()->json([
            'success' => true,
            'message' => $user->is_active ? 'User activated successfully' : 'User deactivated successfully'
        ]);
    }
    
    /**
     * Delete user
     */
    public function usersDestroy($id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Prevent deleting current admin
            if ($user->user_id === auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot delete your own account'
                ], 403);
            }
            
            $user->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user'
            ], 500);
        }
    }
    
    /**
     * Bulk actions on users
     */
    public function usersBulkAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:activate,deactivate,delete',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,user_id',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }
        
        try {
            $userIds = $request->user_ids;
            
            // Prevent bulk action on current admin
            if (in_array(auth()->id(), $userIds)) {
                $userIds = array_diff($userIds, [auth()->id()]);
            }
            
            switch ($request->action) {
                case 'activate':
                    User::whereIn('user_id', $userIds)->update(['is_active' => true]);
                    $message = 'Users activated successfully';
                    break;
                    
                case 'deactivate':
                    User::whereIn('user_id', $userIds)->update(['is_active' => false]);
                    $message = 'Users deactivated successfully';
                    break;
                    
                case 'delete':
                    User::whereIn('user_id', $userIds)->delete();
                    $message = 'Users deleted successfully';
                    break;
            }
            
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bulk action failed'
            ], 500);
        }
    }
    
    /**
     * Display organizations list
     */
    public function organizationsIndex(Request $request)
    {
        $query = Organization::with('user');
        
        // Search
        if ($request->filled('search')) {
            $query->where('organization_name', 'LIKE', "%{$request->search}%");
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('verification_status', $request->status);
        }
        
        // Filter by type
        if ($request->filled('type')) {
            $query->where('organization_type', $request->type);
        }
        
        $organizations = $query->latest()->paginate(15);
        
        // Statistics
        $stats = [
            'total' => Organization::count(),
            'verified' => Organization::where('verification_status', 'Verified')->count(),
            'pending' => Organization::where('verification_status', 'Pending')->count(),
            'rejected' => Organization::where('verification_status', 'Rejected')->count(),
        ];
        
        return view('admin.organizations.index', compact('organizations', 'stats'));
    }
    
    /**
     * Show organization details
     */
    public function organizationsShow($id)
    {
        $org = Organization::with('user')->findOrFail($id);
        return response()->json($org);
    }
    
    /**
     * Approve organization
     */
    public function organizationsApprove($id)
    {
        $org = Organization::findOrFail($id);
        $org->update(['verification_status' => 'Verified']);
        
        // Create notification for organization
        \App\Models\Notification::create([
            'user_id' => $org->user_id,
            'notification_type' => 'System',
            'title' => 'Organization Verified',
            'content' => 'Congratulations! Your organization has been verified and you can now post opportunities.',
            'priority' => 'high',
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Organization approved successfully'
        ]);
    }
    
    /**
     * Reject organization
     */
    public function organizationsReject($id)
    {
        $org = Organization::findOrFail($id);
        $org->update(['verification_status' => 'Rejected']);
        
        // Create notification
        \App\Models\Notification::create([
            'user_id' => $org->user_id,
            'notification_type' => 'System',
            'title' => 'Organization Verification Rejected',
            'content' => 'Your organization verification has been rejected. Please contact support for more information.',
            'priority' => 'high',
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Organization rejected'
        ]);
    }
    
    /**
     * Delete organization
     */
    public function organizationsDestroy($id)
    {
        try {
            $org = Organization::findOrFail($id);
            $org->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Organization deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete organization'
            ], 500);
        }
    }
    
    /**
     * Export organizations
     */
    public function organizationsExport()
    {
        $organizations = Organization::with('user')->get();
        
        $filename = 'organizations_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($organizations) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, ['ID', 'Name', 'Type', 'Status', 'Email', 'Phone', 'Founded', 'Opportunities', 'Rating', 'Created']);
            
            foreach ($organizations as $org) {
                fputcsv($file, [
                    $org->org_id,
                    $org->organization_name,
                    $org->organization_type,
                    $org->verification_status,
                    $org->user->email,
                    $org->user->phone,
                    $org->founded_year,
                    $org->total_opportunities,
                    $org->rating,
                    $org->created_at->format('Y-m-d'),
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Display opportunities list
     */
    public function opportunitiesIndex(Request $request)
    {
        $query = VolunteerOpportunity::with(['organization.user', 'category']);
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('location', 'LIKE', "%{$search}%");
            });
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        
        // Filter by organization
        if ($request->filled('organization')) {
            $query->whereHas('organization', function($q) use ($request) {
                $q->where('organization_name', 'LIKE', "%{$request->organization}%");
            });
        }
        
        $opportunities = $query->latest()->paginate(15);
        
        // Statistics
        $stats = [
            'total' => VolunteerOpportunity::count(),
            'active' => VolunteerOpportunity::where('status', 'Active')->count(),
            'paused' => VolunteerOpportunity::where('status', 'Paused')->count(),
            'completed' => VolunteerOpportunity::where('status', 'Completed')->count(),
            'cancelled' => VolunteerOpportunity::where('status', 'Cancelled')->count(),
        ];
        
        $categories = Category::where('is_active', true)->get();
        
        return view('admin.opportunities.index', compact('opportunities', 'stats', 'categories'));
    }
    
    /**
     * Show opportunity details
     */
    public function opportunitiesShow($id)
    {
        $opportunity = VolunteerOpportunity::with(['organization', 'category'])->findOrFail($id);
        return response()->json($opportunity);
    }
    
    /**
     * Update opportunity status
     */
    public function opportunitiesUpdateStatus(Request $request, $id)
    {
        $opportunity = VolunteerOpportunity::findOrFail($id);
        
        $opportunity->update([
            'status' => $request->status
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully'
        ]);
    }
    
    /**
     * Delete opportunity
     */
    public function opportunitiesDestroy($id)
    {
        try {
            $opportunity = VolunteerOpportunity::findOrFail($id);
            $opportunity->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Opportunity deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete opportunity'
            ], 500);
        }
    }
    
    /**
     * Export opportunities
     */
    public function opportunitiesExport()
    {
        $opportunities = VolunteerOpportunity::with(['organization', 'category'])->get();
        
        $filename = 'opportunities_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($opportunities) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, ['ID', 'Title', 'Organization', 'Category', 'Status', 'Location', 'Start Date', 'Applications', 'Views', 'Created']);
            
            foreach ($opportunities as $opp) {
                fputcsv($file, [
                    $opp->opportunity_id,
                    $opp->title,
                    $opp->organization->organization_name,
                    $opp->category ? $opp->category->category_name : 'N/A',
                    $opp->status,
                    $opp->location,
                    $opp->start_date->format('Y-m-d'),
                    $opp->application_count,
                    $opp->view_count,
                    $opp->created_at->format('Y-m-d'),
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Display applications list
     */
    public function applicationsIndex()
    {
        $applications = Application::with(['volunteer', 'opportunity'])
            ->latest()
            ->paginate(15);
        
        return view('admin.applications.index', compact('applications'));
    }
    
    /**
     * Display categories list
     */
    public function categoriesIndex()
    {
        $categories = Category::withCount('opportunities')
            ->orderBy('display_order')
            ->get();
        
        return view('admin.categories.index', compact('categories'));
    }
    
    /**
     * Store category
     */
    public function categoriesStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_name' => 'required|string|max:50|unique:categories,category_name',
            'description' => 'nullable|string',
            'icon' => 'nullable|string',
            'color' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }
        
        Category::create($request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'Category created successfully'
        ]);
    }
    
    /**
     * Display activities list
     */
    public function activitiesIndex()
    {
        $activities = VolunteerActivity::with(['volunteer', 'opportunity', 'organization'])
            ->latest()
            ->paginate(15);
        
        return view('admin.activities.index', compact('activities'));
    }
    
    /**
     * Display reviews list
     */
    public function reviewsIndex()
    {
        $reviews = Review::with(['reviewer', 'reviewee', 'opportunity'])
            ->latest()
            ->paginate(15);
        
        return view('admin.reviews.index', compact('reviews'));
    }
    
    /**
     * Display analytics
     */
    public function analytics()
    {
        $analytics = [
            'total_volunteer_hours' => VolunteerActivity::where('status', 'Verified')->sum('hours_worked'),
            'average_rating' => Review::where('is_approved', true)->avg('rating'),
            'conversion_rate' => Application::where('status', 'Accepted')->count() / max(Application::count(), 1) * 100,
        ];
        
        return view('admin.analytics', compact('analytics'));
    }
    
    /**
     * Display reports
     */
    public function reportsIndex()
    {
        return view('admin.reports.index');
    }
    
    /**
     * Generate report
     */
    public function reportsGenerate(Request $request)
    {
        $type = $request->type;
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        
        // Generate report based on type
        // Implementation depends on requirements
        
        return response()->json([
            'success' => true,
            'message' => 'Report generated successfully'
        ]);
    }
    
    /**
     * Display settings
     */
    public function settings()
    {
        return view('admin.settings');
    }
    
    /**
     * Update settings
     */
    public function updateSettings(Request $request)
    {
        // Save settings to database or config
        
        return redirect()->back()->with('success', 'Settings updated successfully');
    }
}