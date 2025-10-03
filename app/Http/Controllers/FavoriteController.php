<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\VolunteerOpportunity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FavoriteController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    //     $this->middleware('volunteer')->except(['index']);
    // }

    // Danh sách favorites
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Favorite::with(['opportunity.organization', 'opportunity.category'])
            ->where('user_id', $user->user_id);
        
        // Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('opportunity', function($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%");
            });
        }
        
        // Filter by category
        if ($request->has('category') && $request->category != '') {
            $query->whereHas('opportunity', function($q) use ($request) {
                $q->where('category_id', $request->category);
            });
        }
        
        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if ($sortBy === 'title') {
            $query->join('volunteer_opportunities', 'favorites.opportunity_id', '=', 'volunteer_opportunities.opportunity_id')
                ->orderBy('volunteer_opportunities.title', $sortOrder)
                ->select('favorites.*');
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }
        
        $favorites = $query->paginate(12);
        
        // Get categories for filter
        $categories = DB::table('categories')
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get();
        
        return view('favorites.index', compact('favorites', 'categories'));
    }

    // Toggle favorite (thêm/xóa)
    public function toggle(Request $request)
    {
        $validated = $request->validate([
            'opportunity_id' => 'required|exists:volunteer_opportunities,opportunity_id'
        ]);
        
        $user = Auth::user();
        
        // Kiểm tra opportunity có active không
        $opportunity = VolunteerOpportunity::findOrFail($validated['opportunity_id']);
        
        if ($opportunity->status !== 'Active') {
            return response()->json([
                'success' => false,
                'message' => 'Không thể favorite opportunity không active!'
            ], 400);
        }
        
        // Toggle favorite
        $favorite = Favorite::where('user_id', $user->user_id)
            ->where('opportunity_id', $validated['opportunity_id'])
            ->first();
        
        if ($favorite) {
            // Xóa favorite
            $favorite->delete();
            
            return response()->json([
                'success' => true,
                'action' => 'removed',
                'message' => 'Đã xóa khỏi danh sách yêu thích'
            ]);
        } else {
            // Thêm favorite
            Favorite::create([
                'user_id' => $user->user_id,
                'opportunity_id' => $validated['opportunity_id']
            ]);
            
            return response()->json([
                'success' => true,
                'action' => 'added',
                'message' => 'Đã thêm vào danh sách yêu thích'
            ]);
        }
    }

    // Update notes cho favorite
    public function updateNotes(Request $request, $id)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:500'
        ]);
        
        $favorite = Favorite::findOrFail($id);
        $user = Auth::user();
        
        // Kiểm tra quyền
        if ($favorite->user_id != $user->user_id) {
            abort(403, 'Bạn không có quyền chỉnh sửa favorite này');
        }
        
        $favorite->update([
            'notes' => $validated['notes']
        ]);
        
        return back()->with('success', 'Đã cập nhật ghi chú thành công!');
    }

    // Xóa favorite
    public function destroy($id)
    {
        $favorite = Favorite::findOrFail($id);
        $user = Auth::user();
        
        // Kiểm tra quyền
        if ($favorite->user_id != $user->user_id) {
            abort(403, 'Bạn không có quyền xóa favorite này');
        }
        
        $favorite->delete();
        
        return back()->with('success', 'Đã xóa khỏi danh sách yêu thích!');
    }

    // Bulk delete favorites
    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'favorite_ids' => 'required|array',
            'favorite_ids.*' => 'exists:favorites,favorite_id'
        ]);
        
        $user = Auth::user();
        
        $count = Favorite::whereIn('favorite_id', $validated['favorite_ids'])
            ->where('user_id', $user->user_id)
            ->delete();
        
        return back()->with('success', "Đã xóa $count favorites!");
    }

    // Kiểm tra xem opportunity có được favorite không
    public function check($opportunityId)
    {
        $user = Auth::user();
        
        $isFavorited = Favorite::where('user_id', $user->user_id)
            ->where('opportunity_id', $opportunityId)
            ->exists();
        
        return response()->json([
            'is_favorited' => $isFavorited
        ]);
    }

    // Export favorites to CSV
    public function export()
    {
        $user = Auth::user();
        
        $favorites = Favorite::with(['opportunity.organization', 'opportunity.category'])
            ->where('user_id', $user->user_id)
            ->get();
        
        $filename = 'my_favorites_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($favorites) {
            $file = fopen('php://output', 'w');
            
            // BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'Opportunity Title',
                'Organization',
                'Category',
                'Location',
                'Start Date',
                'My Notes',
                'Added Date'
            ]);
            
            foreach ($favorites as $favorite) {
                fputcsv($file, [
                    $favorite->opportunity->title,
                    $favorite->opportunity->organization->organization_name,
                    $favorite->opportunity->category->category_name ?? 'N/A',
                    $favorite->opportunity->location,
                    $favorite->opportunity->start_date,
                    $favorite->notes,
                    $favorite->created_at->format('Y-m-d')
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    // Get favorite count
    public function count()
    {
        $user = Auth::user();
        
        $count = Favorite::where('user_id', $user->user_id)->count();
        
        return response()->json([
            'count' => $count
        ]);
    }

    // Clear all favorites (with confirmation)
    public function clearAll()
    {
        $user = Auth::user();
        
        $count = Favorite::where('user_id', $user->user_id)->delete();
        
        return back()->with('success', "Đã xóa tất cả $count favorites!");
    }
}