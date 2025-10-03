<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\VolunteerOpportunity;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ApplicationController extends Controller
{
    /**
     * Show application form
     */
    public function create($opportunityId)
    {
        $opportunity = VolunteerOpportunity::with('organization.user')
            ->findOrFail($opportunityId);

        // Check if user is volunteer
        if (!Auth::user()->isVolunteer()) {
            return redirect()->back()
                ->with('error', 'Only volunteers can apply for opportunities.');
        }

        // Check if opportunity is still active
        if ($opportunity->status !== 'Active') {
            return redirect()->back()
                ->with('error', 'This opportunity is no longer accepting applications.');
        }

        // Check if application deadline passed
        if ($opportunity->application_deadline < now()) {
            return redirect()->back()
                ->with('error', 'Application deadline has passed.');
        }

        // Check if already applied
        $hasApplied = $opportunity->applications()
            ->where('volunteer_id', Auth::id())
            ->exists();

        if ($hasApplied) {
            return redirect()->back()
                ->with('error', 'You have already applied for this opportunity.');
        }

        // Check maximum concurrent applications (3)
        $activeApplications = Auth::user()->applications()
            ->whereIn('status', ['Pending', 'Under Review'])
            ->count();

        if ($activeApplications >= 3) {
            return redirect()->back()
                ->with('error', 'You can only have 3 active applications at a time.');
        }

        return view('applications.create', compact('opportunity'));
    }

    /**
     * Store application
     */
    public function store(Request $request, $opportunityId)
    {
        $opportunity = VolunteerOpportunity::findOrFail($opportunityId);

        // Validation
        $validator = Validator::make($request->all(), [
            'motivation_letter' => 'required|string|min:100|max:2000',
            'relevant_experience' => 'nullable|string|max:1000',
            'availability_note' => 'nullable|string|max:500',
        ], [
            'motivation_letter.required' => 'Motivation letter is required',
            'motivation_letter.min' => 'Motivation letter must be at least 100 characters',
            'motivation_letter.max' => 'Motivation letter must not exceed 2000 characters',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check if already applied
        $hasApplied = $opportunity->applications()
            ->where('volunteer_id', Auth::id())
            ->exists();

        if ($hasApplied) {
            return redirect()->back()
                ->with('error', 'You have already applied for this opportunity.');
        }

        try {
            DB::beginTransaction();

            // Create application
            $application = Application::create([
                'opportunity_id' => $opportunityId,
                'volunteer_id' => Auth::id(),
                'motivation_letter' => $request->motivation_letter,
                'relevant_experience' => $request->relevant_experience,
                'availability_note' => $request->availability_note,
                'status' => 'Pending',
                'applied_date' => now(),
            ]);

            // Increment application count
            $opportunity->increment('application_count');

            // Create notification for organization
            Notification::create([
                'user_id' => $opportunity->organization->user_id,
                'notification_type' => 'Application',
                'title' => 'New Application Received',
                'content' => Auth::user()->full_name . ' has applied for your opportunity: ' . $opportunity->title,
                'related_id' => $application->application_id,
                'related_type' => 'application',
                'action_url' => route('applications.show', $application->application_id),
                'priority' => 'medium',
            ]);

            DB::commit();

            return redirect()->route('applications.show', $application->application_id)
                ->with('success', 'Application submitted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Failed to submit application. Please try again.')
                ->withInput();
        }
    }

    /**
     * Show application details
     */
    public function show($id)
    {
        $application = Application::with(['opportunity.organization.user', 'volunteer'])
            ->findOrFail($id);

        // Check authorization
        $user = Auth::user();
        if ($application->volunteer_id !== $user->user_id && 
            $application->opportunity->organization->user_id !== $user->user_id) {
            abort(403, 'Unauthorized action.');
        }

        return view('applications.show', compact('application'));
    }

    /**
     * Volunteer's applications list
     */
    public function myApplications(Request $request)
    {
        if (!Auth::user()->isVolunteer()) {
            abort(403, 'Unauthorized action.');
        }

        $status = $request->get('status');

        $query = Auth::user()->applications()
            ->with(['opportunity.organization.user', 'opportunity.category'])
            ->latest();

        if ($status && in_array($status, ['Pending', 'Under Review', 'Accepted', 'Rejected', 'Withdrawn'])) {
            $query->where('status', $status);
        }

        $applications = $query->paginate(10);

        return view('applications.my-applications', compact('applications'));
    }

    /**
     * Organization's received applications
     */
    public function receivedApplications(Request $request, $opportunityId = null)
    {
        if (!Auth::user()->isOrganization()) {
            abort(403, 'Unauthorized action.');
        }

        $organization = Auth::user()->organization;

        $query = Application::with(['volunteer.volunteerProfile', 'opportunity'])
            ->whereHas('opportunity', function($q) use ($organization) {
                $q->where('org_id', $organization->org_id);
            });

        // Filter by opportunity
        if ($opportunityId) {
            $query->where('opportunity_id', $opportunityId);
        }

        // Filter by status
        $status = $request->get('status');
        if ($status && in_array($status, ['Pending', 'Under Review', 'Accepted', 'Rejected'])) {
            $query->where('status', $status);
        }

        // Sort by date
        $query->latest('applied_date');

        $applications = $query->paginate(15);

        // Get opportunities for filter dropdown
        $opportunities = $organization->opportunities()
            ->select('opportunity_id', 'title')
            ->get();

        return view('applications.received', compact('applications', 'opportunities', 'opportunityId'));
    }

    /**
     * Update application status (Organization only)
     */
    public function updateStatus(Request $request, $id)
    {
        $application = Application::with('opportunity.organization')
            ->findOrFail($id);

        // Check authorization
        if ($application->opportunity->organization->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:Under Review,Accepted,Rejected',
            'organization_notes' => 'nullable|string|max:1000',
            'interview_scheduled' => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        try {
            DB::beginTransaction();

            // Update application
            $application->update([
                'status' => $request->status,
                'organization_notes' => $request->organization_notes,
                'interview_scheduled' => $request->interview_scheduled,
                'reviewed_date' => now(),
            ]);

            // Update volunteers_registered if accepted
            if ($request->status === 'Accepted') {
                $application->opportunity->increment('volunteers_registered');
            }

            // Create notification for volunteer
            $notificationContent = match($request->status) {
                'Under Review' => 'Your application is now under review',
                'Accepted' => 'Congratulations! Your application has been accepted',
                'Rejected' => 'Your application has been reviewed',
                default => 'Your application status has been updated'
            };

            Notification::create([
                'user_id' => $application->volunteer_id,
                'notification_type' => 'Application',
                'title' => 'Application Status Updated',
                'content' => $notificationContent . ' for: ' . $application->opportunity->title,
                'related_id' => $application->application_id,
                'related_type' => 'application',
                'action_url' => route('applications.show', $application->application_id),
                'priority' => $request->status === 'Accepted' ? 'high' : 'medium',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Application status updated successfully',
                'status' => $request->status
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json(['error' => 'Failed to update status'], 500);
        }
    }

    /**
     * Withdraw application (Volunteer only)
     */
    public function withdraw($id)
    {
        $application = Application::findOrFail($id);

        // Check authorization
        if ($application->volunteer_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Can only withdraw if status is Pending or Under Review
        if (!in_array($application->status, ['Pending', 'Under Review'])) {
            return redirect()->back()
                ->with('error', 'You can only withdraw pending or under review applications.');
        }

        try {
            DB::beginTransaction();

            $application->update([
                'status' => 'Withdrawn',
                'reviewed_date' => now(),
            ]);

            // Decrement application count
            $application->opportunity->decrement('application_count');

            // Create notification for organization
            Notification::create([
                'user_id' => $application->opportunity->organization->user_id,
                'notification_type' => 'Application',
                'title' => 'Application Withdrawn',
                'content' => Auth::user()->full_name . ' has withdrawn their application for: ' . $application->opportunity->title,
                'related_id' => $application->application_id,
                'related_type' => 'application',
                'priority' => 'low',
            ]);

            DB::commit();

            return redirect()->route('applications.my-applications')
                ->with('success', 'Application withdrawn successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Failed to withdraw application. Please try again.');
        }
    }

    /**
     * Schedule interview
     */
    public function scheduleInterview(Request $request, $id)
    {
        $application = Application::with('opportunity.organization')
            ->findOrFail($id);

        // Check authorization
        if ($application->opportunity->organization->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $validator = Validator::make($request->all(), [
            'interview_scheduled' => 'required|date|after:now',
            'interview_notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        try {
            DB::beginTransaction();

            $application->update([
                'status' => 'Under Review',
                'interview_scheduled' => $request->interview_scheduled,
                'organization_notes' => $request->interview_notes,
            ]);

            // Create notification for volunteer
            Notification::create([
                'user_id' => $application->volunteer_id,
                'notification_type' => 'Application',
                'title' => 'Interview Scheduled',
                'content' => 'An interview has been scheduled for your application: ' . $application->opportunity->title . ' on ' . date('M d, Y h:i A', strtotime($request->interview_scheduled)),
                'related_id' => $application->application_id,
                'related_type' => 'application',
                'action_url' => route('applications.show', $application->application_id),
                'priority' => 'high',
            ]);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Interview scheduled successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Failed to schedule interview. Please try again.');
        }
    }

    /**
     * Get application statistics
     */
    public function getStatistics()
    {
        $user = Auth::user();
        $stats = [];

        if ($user->isVolunteer()) {
            $stats = [
                'total' => $user->applications()->count(),
                'pending' => $user->applications()->where('status', 'Pending')->count(),
                'under_review' => $user->applications()->where('status', 'Under Review')->count(),
                'accepted' => $user->applications()->where('status', 'Accepted')->count(),
                'rejected' => $user->applications()->where('status', 'Rejected')->count(),
                'withdrawn' => $user->applications()->where('status', 'Withdrawn')->count(),
            ];
        } elseif ($user->isOrganization()) {
            $organization = $user->organization;
            $stats = [
                'total' => Application::whereHas('opportunity', function($q) use ($organization) {
                    $q->where('org_id', $organization->org_id);
                })->count(),
                'pending' => Application::whereHas('opportunity', function($q) use ($organization) {
                    $q->where('org_id', $organization->org_id);
                })->where('status', 'Pending')->count(),
                'under_review' => Application::whereHas('opportunity', function($q) use ($organization) {
                    $q->where('org_id', $organization->org_id);
                })->where('status', 'Under Review')->count(),
                'accepted' => Application::whereHas('opportunity', function($q) use ($organization) {
                    $q->where('org_id', $organization->org_id);
                })->where('status', 'Accepted')->count(),
                'need_response' => Application::whereHas('opportunity', function($q) use ($organization) {
                    $q->where('org_id', $organization->org_id);
                })->where('status', 'Pending')
                  ->where('applied_date', '<', now()->subDays(5))
                  ->count(),
            ];
        }

        return response()->json($stats);
    }

    /**
     * Bulk update application status (Organization only)
     */
    public function bulkUpdateStatus(Request $request)
    {
        if (!Auth::user()->isOrganization()) {
            abort(403, 'Unauthorized action.');
        }

        $validator = Validator::make($request->all(), [
            'application_ids' => 'required|array',
            'application_ids.*' => 'exists:applications,application_id',
            'status' => 'required|in:Under Review,Accepted,Rejected',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        try {
            DB::beginTransaction();

            $organization = Auth::user()->organization;

            // Get applications that belong to this organization
            $applications = Application::whereIn('application_id', $request->application_ids)
                ->whereHas('opportunity', function($q) use ($organization) {
                    $q->where('org_id', $organization->org_id);
                })
                ->get();

            foreach ($applications as $application) {
                $application->update([
                    'status' => $request->status,
                    'reviewed_date' => now(),
                ]);

                // Create notification
                Notification::create([
                    'user_id' => $application->volunteer_id,
                    'notification_type' => 'Application',
                    'title' => 'Application Status Updated',
                    'content' => 'Your application status has been updated to: ' . $request->status,
                    'related_id' => $application->application_id,
                    'related_type' => 'application',
                    'action_url' => route('applications.show', $application->application_id),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($applications) . ' application(s) updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json(['error' => 'Failed to update applications'], 500);
        }
    }

    /**
     * Export applications to CSV (Organization only)
     */
    public function export($opportunityId = null)
    {
        if (!Auth::user()->isOrganization()) {
            abort(403, 'Unauthorized action.');
        }

        $organization = Auth::user()->organization;

        $query = Application::with(['volunteer', 'opportunity'])
            ->whereHas('opportunity', function($q) use ($organization) {
                $q->where('org_id', $organization->org_id);
            });

        if ($opportunityId) {
            $query->where('opportunity_id', $opportunityId);
        }

        $applications = $query->get();

        $filename = 'applications_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($applications) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, ['ID', 'Opportunity', 'Volunteer Name', 'Email', 'Phone', 'Status', 'Applied Date', 'Reviewed Date']);
            
            foreach ($applications as $app) {
                fputcsv($file, [
                    $app->application_id,
                    $app->opportunity->title,
                    $app->volunteer->full_name,
                    $app->volunteer->email,
                    $app->volunteer->phone,
                    $app->status,
                    $app->applied_date,
                    $app->reviewed_date,
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}