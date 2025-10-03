<?php

namespace App\Http\Controllers;

use App\Models\VolunteerOpportunity;
use App\Models\Organization;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    // Tìm kiếm toàn bộ (unified search)
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $type = $request->get('type', 'all'); // all, opportunities, organizations, volunteers
        
        if (strlen($query) < 2) {
            return back()->with('error', 'Từ khóa tìm kiếm phải có ít nhất 2 ký tự');
        }
        
        $results = [
            'opportunities' => collect(),
            'organizations' => collect(),
            'volunteers' => collect(),
            'total' => 0
        ];
        
        // Search Opportunities
        if ($type === 'all' || $type === 'opportunities') {
            $opportunities = VolunteerOpportunity::with(['organization', 'category'])
                ->where('status', 'Active')
                ->where(function($q) use ($query) {
                    $q->where('title', 'like', "%$query%")
                      ->orWhere('description', 'like', "%$query%")
                      ->orWhere('location', 'like', "%$query%")
                      ->orWhere('required_skills', 'like', "%$query%");
                })
                ->orderBy('created_at', 'desc')
                ->limit($type === 'opportunities' ? 50 : 10)
                ->get();
            
            $results['opportunities'] = $opportunities;
            $results['total'] += $opportunities->count();
        }
        
        // Search Organizations
        if ($type === 'all' || $type === 'organizations') {
            $organizations = Organization::with('user')
                ->where('verification_status', 'Verified')
                ->where(function($q) use ($query) {
                    $q->where('organization_name', 'like', "%$query%")
                      ->orWhere('description', 'like', "%$query%")
                      ->orWhere('mission_statement', 'like', "%$query%");
                })
                ->orderBy('rating', 'desc')
                ->limit($type === 'organizations' ? 50 : 5)
                ->get();
            
            $results['organizations'] = $organizations;
            $results['total'] += $organizations->count();
        }
        
        // Search Volunteers (chỉ admin và organization mới search được)
        if (Auth::check() && in_array(Auth::user()->user_type, ['Admin', 'Organization'])) {
            if ($type === 'all' || $type === 'volunteers') {
                $volunteers = User::with('volunteerProfile')
                    ->where('user_type', 'Volunteer')
                    ->where('is_active', true)
                    ->where('is_verified', true)
                    ->where(function($q) use ($query) {
                        $q->where('first_name', 'like', "%$query%")
                          ->orWhere('last_name', 'like', "%$query%")
                          ->orWhere('email', 'like', "%$query%")
                          ->orWhereHas('volunteerProfile', function($subQ) use ($query) {
                              $subQ->where('skills', 'like', "%$query%")
                                   ->orWhere('interests', 'like', "%$query%")
                                   ->orWhere('bio', 'like', "%$query%");
                          });
                    })
                    ->limit($type === 'volunteers' ? 50 : 5)
                    ->get();
                
                $results['volunteers'] = $volunteers;
                $results['total'] += $volunteers->count();
            }
        }
        
        // Log search query for analytics
        $this->logSearchQuery($query, $type, $results['total']);
        
        return view('search.results', compact('results', 'query', 'type'));
    }

    // Advanced search cho opportunities
    public function advancedSearch(Request $request)
    {
        $query = VolunteerOpportunity::with(['organization', 'category'])
            ->where('status', 'Active');
        
        // Keyword search
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function($q) use ($keyword) {
                $q->where('title', 'like', "%$keyword%")
                  ->orWhere('description', 'like', "%$keyword%");
            });
        }
        
        // Category filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        // Location filter
        if ($request->filled('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }
        
        // Date range
        if ($request->filled('start_date')) {
            $query->where('start_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('start_date', '<=', $request->end_date);
        }
        
        // Time commitment
        if ($request->filled('time_commitment')) {
            $query->where('time_commitment', $request->time_commitment);
        }
        
        // Schedule type
        if ($request->filled('schedule_type')) {
            $query->where('schedule_type', $request->schedule_type);
        }
        
        // Experience needed
        if ($request->filled('experience_needed')) {
            $query->where('experience_needed', $request->experience_needed);
        }
        
        // Required skills
        if ($request->filled('skills')) {
            $skills = explode(',', $request->skills);
            foreach ($skills as $skill) {
                $query->where('required_skills', 'like', '%' . trim($skill) . '%');
            }
        }
        
        // Organization type
        if ($request->filled('organization_type')) {
            $query->whereHas('organization', function($q) use ($request) {
                $q->where('organization_type', $request->organization_type);
            });
        }
        
        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        switch ($sortBy) {
            case 'title':
                $query->orderBy('title', $sortOrder);
                break;
            case 'start_date':
                $query->orderBy('start_date', $sortOrder);
                break;
            case 'view_count':
                $query->orderBy('view_count', $sortOrder);
                break;
            case 'application_count':
                $query->orderBy('application_count', $sortOrder);
                break;
            default:
                $query->orderBy('created_at', $sortOrder);
        }
        
        $opportunities = $query->paginate(12);
        
        // Get filter options
        $categories = Category::where('is_active', true)
            ->orderBy('display_order')
            ->get();
        
        return view('search.advanced', compact('opportunities', 'categories'));
    }

    // Search suggestions (for autocomplete)
    public function suggestions(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }
        
        $suggestions = [];
        
        // Opportunity titles
        $opportunityTitles = VolunteerOpportunity::where('status', 'Active')
            ->where('title', 'like', "$query%")
            ->limit(5)
            ->pluck('title')
            ->map(function($title) {
                return [
                    'text' => $title,
                    'type' => 'opportunity'
                ];
            });
        
        $suggestions = array_merge($suggestions, $opportunityTitles->toArray());
        
        // Organization names
        $organizationNames = Organization::where('verification_status', 'Verified')
            ->where('organization_name', 'like', "$query%")
            ->limit(3)
            ->pluck('organization_name')
            ->map(function($name) {
                return [
                    'text' => $name,
                    'type' => 'organization'
                ];
            });
        
        $suggestions = array_merge($suggestions, $organizationNames->toArray());
        
        // Categories
        $categories = Category::where('is_active', true)
            ->where('category_name', 'like', "$query%")
            ->limit(2)
            ->pluck('category_name')
            ->map(function($name) {
                return [
                    'text' => $name,
                    'type' => 'category'
                ];
            });
        
        $suggestions = array_merge($suggestions, $categories->toArray());
        
        return response()->json($suggestions);
    }

    // Search by location (với geolocation)
    public function searchByLocation(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|integer|min:1|max:100' // km
        ]);
        
        $lat = $validated['latitude'];
        $lng = $validated['longitude'];
        $radius = $validated['radius'] ?? 10; // default 10km
        
        // Haversine formula để tính khoảng cách
        $opportunities = VolunteerOpportunity::select('*')
            ->selectRaw(
                '(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance',
                [$lat, $lng, $lat]
            )
            ->where('status', 'Active')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->having('distance', '<=', $radius)
            ->orderBy('distance', 'asc')
            ->with(['organization', 'category'])
            ->paginate(12);
        
        return view('search.location', compact('opportunities', 'radius'));
    }

    // Search by category
    public function searchByCategory($categoryId)
    {
        $category = Category::findOrFail($categoryId);
        
        $opportunities = VolunteerOpportunity::with(['organization', 'category'])
            ->where('category_id', $categoryId)
            ->where('status', 'Active')
            ->orderBy('created_at', 'desc')
            ->paginate(12);
        
        return view('search.category', compact('category', 'opportunities'));
    }

    // Search organizations
    public function searchOrganizations(Request $request)
    {
        $query = Organization::with('user')
            ->where('verification_status', 'Verified');
        
        // Keyword
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function($q) use ($keyword) {
                $q->where('organization_name', 'like', "%$keyword%")
                  ->orWhere('description', 'like', "%$keyword%");
            });
        }
        
        // Type
        if ($request->filled('type')) {
            $query->where('organization_type', $request->type);
        }
        
        // Location
        if ($request->filled('location')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('city', 'like', '%' . $request->location . '%');
            });
        }
        
        // Sort
        $sortBy = $request->get('sort_by', 'rating');
        $query->orderBy($sortBy, 'desc');
        
        $organizations = $query->paginate(12);
        
        return view('search.organizations', compact('organizations'));
    }

    // Popular searches
    public function popularSearches()
    {
        // Lấy top 10 từ khóa được search nhiều nhất trong 30 ngày qua
        $popularSearches = DB::table('system_analytics')
            ->where('metric_name', 'search_query')
            ->where('record_date', '>=', now()->subDays(30))
            ->select('metadata->query as query', DB::raw('SUM(metric_value) as total'))
            ->groupBy('query')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();
        
        return response()->json($popularSearches);
    }

    // Trending opportunities
    public function trendingOpportunities()
    {
        $opportunities = VolunteerOpportunity::with(['organization', 'category'])
            ->where('status', 'Active')
            ->where('created_at', '>=', now()->subDays(7))
            ->orderBy('view_count', 'desc')
            ->orderBy('application_count', 'desc')
            ->limit(10)
            ->get();
        
        return view('search.trending', compact('opportunities'));
    }

    // Helper: Log search query for analytics
    private function logSearchQuery($query, $type, $resultCount)
    {
        try {
            DB::table('system_analytics')->insert([
                'metric_name' => 'search_query',
                'metric_value' => 1,
                'record_date' => now()->toDateString(),
                'category' => 'search',
                'metadata' => json_encode([
                    'query' => $query,
                    'type' => $type,
                    'result_count' => $resultCount,
                    'user_id' => Auth::id()
                ]),
                'created_at' => now()
            ]);
        } catch (\Exception $e) {
            // Silent fail - không ảnh hưởng đến search functionality
        }
    }
}