<?php
namespace App\Http\Controllers;

use App\Models\VolunteerActivity;
use Illuminate\Http\Request;
use App\Models\VolunteerOpportunity;


class ActivityController extends Controller
{
    // Log volunteer hours
    public function store(Request $request)
    {
        $validated = $request->validate([
            'opportunity_id' => 'required|exists:volunteer_opportunities,opportunity_id',
            'activity_date' => 'required|date|before_or_equal:today|after_or_equal:' . now()->subDays(7)->toDateString(),
            'hours_worked' => 'required|numeric|min:0.5|max:12',
            'activity_description' => 'nullable|string',
        ]);
        
        // Get organization from opportunity
        $opportunity = VolunteerOpportunity::findOrFail($validated['opportunity_id']);
        
        $activity = VolunteerActivity::create([
            'volunteer_id' => auth()->id(),
            'org_id' => $opportunity->org_id,
            ...$validated,
        ]);
        
        return response()->json($activity, 201);
    }
    
    // Organization verifies hours
    public function verify($activityId)
    {
        $activity = VolunteerActivity::with('volunteer.volunteerProfile')
            ->findOrFail($activityId);
        
        // Authorize: only organization can verify
        if ($activity->org_id !== auth()->user()->organization->org_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $activity->verify(auth()->id());
        
        // Add hours to volunteer profile
        $activity->volunteer->volunteerProfile->addHours($activity->hours_worked);
        
        return response()->json($activity);
    }
    
    // Get volunteer impact report
    public function report($volunteerId)
    {
        $activities = VolunteerActivity::where('volunteer_id', $volunteerId)
            ->verified()
            ->with(['opportunity', 'organization'])
            ->get();
        
        $totalHours = $activities->sum('hours_worked');
        $opportunitiesCount = $activities->pluck('opportunity_id')->unique()->count();
        $organizationsCount = $activities->pluck('org_id')->unique()->count();
        
        return response()->json([
            'total_hours' => $totalHours,
            'opportunities_participated' => $opportunitiesCount,
            'organizations_worked_with' => $organizationsCount,
            'activities' => $activities,
        ]);
    }
}
