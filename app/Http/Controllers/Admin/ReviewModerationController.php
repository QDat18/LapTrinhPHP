<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;
use Illuminate\Support\Facades\Validator;

class ReviewModerationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:Admin']);
    }

    /**
     * Display reviews pending moderation
     */
    public function index(Request $request)
    {
        $query = Review::with(['reviewer', 'reviewee', 'opportunity']);

        // Filter by approval status
        $status = $request->get('status', 'pending');
        if ($status == 'pending') {
            $query->where('is_approved', false);
        } elseif ($status == 'approved') {
            $query->where('is_approved', true);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('review_title', 'LIKE', "%{$search}%")
                  ->orWhere('review_text', 'LIKE', "%{$search}%");
            });
        }

        // Filter by review type
        if ($request->filled('review_type')) {
            $query->where('review_type', $request->review_type);
        }

        // Filter by rating
        if ($request->filled('min_rating')) {
            $query->where('rating', '>=', $request->min_rating);
        }

        $reviews = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.reviews.moderate', compact('reviews', 'status'));
    }

    /**
     * Show review details
     */
    public function show($id)
    {
        $review = Review::with([
            'reviewer.volunteerProfile',
            'reviewee',
            'opportunity'
        ])->findOrFail($id);

        // Get other reviews by same reviewer
        $reviewerHistory = Review::where('reviewer_id', $review->reviewer_id)
            ->where('review_id', '!=', $id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get other reviews for same reviewee
        $revieweeReviews = Review::where('reviewee_id', $review->reviewee_id)
            ->where('review_id', '!=', $id)
            ->where('is_approved', true)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('admin.reviews.show', compact('review', 'reviewerHistory', 'revieweeReviews'));
    }

    /**
     * Approve review
     */
    public function approve($id)
    {
        $review = Review::findOrFail($id);
        $review->approve();

        return redirect()->back()->with('success', 'Review has been approved and published.');
    }

    /**
     * Reject/Delete review
     */
    public function reject(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $review = Review::findOrFail($id);
        
        // TODO: Send notification to reviewer about rejection
        
        $review->delete();

        return redirect()->route('admin.reviews.moderate')
            ->with('success', 'Review has been rejected and removed.');
    }

    /**
     * Bulk actions on reviews
     */
    public function bulkAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:approve,reject',
            'review_ids' => 'required|array',
            'review_ids.*' => 'exists:reviews,review_id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $reviews = Review::whereIn('review_id', $request->review_ids)->get();

        if ($request->action == 'approve') {
            foreach ($reviews as $review) {
                $review->approve();
            }
            $message = count($reviews) . ' reviews have been approved.';
        } else {
            Review::whereIn('review_id', $request->review_ids)->delete();
            $message = count($reviews) . ' reviews have been rejected.';
        }

        return redirect()->back()->with('success', $message);
    }
}
