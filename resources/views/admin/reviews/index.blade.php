@extends('layouts.admin')

@section('title', 'Reviews Management')
@section('breadcrumb', 'Reviews')

@section('content')
<div class="space-y-6">
    
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Reviews Management</h2>
            <p class="text-gray-600 mt-1">Manage and moderate user reviews</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.reviews.pending') }}" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition">
                <i class="fas fa-clock mr-2"></i>Pending Reviews ({{ $pendingCount ?? 0 }})
            </a>
        </div>
    </div>
    
    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total Reviews</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-star text-blue-600"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Approved</p>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['approved'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Pending</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-600"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Avg Rating</p>
                    <p class="text-2xl font-bold text-orange-600">{{ number_format($stats['avg_rating'] ?? 0, 1) }}</p>
                </div>
                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-star-half-alt text-orange-600"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form action="{{ route('admin.reviews.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                       placeholder="Search reviews...">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="">All Status</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
                <select name="rating" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="">All Ratings</option>
                    <option value="5">5 Stars</option>
                    <option value="4">4 Stars</option>
                    <option value="3">3 Stars</option>
                    <option value="2">2 Stars</option>
                    <option value="1">1 Star</option>
                </select>
            </div>
            
            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    <i class="fas fa-filter mr-2"></i>Apply
                </button>
                <a href="{{ route('admin.reviews.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Reset
                </a>
            </div>
        </form>
    </div>
    
    <!-- Reviews List -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="divide-y divide-gray-200">
            @forelse($reviews as $review)
            <div class="p-6 hover:bg-gray-50 transition">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <!-- Rating -->
                        <div class="flex items-center space-x-2 mb-2">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                            @endfor
                            <span class="text-sm text-gray-600">{{ $review->rating }}/5</span>
                            <span class="px-2 py-1 text-xs rounded-full {{ $review->is_approved ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $review->is_approved ? 'Approved' : 'Pending' }}
                            </span>
                        </div>
                        
                        <!-- Title -->
                        <h3 class="font-semibold text-gray-900 mb-1">{{ $review->review_title }}</h3>
                        
                        <!-- Review Text -->
                        <p class="text-gray-700 mb-3">{{ Str::limit($review->review_text, 200) }}</p>
                        
                        <!-- Meta Info -->
                        <div class="flex items-center space-x-4 text-sm text-gray-500">
                            <div class="flex items-center">
                                <i class="fas fa-user mr-1"></i>
                                <span>{{ $review->reviewer->first_name }} {{ $review->reviewer->last_name }}</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-arrow-right mr-1"></i>
                                <span>{{ $review->reviewee->first_name }} {{ $review->reviewee->last_name }}</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-calendar mr-1"></i>
                                <span>{{ $review->created_at->diffForHumans() }}</span>
                            </div>
                            @if($review->opportunity)
                            <div class="flex items-center">
                                <i class="fas fa-briefcase mr-1"></i>
                                <span>{{ Str::limit($review->opportunity->title, 30) }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex flex-col space-y-2 ml-4">
                        @if(!$review->is_approved)
                            <button onclick="approveReview({{ $review->review_id }})" 
                                    class="px-4 py-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition text-sm">
                                <i class="fas fa-check mr-1"></i>Approve
                            </button>
                            <button onclick="rejectReview({{ $review->review_id }})" 
                                    class="px-4 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition text-sm">
                                <i class="fas fa-times mr-1"></i>Reject
                            </button>
                        @else
                            <a href="{{ route('reviews.show', $review->review_id) }}" 
                               class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition text-sm text-center">
                                <i class="fas fa-eye mr-1"></i>View
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="p-12 text-center">
                <i class="fas fa-star text-6xl text-gray-300 mb-4"></i>
                <p class="text-lg font-medium text-gray-900">No reviews found</p>
                <p class="text-gray-500 mt-1">Try adjusting your filters</p>
            </div>
            @endforelse
        </div>
        
        <!-- Pagination -->
        @if($reviews->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $reviews->links() }}
        </div>
        @endif
    </div>
    
</div>

@push('scripts')
<script>
    function approveReview(reviewId) {
        if (confirm('Are you sure you want to approve this review?')) {
            fetch(`/admin/reviews/${reviewId}/approve`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Review approved successfully', 'success');
                    setTimeout(() => location.reload(), 1000);
                }
            })
            .catch(() => showToast('An error occurred', 'error'));
        }
    }
    
    function rejectReview(reviewId) {
        const reason = prompt('Enter reason for rejection:');
        if (reason) {
            fetch(`/admin/reviews/${reviewId}/reject`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ reason: reason })
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
    }
</script>
@endpush
@endsection