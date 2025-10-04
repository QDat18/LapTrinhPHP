@extends('layouts.admin')

@section('title', 'Activity Details')

@section('content')
<div class="space-y-6">
    
    <!-- Breadcrumb -->
    <nav class="text-sm">
        <ol class="flex items-center space-x-2 text-gray-600 dark:text-gray-400">
            <li><a href="{{ route('admin.dashboard') }}" class="hover:text-indigo-600">Dashboard</a></li>
            <li><span class="mx-2">/</span></li>
            <li><a href="{{ route('admin.activities.index') }}" class="hover:text-indigo-600">Activities</a></li>
            <li><span class="mx-2">/</span></li>
            <li class="text-gray-900 dark:text-gray-100 font-medium">#{{ $activity->activity_id }}</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Activity #{{ $activity->activity_id }}</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                Logged on {{ $activity->activity_date->format('F d, Y') }}
            </p>
        </div>
        <div class="flex space-x-3">
            <span class="px-4 py-2 rounded-lg font-semibold
                {{ $activity->status == 'Verified' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                   ($activity->status == 'Pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 
                   'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200') }}">
                {{ $activity->status }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Activity Details -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">Activity Details</h2>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Activity Date</label>
                        <p class="mt-1 text-gray-900 dark:text-gray-100">{{ $activity->activity_date->format('M d, Y') }}</p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Hours Worked</label>
                        <p class="mt-1 text-gray-900 dark:text-gray-100 text-2xl font-bold text-indigo-600">
                            {{ $activity->hours_worked }} hrs
                        </p>
                    </div>
                    
                    <div class="col-span-2">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Opportunity</label>
                        <p class="mt-1 text-gray-900 dark:text-gray-100">
                            <a href="{{ route('admin.opportunities.show', $activity->opportunity_id) }}" 
                               class="text-indigo-600 hover:underline">
                                {{ $activity->opportunity->title }}
                            </a>
                        </p>
                    </div>
                    
                    @if($activity->activity_description)
                    <div class="col-span-2">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                        <p class="mt-1 text-gray-700 dark:text-gray-300">{{ $activity->activity_description }}</p>
                    </div>
                    @endif
                    
                    @if($activity->impact_notes)
                    <div class="col-span-2">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Impact Notes</label>
                        <p class="mt-1 text-gray-700 dark:text-gray-300">{{ $activity->impact_notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Verification Info -->
            @if($activity->verified_by)
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-6">
                <h3 class="font-semibold text-green-900 dark:text-green-100 mb-2">
                    <i class="fas fa-check-circle mr-2"></i>Verified
                </h3>
                <p class="text-sm text-green-700 dark:text-green-300">
                    Verified by {{ $activity->verifier->first_name }} {{ $activity->verifier->last_name }}
                    on {{ $activity->verified_date->format('M d, Y h:i A') }}
                </p>
            </div>
            @endif

        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            
            <!-- Volunteer Info -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Volunteer</h2>
                <div class="flex items-center space-x-3 mb-3">
                    <img src="{{ $activity->volunteer->avatar_url ?? '/images/default-avatar.png' }}" 
                         alt="{{ $activity->volunteer->first_name }}"
                         class="w-12 h-12 rounded-full object-cover">
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100">
                            {{ $activity->volunteer->first_name }} {{ $activity->volunteer->last_name }}
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $activity->volunteer->email }}</p>
                    </div>
                </div>
                <a href="{{ route('admin.users.show', $activity->volunteer_id) }}" 
                   class="block w-full text-center px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                    View Profile
                </a>
            </div>

            <!-- Organization Info -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Organization</h2>
                <div class="flex items-center space-x-3 mb-3">
                    <img src="{{ $activity->organization->user->avatar_url ?? '/images/default-org.png' }}" 
                         alt="{{ $activity->organization->organization_name }}"
                         class="w-12 h-12 rounded-lg object-cover">
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100">
                            {{ $activity->organization->organization_name }}
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $activity->organization->organization_type }}</p>
                    </div>
                </div>
                <a href="{{ route('admin.organizations.show', $activity->org_id) }}" 
                   class="block w-full text-center px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                    View Organization
                </a>
            </div>

            <!-- Actions -->
            @if($activity->status == 'Disputed')
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Resolve Dispute</h2>
                <div class="space-y-2">
                    <button onclick="resolveDispute({{ $activity->activity_id }}, 'approve')" 
                            class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                        <i class="fas fa-check mr-2"></i>Approve Activity
                    </button>
                    <button onclick="resolveDispute({{ $activity->activity_id }}, 'reject')" 
                            class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                        <i class="fas fa-times mr-2"></i>Reject Activity
                    </button>
                </div>
            </div>
            @endif

        </div>
    </div>

</div>

@push('scripts')
<script>
async function resolveDispute(activityId, resolution) {
    const notes = prompt(`Enter admin notes for ${resolution}:`);
    if (notes === null) return;

    try {
        const response = await fetch(`/admin/activities/${activityId}/resolve-dispute`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ resolution, admin_notes: notes })
        });

        const data = await response.json();
        
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => window.location.reload(), 1000);
        }
    } catch (error) {
        showToast('Failed to resolve dispute', 'error');
    }
}
</script>
@endpush
@endsection