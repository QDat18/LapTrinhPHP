@extends('layouts.admin')

@section('title', 'Pending Reviews')
@section('breadcrumb', 'Reviews > Pending')

@section('content')
<div class="space-y-6">
    
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Pending Reviews</h2>
            <p class="text-gray-600 mt-1">{{ $reviews->total() }} reviews waiting for approval</p>
        </div>
        <div class="flex space-x-3">
            <button onclick="bulkApprove()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                <i class="fas fa-check-double mr-2"></i>Bulk Approve
            </button>
            <a href="{{ route('admin.reviews.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                <i class="fas fa-arrow-left mr-2"></i>All Reviews
            </a>
        </div>
    </div>
    
    <!-- Bulk Actions Bar -->
    <div id="bulkActionsBar" class="hidden bg-indigo-50 border border-indigo-200 rounded-lg p-4">
        <div class="flex items-center justify-between">
            <span class="text-sm text-indigo-700">
                <span id="selectedCount">0</span> reviews selected
            </span>
            <div class="flex space-x-2">
                <button onclick="bulkApproveSelected()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm">
                    <i class="fas fa-check mr-1"></i>Approve Selected
                </button>
                <button onclick="deselectAll()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-sm">
                    Deselect All
                </button>
            </div>
        </div>
    </div>
    
    <!-- Reviews Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @forelse($reviews as $review)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center space-x-3">
                    <input type="checkbox" class="review-checkbox w-5 h-5 text-indigo-600 rounded" 
                           value="{{ $review->review_id }}" onchange="updateSelection()">
                    <div>
                        <!-- Rating -->
                        <div class="flex items-center space-x-1">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }} text-lg"></i>
                            @endfor
                        </div>
                        <p class="text-xs text-gray-500 mt-1">{{ $review->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                <span class="px-3 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                    Pending
                </span>
            </div>
            
            <!-- Review Title -->
            <h3 class="font-semibold text-gray-900 mb-2">{{ $review->review_title }}</h3>
            
            <!-- Review Text -->
            <p class="text-gray-700 text-sm mb-4">{{ $review->review_text }}</p>
            
            <!-- Reviewer Info -->
            <div class="flex items-center space-x-3 mb-4 pb-4 border-b border-gray-200">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($review->reviewer->first_name) }}&background=random" 
                     class="w-10 h-10 rounded-full" alt="Avatar">
                <div>
                    <p class="text-sm font-medium text-gray-900">
                        {{ $review->reviewer->first_name }} {{ $review->reviewer->last_name }}
                    </p>
                    <p class="text-xs text-gray-500">
                        <i class="fas fa-arrow-right mr-1"></i>
                        {{ $review->reviewee->first_name }} {{ $review->reviewee->last_name }}
                        <span class="mx-1">•</span>
                        {{ $review->review_type }}
                    </p>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="grid grid-cols-2 gap-2">
                <button onclick="quickApprove({{ $review->review_id }})" 
                        class="px-4 py-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition text-sm font-medium">
                    <i class="fas fa-check mr-1"></i>Approve
                </button>
                <button onclick="quickReject({{ $review->review_id }})" 
                        class="px-4 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition text-sm font-medium">
                    <i class="fas fa-times mr-1"></i>Reject
                </button>
            </div>
        </div>
        @empty
        <div class="lg:col-span-2 bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
            <i class="fas fa-check-circle text-6xl text-green-300 mb-4"></i>
            <p class="text-lg font-medium text-gray-900">All caught up!</p>
            <p class="text-gray-500 mt-1">No pending reviews at the moment</p>
        </div>
        @endforelse
    </div>
    
    <!-- Pagination -->
    @if($reviews->hasPages())
    <div class="flex justify-center">
        {{ $reviews->links() }}
    </div>
    @endif
    
</div>

@push('scripts')
<script>
    const selectedReviews = new Set();
    
    function updateSelection() {
        selectedReviews.clear();
        document.querySelectorAll('.review-checkbox:checked').forEach(cb => {
            selectedReviews.add(parseInt(cb.value));
        });
        
        const count = selectedReviews.size;
        document.getElementById('selectedCount').textContent = count;
        document.getElementById('bulkActionsBar').classList.toggle('hidden', count === 0);
    }
    
    function deselectAll() {
        document.querySelectorAll('.review-checkbox').forEach(cb => cb.checked = false);
        updateSelection();
    }
    
    function quickApprove(reviewId) {
        if (confirm('Approve this review?')) {
            approveReviews([reviewId]);
        }
    }
    
    function quickReject(reviewId) {
        const reason = prompt('Enter reason for rejection (optional):');
        if (reason !== null) {
            rejectReview(reviewId, reason);
        }
    }
    
    function bulkApproveSelected() {
        if (selectedReviews.size === 0) {
            showToast('Please select at least one review', 'warning');
            return;
        }
        
        if (confirm(`Approve ${selectedReviews.size} review(s)?`)) {
            approveReviews(Array.from(selectedReviews));
        }
    }
    
    function approveReviews(reviewIds) {
        fetch('/admin/reviews/bulk-approve', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ review_ids: reviewIds })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(`${reviewIds.length} review(s) approved successfully`, 'success');
                setTimeout(() => location.reload(), 1000);
            }
        })
        .catch(() => showToast('An error occurred', 'error'));
    }
    
    function rejectReview(reviewId, reason) {
        fetch(`/admin/reviews/${reviewId}/reject`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ reason: reason || 'Rejected by admin' })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Review rejected', 'success');
                setTimeout(() => location.reload(), 1000);
            }
        })
        .catch(() => showToast('An error occurred', 'error'));
    }
</script>
@endpush
@endsection