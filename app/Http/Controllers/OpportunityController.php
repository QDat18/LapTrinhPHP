<?php
namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\VolunteerOpportunity;

class OpportunityController extends Controller
{
    // Get all active opportunities with relationships
    public function index()
    {
        $opportunities = VolunteerOpportunity::with(['organization.user', 'category'])
            ->active()
            ->notFull()
            ->latest()
            ->paginate(20);
            
        return response()->json($opportunities);
    }
    
    // Get single opportunity with details
    public function show($id)
    {
        $opportunity = VolunteerOpportunity::with([
            'organization.user',
            'category',
            'applications' => function($query) {
                $query->accepted();
            }
        ])->findOrFail($id);
        
        // Increment view count
        $opportunity->incrementViews();
        
        return response()->json($opportunity);
    }
    
    // Search opportunities with filters
    public function search(Request $request)
    {
        $query = VolunteerOpportunity::query()
            ->with(['organization.user', 'category'])
            ->active();
        
        // Filter by category
        if ($request->has('category_id')) {
            $query->byCategory($request->category_id);
        }
        
        // Filter by location
        if ($request->has('city')) {
            $query->byLocation($request->city);
        }
        
        // Filter by skills
        if ($request->has('skills')) {
            $skills = $request->skills;
            $query->where(function($q) use ($skills) {
                foreach ($skills as $skill) {
                    $q->orWhereJsonContains('required_skills', $skill);
                }
            });
        }
        
        // Filter by date range
        if ($request->has('start_date')) {
            $query->where('start_date', '>=', $request->start_date);
        }
        
        // Only not full
        if ($request->boolean('available_only')) {
            $query->notFull();
        }
        
        return response()->json($query->paginate(20));
    }
    
    // Create new opportunity
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'required|string',
            'category_id' => 'nullable|exists:categories,category_id',
            'location' => 'required|string|max:200',
            'start_date' => 'required|date|after:+3 days',
            'volunteers_needed' => 'required|integer|min:1',
            'min_age' => 'required|integer|min:16',
            // ... more validation rules
        ]);
        
        $opportunity = auth()->user()->organization->opportunities()->create($validated);
        
        return response()->json($opportunity, 201);
    }
}