<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Activity Verification - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">

    @include('admin.partials.navbar')

    <div class="max-w-7xl mx-auto px-4 py-8">
        
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800">
                <i class="fas fa-clock mr-3 text-purple-600"></i>
                Volunteer Activity Verification
            </h1>
            <p class="text-gray-600 mt-2">Review and verify volunteer hour submissions</p>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <form method="GET" action="{{ route('admin.activities.pending') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search Volunteer</label>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Volunteer name..."
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Organization</label>
                        <select name="org_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            <option value="">All Organizations</option>
                            @foreach($organizations as $org)
                            <option value="{{ $org->org_id }}" {{ request('org_id') == $org->org_id ? 'selected' : '' }}>
                                {{ $org->organization_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>
                <div class="flex justify-between">
                    <div class="space-x-2">
                        <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg transition">
                            <i class="fas fa-filter mr-2"></i>Filter
                        </button>
                        <a href="{{ route('admin.activities.pending') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg inline-block transition">
                            <i class="fas fa-redo mr-2"></i>Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Bulk Actions -->
        <div class="bg-white rounded-lg shadow-md p-4 mb-6">
            <form method="POST" action="{{ route('admin.activities.bulkVerify') }}" id="bulkForm">
                @csrf
                <div class="flex items-center space-x-4">
                    <input type="checkbox" id="selectAll" class="rounded">
                    <label for="selectAll" class="text-sm font-medium text-gray-700">Select All</label>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition">
                        <i class="fas fa-check-double mr-2"></i>Verify Selected
                    </button>
                </div>
            </form>
        </div>

        <!-- Activities List -->
        <div class="space-y-4">
            @forelse($activities as $activity)
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start space-x-4 flex-1">
                            <input type="checkbox" name="activity_ids[]" value="{{ $activity->activity_id }}" 
                                   class="activity-checkbox mt-1 rounded" form="bulkForm">
                            
                            <div class="flex-1">
                                <!-- Volunteer Info -->
                                <div class="flex items-center space-x-3 mb-3">
                                    @if($activity->volunteer->avatar_url)
                                    <img src="{{ $activity->volunteer->avatar_url }}" alt="" 
                                         class="w-12 h-12 rounded-full object-cover">
                                    @else
                                    <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center">
                                        <span class="text-purple-600 font-semibold text-lg">
                                            {{ substr($activity->volunteer->first_name, 0, 1) }}{{ substr($activity->volunteer->last_name, 0, 1) }}
                                        </span>
                                    </div>
                                    @endif
                                    <div>
                                        <h3 class="font-bold text-gray-800">
                                            {{ $activity->volunteer->first_name }} {{ $activity->volunteer->last_name }}
                                        </h3>
                                        <p class="text-sm text-gray-600">{{ $activity->volunteer->email }}</p>
                                    </div>
                                </div>

                                <!-- Activity Details -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <p class="text-sm text-gray-600">
                                            <i class="fas fa-building mr-2 text-blue-600"></i>
                                            <strong>Organization:</strong> 
                                            <a href="{{ route('admin.organizations.show', $activity->organization->org_id) }}" 
                                               class="text-blue-600 hover:underline">
                                                {{ $activity->organization->organization_name }}
                                            </a>
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">
                                            <i class="fas fa-hands-helping mr-2 text-green-600"></i>
                                            <strong>Opportunity:</strong> 
                                            {{ $activity->opportunity->title }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">
                                            <i class="fas fa-calendar mr-2 text-orange-600"></i>
                                            <strong>Date:</strong> 
                                            {{ $activity->activity_date->format('M d, Y') }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">
                                            <i class="fas fa-clock mr-2 text-purple-600"></i>
                                            <strong>Hours:</strong> 
                                            <span class="text-lg font-bold text-purple-600">{{ number_format($activity->hours_worked, 1) }}h</span>
                                        </p>
                                    </div>
                                </div>

                                @if($activity->activity_description)
                                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                                    <h4 class="font-semibold text-gray-700 mb-2">Activity Description:</h4>
                                    <p class="text-sm text-gray-600">{{ $activity->activity_description }}</p>
                                </div>
                                @endif

                                @if($activity->impact_notes)
                                <div class="bg-blue-50 rounded-lg p-4">
                                    <h4 class="font-semibold text-blue-700 mb-2">Impact Notes:</h4>
                                    <p class="text-sm text-blue-600">{{ $activity->impact_notes }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200 mt-4">
                        <button onclick="openVerifyModal({{ $activity->activity_id }})" 
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition">
                            <i class="fas fa-check mr-2"></i>Verify
                        </button>
                        <button onclick="openDisputeModal({{ $activity->activity_id }})" 
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition">
                            <i class="fas fa-exclamation-triangle mr-2"></i>Dispute
                        </button>
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <i class="fas fa-check-circle text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">All Caught Up!</h3>
                <p class="text-gray-500">No pending activities to verify</p>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($activities->hasPages())
        <div class="mt-6">
            {{ $activities->links() }}
        </div>
        @endif
    </div>

    <!-- Verify Modal -->
    <div id="verifyModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
            <h3 class="text-xl font-bold text-gray-800 mb-4">
                <i class="fas fa-check-circle text-green-600 mr-2"></i>
                Verify Volunteer Activity
            </h3>
            <form id="verifyForm" method="POST" action="">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Verification Notes (Optional)</label>
                    <textarea name="verification_notes" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                              placeholder="Add any notes about this verification..."></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('verifyModal')" 
                            class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition">
                        <i class="fas fa-check mr-2"></i>Verify
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Dispute Modal -->
    <div id="disputeModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
            <h3 class="text-xl font-bold text-gray-800 mb-4">
                <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                Dispute Activity
            </h3>
            <form id="disputeForm" method="POST" action="">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Dispute Reason *</label>
                    <textarea name="dispute_reason" rows="4" required
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500"
                              placeholder="Explain why this activity is disputed..."></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('disputeModal')" 
                            class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg transition">
                        <i class="fas fa-flag mr-2"></i>Mark as Disputed
                    </button>
                </div>
            </form>
        </div>
    </div>
<script>
        // Select all functionality
        document.getElementById('selectAll').addEventListener('change', function() {
            document.querySelectorAll('.activity-checkbox').forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        function openVerifyModal(activityId) {
            document.getElementById('verifyForm').action = `/admin/activities/${activityId}/verify`;
            document.getElementById('verifyModal').classList.remove('hidden');
        }

        function openDisputeModal(activityId) {
            document.getElementById('disputeForm').action = `/admin/activities/${activityId}/dispute`;
            document.getElementById('disputeModal').classList.remove('hidden');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }
    </script>
</body>
</html>
