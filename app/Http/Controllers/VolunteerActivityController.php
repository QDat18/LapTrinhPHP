<?php

namespace App\Http\Controllers;

use App\Models\VolunteerActivity;
use App\Models\VolunteerOpportunity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class VolunteerActivityController extends Controller
{
    use AuthorizesRequests;

    // Hiển thị danh sách activities
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = VolunteerActivity::with(['volunteer', 'opportunity', 'organization', 'verifier']);
        
        // Filter theo user role
        if ($user->user_type === 'Volunteer') {
            $query->where('volunteer_id', $user->user_id);
        } elseif ($user->user_type === 'Organization') {
            $query->where('org_id', $user->organization->org_id);
        }
        
        // Filter theo status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }
        
        // Filter theo date range
        if ($request->has('start_date') && $request->start_date != '') {
            $query->where('activity_date', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date != '') {
            $query->where('activity_date', '<=', $request->end_date);
        }
        
        $activities = $query->orderBy('activity_date', 'desc')->paginate(20);
        
        // Tính tổng hours
        $totalHours = $query->where('status', 'Verified')->sum('hours_worked');
        $pendingHours = $query->where('status', 'Pending')->sum('hours_worked');
        
        return view('volunteer-activities.index', compact('activities', 'totalHours', 'pendingHours'));
    }

    // Hiển thị form log giờ tình nguyện
    public function create()
    {
        $user = Auth::user();
        
        // Lấy danh sách opportunities mà volunteer đã được accept
        $opportunities = DB::table('applications')
            ->join('volunteer_opportunities', 'applications.opportunity_id', '=', 'volunteer_opportunities.opportunity_id')
            ->join('organizations', 'volunteer_opportunities.org_id', '=', 'organizations.org_id')
            ->where('applications.volunteer_id', $user->user_id)
            ->where('applications.status', 'Accepted')
            ->select(
                'volunteer_opportunities.opportunity_id',
                'volunteer_opportunities.title',
                'organizations.organization_name',
                'organizations.org_id'
            )
            ->get();
        
        return view('volunteer-activities.create', compact('opportunities'));
    }

    // Lưu activity (log giờ tình nguyện)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'opportunity_id' => 'required|exists:volunteer_opportunities,opportunity_id',
            'activity_date' => 'required|date|before_or_equal:today|after_or_equal:' . now()->subDays(7)->format('Y-m-d'),
            'hours_worked' => 'required|numeric|min:0.5|max:12',
            'activity_description' => 'required|string|max:1000'
        ], [
            'activity_date.after_or_equal' => 'Chỉ có thể log giờ trong vòng 7 ngày gần đây',
            'hours_worked.max' => 'Số giờ tối đa là 12 giờ/ngày',
            'hours_worked.min' => 'Số giờ tối thiểu là 0.5 giờ'
        ]);
        
        $user = Auth::user();
        
        // Lấy org_id từ opportunity
        $opportunity = VolunteerOpportunity::findOrFail($validated['opportunity_id']);
        
        // Kiểm tra xem volunteer có được accept cho opportunity này không
        $application = DB::table('applications')
            ->where('opportunity_id', $validated['opportunity_id'])
            ->where('volunteer_id', $user->user_id)
            ->where('status', 'Accepted')
            ->first();
            
        if (!$application) {
            return back()->with('error', 'Bạn chưa được chấp nhận cho hoạt động này!');
        }
        
        // Kiểm tra duplicate log trong cùng ngày
        $existingActivity = VolunteerActivity::where('volunteer_id', $user->user_id)
            ->where('opportunity_id', $validated['opportunity_id'])
            ->where('activity_date', $validated['activity_date'])
            ->first();
            
        if ($existingActivity) {
            return back()->with('error', 'Bạn đã log giờ cho hoạt động này trong ngày này rồi!');
        }
        
        // Tạo activity
        $activity = VolunteerActivity::create([
            'volunteer_id' => $user->user_id,
            'opportunity_id' => $validated['opportunity_id'],
            'org_id' => $opportunity->org_id,
            'activity_date' => $validated['activity_date'],
            'hours_worked' => $validated['hours_worked'],
            'activity_description' => $validated['activity_description'],
            'status' => 'Pending'
        ]);
        
        // Gửi notification cho organization
        $this->sendVerificationNotification($opportunity->org_id, $activity);
        
        return redirect()->route('volunteer-activities.index')
            ->with('success', 'Đã log giờ tình nguyện thành công! Chờ tổ chức xác nhận.');
    }

    // Hiển thị chi tiết activity
    public function show($id)
    {
        $activity = VolunteerActivity::with(['volunteer', 'opportunity', 'organization', 'verifier'])
            ->findOrFail($id);
        
        // Kiểm tra quyền xem
        $user = Auth::user();
        if ($user->user_type === 'Volunteer' && $activity->volunteer_id != $user->user_id) {
            abort(403, 'Bạn không có quyền xem activity này');
        }
        if ($user->user_type === 'Organization' && $activity->org_id != $user->organization->org_id) {
            abort(403, 'Bạn không có quyền xem activity này');
        }
        
        return view('volunteer-activities.show', compact('activity'));
    }

    // Verify giờ tình nguyện (Organization only)
    public function verify(Request $request, $id)
    {
        $this->authorize('organization');
        
        $activity = VolunteerActivity::findOrFail($id);
        $user = Auth::user();
        
        // Kiểm tra quyền verify
        if ($activity->org_id != $user->organization->org_id) {
            abort(403, 'Bạn không có quyền verify activity này');
        }
        
        if ($activity->status !== 'Pending') {
            return back()->with('error', 'Activity này đã được xử lý rồi!');
        }
        
        $validated = $request->validate([
            'action' => 'required|in:verify,dispute',
            'impact_notes' => 'nullable|string|max:500'
        ]);
        
        DB::beginTransaction();
        try {
            if ($validated['action'] === 'verify') {
                // Verify activity
                $activity->update([
                    'status' => 'Verified',
                    'verified_by' => $user->user_id,
                    'verified_date' => now(),
                    'impact_notes' => $validated['impact_notes'] ?? null
                ]);
                
                // Cập nhật total hours của volunteer
                $volunteerProfile = DB::table('volunteer_profiles')
                    ->where('user_id', $activity->volunteer_id)
                    ->first();
                    
                if ($volunteerProfile) {
                    DB::table('volunteer_profiles')
                        ->where('user_id', $activity->volunteer_id)
                        ->update([
                            'total_volunteer_hours' => $volunteerProfile->total_volunteer_hours + $activity->hours_worked
                        ]);
                }
                
                // Gửi notification cho volunteer
                $this->sendVerifiedNotification($activity);
                
                $message = 'Đã xác nhận giờ tình nguyện thành công!';
                
            } else {
                // Dispute activity
                $activity->update([
                    'status' => 'Disputed',
                    'verified_by' => $user->user_id,
                    'verified_date' => now(),
                    'impact_notes' => $validated['impact_notes'] ?? 'Thông tin không chính xác'
                ]);
                
                // Gửi notification cho volunteer và admin
                $this->sendDisputeNotification($activity);
                
                $message = 'Đã đánh dấu activity là tranh chấp. Admin sẽ xem xét.';
            }
            
            DB::commit();
            return back()->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    // Bulk verify activities
    public function bulkVerify(Request $request)
    {
        $this->authorize('organization');
        
        $validated = $request->validate([
            'activity_ids' => 'required|array',
            'activity_ids.*' => 'exists:volunteer_activities,activity_id',
            'action' => 'required|in:verify,dispute'
        ]);
        
        $user = Auth::user();
        $orgId = $user->organization->org_id;
        
        DB::beginTransaction();
        try {
            $activities = VolunteerActivity::whereIn('activity_id', $validated['activity_ids'])
                ->where('org_id', $orgId)
                ->where('status', 'Pending')
                ->get();
            
            foreach ($activities as $activity) {
                if ($validated['action'] === 'verify') {
                    $activity->update([
                        'status' => 'Verified',
                        'verified_by' => $user->user_id,
                        'verified_date' => now()
                    ]);
                    
                    // Cập nhật total hours
                    DB::table('volunteer_profiles')
                        ->where('user_id', $activity->volunteer_id)
                        ->increment('total_volunteer_hours', $activity->hours_worked);
                    
                } else {
                    $activity->update([
                        'status' => 'Disputed',
                        'verified_by' => $user->user_id,
                        'verified_date' => now()
                    ]);
                }
            }
            
            DB::commit();
            
            $count = $activities->count();
            $message = $validated['action'] === 'verify' 
                ? "Đã verify $count activities thành công!" 
                : "Đã đánh dấu $count activities là tranh chấp!";
                
            return back()->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    // Tranh chấp activity (Volunteer)
    public function dispute(Request $request, $id)
    {
        $activity = VolunteerActivity::findOrFail($id);
        $user = Auth::user();
        
        // Kiểm tra quyền
        if ($activity->volunteer_id != $user->user_id) {
            abort(403, 'Bạn không có quyền tranh chấp activity này');
        }
        
        if ($activity->status !== 'Verified') {
            return back()->with('error', 'Chỉ có thể tranh chấp activity đã được verify!');
        }
        
        $validated = $request->validate([
            'dispute_reason' => 'required|string|max:500'
        ]);
        
        $activity->update([
            'status' => 'Disputed',
            'impact_notes' => $validated['dispute_reason']
        ]);
        
        // Gửi notification cho admin
        $this->sendDisputeNotification($activity);
        
        return back()->with('success', 'Đã gửi tranh chấp. Admin sẽ xem xét.');
    }

    // Export activities to CSV
    public function export(Request $request)
    {
        $user = Auth::user();
        
        $query = VolunteerActivity::with(['volunteer', 'opportunity', 'organization']);
        
        if ($user->user_type === 'Volunteer') {
            $query->where('volunteer_id', $user->user_id);
        } elseif ($user->user_type === 'Organization') {
            $query->where('org_id', $user->organization->org_id);
        }
        
        // Apply filters
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }
        if ($request->has('start_date')) {
            $query->where('activity_date', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->where('activity_date', '<=', $request->end_date);
        }
        
        $activities = $query->get();
        
        $filename = 'volunteer_activities_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($activities) {
            $file = fopen('php://output', 'w');
            
            // BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'Activity ID',
                'Volunteer',
                'Opportunity',
                'Organization',
                'Date',
                'Hours',
                'Status',
                'Description'
            ]);
            
            foreach ($activities as $activity) {
                fputcsv($file, [
                    $activity->activity_id,
                    $activity->volunteer->first_name . ' ' . $activity->volunteer->last_name,
                    $activity->opportunity->title,
                    $activity->organization->organization_name,
                    $activity->activity_date,
                    $activity->hours_worked,
                    $activity->status,
                    $activity->activity_description
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    // Helper: Send verification notification
    private function sendVerificationNotification($orgId, $activity)
    {
        $organization = User::whereHas('organization', function($q) use ($orgId) {
            $q->where('org_id', $orgId);
        })->first();
        
        if ($organization) {
            DB::table('notifications')->insert([
                'user_id' => $organization->user_id,
                'notification_type' => 'System',
                'title' => 'Yêu cầu xác nhận giờ tình nguyện',
                'content' => 'Tình nguyện viên đã log ' . $activity->hours_worked . ' giờ cho hoạt động của bạn',
                'related_id' => $activity->activity_id,
                'related_type' => 'activity',
                'action_url' => route('volunteer-activities.show', $activity->activity_id),
                'created_at' => now()
            ]);
        }
    }

    // Helper: Send verified notification
    private function sendVerifiedNotification($activity)
    {
        DB::table('notifications')->insert([
            'user_id' => $activity->volunteer_id,
            'notification_type' => 'System',
            'title' => 'Giờ tình nguyện đã được xác nhận',
            'content' => $activity->hours_worked . ' giờ của bạn đã được tổ chức xác nhận',
            'related_id' => $activity->activity_id,
            'related_type' => 'activity',
            'action_url' => route('volunteer-activities.show', $activity->activity_id),
            'created_at' => now()
        ]);
    }

    // Helper: Send dispute notification
    private function sendDisputeNotification($activity)
    {
        // Notify admin
        $admins = User::where('user_type', 'Admin')->get();
        foreach ($admins as $admin) {
            DB::table('notifications')->insert([
                'user_id' => $admin->user_id,
                'notification_type' => 'System',
                'title' => 'Activity bị tranh chấp',
                'content' => 'Activity #' . $activity->activity_id . ' cần được xem xét',
                'related_id' => $activity->activity_id,
                'related_type' => 'activity',
                'action_url' => route('admin.activities.show', $activity->activity_id),
                'priority' => 'high',
                'created_at' => now()
            ]);
        }
    }
}