<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Moderation - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">

    @include('admin.partials.navbar')

    <div class="max-w-7xl mx-auto px-4 py-8">
        
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800">
                <i class="fas fa-star mr-3 text-yellow-600"></i>
                Review Moderation
            </h1>
            <p class="text-gray-600 mt-2">Review and moderate user-submitted reviews</p>
        </div>

        <!-- Status Tabs -->
        <div class="mb-6 border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <a href="{{ route('admin.reviews.moderate', ['status' => 'pending']) }}" 
                   class="py-4 px-1 border-b-2 font-medium text-sm
                          {{ $status == 'pending' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    <i class="fas fa-clock mr-2"></i>
                    Pending
                </a>
                <a href="{{ route('admin.reviews.moderate', ['status' => 'approved']) }}" 
                   class="py-4 px-1 border-b-2 font-medium text-sm
                          {{ $status == 'approved' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    <i class="fas fa-check-circle mr-2"></i>
                    Approved
                </a>
            </nav>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <form method="GET" action="{{ route('admin.reviews.moderate') }}" class="space-y-4">
                <input type="hidden" name="status" value="{{ $status }}">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Search reviews..."
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Review Type</label>
                        <select name="review_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <option value="">All Types</option>
                            <option value="Volunteer to Organization" {{ request('review_type') == 'Volunteer to Organization' ? 'selected' : '' }}>
                                Volunteer → Organization
                            </option>
                            <option value="Organization to Volunteer" {{ request('review_type') == 'Organization to Volunteer' ? 'selected' : '' }}>
                                Organization → Volunteer
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Rating</label>
                        <select name="min_rating" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <option value="">All Ratings</option>
                            <option value="5" {{ request('min_rating') == '5' ? 'selected' : '' }}>5 Stars</option>
                            <option value="4" {{ request('min_rating') == '4' ? 'selected' : '' }}>4+ Stars</option>
                            <option value="3" {{ request('min_rating') == '3' ? 'selected' : '' }}>3+ Stars</option>
                            <option value="2" {{ request('min_rating') == '2' ? 'selected' : '' }}>2+ Stars</option>
                            <option value="1" {{ request('min_rating') == '1' ? 'selected' : '' }}>1+ Star</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-between">
                    <div class="space-x-2">
                        <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white px-6 py-2 rounded-lg transition">
                            <i class="fas fa-filter mr-2"></i>Filter
                        </button>
                        <a href="{{ route('admin.reviews.moderate') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg inline-block transition">
                            <i class="fas fa-redo mr-2"></i>Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Bulk Actions -->
        @if($status == 'pending')
        <div class="bg-white rounded-lg shadow-md p-4 mb-6">
            <form method="POST" action="{{ route('admin.reviews.bulkAction') }}" id="bulkForm">
                @csrf
                <div class="flex items-center space-x-4">
                    <input type="checkbox" id="selectAll" class="rounded">
                    <label for="selectAll" class="text-sm font-medium text-gray-700">Select All</label>
                    <button type="submit" name="action" value="approve" 
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition">
                        <i class="fas fa-check mr-2"></i>Approve Selected
                    </button>
                    <button type="submit" name="action" value="reject" 
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition"
                            onclick="return confirm('Are you sure you want to reject selected reviews?')">
                        <i class="fas fa-times mr-2"></i>Reject Selected
                    </button>
                </div>
            </form>
        </div>
        @endif

        <!-- Reviews List -->
        <div class="space-y-4">
            @forelse($reviews as $review)
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-start space-x-4 flex-1">
                            @if($status == 'pending')
                            <input type="checkbox" name="review_ids[]" value="{{ $review->review_id }}" 
                                   class="review-checkbox mt-1 rounded" form="bulkForm">
                            @endif
                            
                            <div class="flex-1">
                                <!-- Reviewer Info -->
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center space-x-3">
                                        @if($review->reviewer->avatar_url)
                                        <img src="{{ $review->reviewer->avatar_url }}" alt="" 
                                             class="w-10 h-10 rounded-full object-cover">
                                        @else
                                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                            <span class="text-blue-600 font-semibold">
                                                {{ substr($review->reviewer->first_name, 0, 1) }}{{ substr($review->reviewer->last_name, 0, 1) }}
                                            </span>
                                        </div>
                                        @endif
                                        <div>
                                            <h4 class="font-semibold text-gray-800">
                                                {{ $review->reviewer->first_name }} {{ $review->reviewer->last_name }}
                                            </h4>
                                            <p class="text-xs text-gray-500">{{ $review->reviewer->user_type }}</p>
                                        </div>
                                    </div>
                                    
                                    <!-- Rating Stars -->
                                    <div class="flex items-center space-x-1">
                                        @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star {{ $i <= $review->rating ? 'text-yellow-500' : 'text-gray-300' }}"></i>
                                        @endfor
                                        <span class="ml-2 font-semibold text-gray-700">{{ $review->rating }}/5</span>
                                    </div>
                                </div>

                                <!-- Review Type Badge -->
                                <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full mb-3
                                    {{ $review->review_type == 'Volunteer to Organization' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                    <i class="fas fa-arrow-right mr-1"></i>{{ $review->review_type }}
                                </span>

                                <!-- Review Content -->
                                @if($review->review_title)
                                <h3 class="font-bold text-lg text-gray-800 mb-2">{{ $review->review_title }}</h3>
                                @endif

                                @if($review->review_text)
                                <div class="bg-gray-50 rounded-lg p-4 mb-3">
                                    <p class="text-gray-700">{{ $review->review_text }}</p>
                                </div>
                                @endif

                                <!-- Meta Info -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm text-gray-600">
                                    <div>
                                        <i class="fas fa-user mr-2 text-gray-400"></i>
                                        <strong>Reviewing:</strong> 
                                        {{ $review->reviewee->first_name }} {{ $review->reviewee->last_name }}
                                    </div>
                                    @if($review->opportunity)
                                    <div>
                                        <i class="fas fa-hands-helping mr-2 text-gray-400"></i>
                                        <strong>Opportunity:</strong> 
                                        {{ $review->opportunity->title }}
                                    </div>
                                    @endif
                                    <div>
                                        <i class="fas fa-calendar mr-2 text-gray-400"></i>
                                        <strong>Posted:</strong> 
                                        {{ $review->created_at->format('M d, Y') }}
                                    </div>
                                </div>

                                @if($review->is_approved)
                                <div class="mt-3 flex items-center text-sm text-gray-600">
                                    <i class="fas fa-thumbs-up mr-2 text-blue-600"></i>
                                    <span>{{ $review->helpful_count }} people found this helpful</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    @if($status == 'pending')
                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                        <a href="{{ route('admin.reviews.show', $review->review_id) }}" 
                           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
                            <i class="fas fa-eye mr-2"></i>View Details
                        </a>
                        <form method="POST" action="{{ route('admin.reviews.approve', $review->review_id) }}" class="inline">
                            @csrf
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition">
                                <i class="fas fa-check mr-2"></i>Approve
                            </button>
                        </form>
                        <button onclick="openRejectModal('{{ $review->review_id }}')" 
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition">
                            <i class="fas fa-times mr-2"></i>Reject
                        </button>
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <i class="fas fa-star text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">No Reviews Found</h3>
                <p class="text-gray-500">No {{ $status }} reviews at the moment</p>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($reviews->hasPages())
        <div class="mt-6">
            {{ $reviews->links() }}
        </div>
        @endif
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
            <h3 class="text-xl font-bold text-gray-800 mb-4">
                <i class="fas fa-times-circle text-red-600 mr-2"></i>
                Reject Review
            </h3>
            <p class="text-gray-600 mb-4">Are you sure you want to reject and delete this review? This action cannot be undone.</p>
            <form id="rejectForm" method="POST" action="">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason (Optional)</label>
                    <textarea name="rejection_reason" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500"
                              placeholder="Explain why this review is being rejected..."></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('rejectModal')" 
                            class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg transition">
                        <i class="fas fa-trash mr-2"></i>Reject & Delete
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Select all functionality
        const selectAllCheckbox = document.getElementById('selectAll');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                document.querySelectorAll('.review-checkbox').forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });
        }

        function openRejectModal(reviewId) {
            document.getElementById('rejectForm').action = `/admin/reviews/${reviewId}/reject`;
            document.getElementById('rejectModal').classList.remove('hidden');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('rejectModal');
            if (event.target == modal) {
                closeModal('rejectModal');
            }
        }
    </script>

</body>
</html>