<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VolunteerActivity;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class ActivityVerificationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:Admin']);
    }

    /**
     * Display pending activity verifications
     */
    public function pending(Request $request)
    {
        $query = VolunteerActivity::with(['volunteer', 'opportunity', 'organization'])
            ->where('status', 'Pending');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('volunteer', function($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%");
            });
        }

        // Filter by organization
        if ($request->filled('org_id')) {
            $query->where('org_id', $request->org_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('activity_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('activity_date', '<=', $request->date_to);
        }

        $activities = $query->orderBy('activity_date', 'desc')->paginate(20);

        // Get organizations for filter
        $organizations = \App\Models\Organization::select('org_id', 'organization_name')
            ->orderBy('organization_name')
            ->get();

        return view('admin.activities.pending', compact('activities', 'organizations'));
    }

    /**
     * Show activity details
     */
    public function show($id)
    {
        $activity = VolunteerActivity::with([
            'volunteer.volunteerProfile',
            'opportunity',
            'organization',
            'verifier'
        ])->findOrFail($id);

        return view('admin.activities.show', compact('activity'));
    }

    /**
     * Verify volunteer activity
     */
    public function verify(Request $request, $id)
    {
        $activity = VolunteerActivity::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'verification_notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $activity->verify(auth()->id());
        
        if ($request->filled('verification_notes')) {
            $activity->impact_notes = $request->verification_notes;
            $activity->save();
        }

        // TODO: Send notification to volunteer

        return redirect()->back()->with('success', 'Activity has been verified successfully.');
    }

    /**
     * Dispute volunteer activity
     */
    public function dispute(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'dispute_reason' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $activity = VolunteerActivity::findOrFail($id);
        $activity->dispute($request->dispute_reason);

        // TODO: Send notification to both volunteer and organization

        return redirect()->back()->with('success', 'Activity has been marked as disputed.');
    }

    /**
     * Bulk verify activities
     */
    public function bulkVerify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'activity_ids' => 'required|array',
            'activity_ids.*' => 'exists:volunteer_activities,activity_id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $activities = VolunteerActivity::whereIn('activity_id', $request->activity_ids)
            ->where('status', 'Pending')
            ->get();

        foreach ($activities as $activity) {
            $activity->verify(auth()->id());
        }

        return redirect()->back()->with('success', count($activities) . ' activities have been verified.');
    }
}