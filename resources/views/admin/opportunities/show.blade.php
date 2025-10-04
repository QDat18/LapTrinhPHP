@extends('layouts.admin')

@section('title', 'Opportunity Details')

@section('content')
<div class="space-y-6">
    
    <!-- Breadcrumb -->
    <nav class="text-sm">
        <ol class="flex items-center space-x-2 text-gray-600">
            <li><a href="{{ route('admin.dashboard') }}" class="hover:text-indigo-600">Dashboard</a></li>
            <li><span class="mx-2">/</span></li>
            <li><a href="{{ route('admin.opportunities.index') }}" class="hover:text-indigo-600">Opportunities</a></li>
            <li><span class="mx-2">/</span></li>
            <li class="text-gray-900 font-medium">{{ Str::limit($opportunity->title, 50) }}</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $opportunity->title }}</h1>
            <p class="text-sm text-gray-600 mt-1">{{ $opportunity->organization->organization_name }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('opportunities.show', $opportunity->opportunity_id) }}" target="_blank"
               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-external-link-alt mr-2"></i>View Public Page
            </a>
            <a href="{{ route('admin.opportunities.index') }}" 
               class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Volunteers Needed</p>
                    <p class="text-2xl font-bold text-indigo-600 mt-1">{{ $opportunity->volunteers_needed }}</p>
                </div>
                <i class="fas fa-users text-indigo-600 text-2xl"></i>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Applications</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">{{ $opportunity->application_count }}</p>
                </div>
                <i class="fas fa-file-alt text-green-600 text-2xl"></i>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Views</p>
                    <p class="text-2xl font-bold text-blue-600 mt-1">{{ $opportunity->view_count }}</p>
                </div>
                <i class="fas fa-eye text-blue-600 text-2xl"></i>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Registered</p>
                    <p class="text-2xl font-bold text-purple-600 mt-1">{{ $opportunity->volunteers_registered }}</p>
                </div>
                <i class="fas fa-user-check text-purple-600 text-2xl"></i>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Basic Info -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Basic Information</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Category</label>
                        <p class="mt-1">
                            @if($opportunity->category)
                            <span class="px-3 py-1 rounded-full text-sm font-medium inline-block"
                                  style="background-color: {{ $opportunity->category->color }}20; color: {{ $opportunity->category->color }}">
                                <i class="{{ $opportunity->category->icon }} mr-1"></i>
                                {{ $opportunity->category->category_name }}
                            </span>
                            @else
                            <span class="text-gray-500">N/A</span>
                            @endif
                        </p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-700">Status</label>
                        <p class="mt-1">
                            <span class="px-3 py-1 text-xs font-medium rounded-full 
                                {{ $opportunity->status == 'Active' ? 'bg-green-100 text-green-800' : 
                                   ($opportunity->status == 'Paused' ? 'bg-yellow-100 text-yellow-800' : 
                                   ($opportunity->status == 'Completed' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800')) }}">
                                {{ $opportunity->status }}
                            </span>
                        </p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-700">Location</label>
                        <p class="mt-1 text-gray-900">{{ $opportunity->location }}</p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-700">Time Commitment</label>
                        <p class="mt-1 text-gray-900">{{ $opportunity->time_commitment }}</p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-700">Schedule Type</label>
                        <p class="mt-1 text-gray-900">{{ $opportunity->schedule_type }}</p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-700">Experience Needed</label>
                        <p class="mt-1 text-gray-900">{{ $opportunity->experience_needed }}</p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-700">Start Date</label>
                        <p class="mt-1 text-gray-900">{{ $opportunity->start_date->format('M d, Y') }}</p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-700">End Date</label>
                        <p class="mt-1 text-gray-900">{{ $opportunity->end_date ? $opportunity->end_date->format('M d, Y') : 'Ongoing' }}</p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-700">Application Deadline</label>
                        <p class="mt-1 text-gray-900">{{ $opportunity->application_deadline ? \Carbon\Carbon::parse($opportunity->application_deadline)->format('M d, Y') : 'No deadline' }}</p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-700">Minimum Age</label>
                        <p class="mt-1 text-gray-900">{{ $opportunity->min_age }} years</p>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Description</h2>
                <div class="prose max-w-none text-gray-700">
                    {!! nl2br(e($opportunity->description)) !!}
                </div>
            </div>

            <!-- Requirements -->
            @if($opportunity->requirements)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Requirements</h2>
                <div class="prose max-w-none text-gray-700">
                    {!! nl2br(e($opportunity->requirements)) !!}
                </div>
            </div>
            @endif

            <!-- Benefits -->
            @if($opportunity->benefits)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Benefits</h2>
                <div class="prose max-w-none text-gray-700">
                    {!! nl2br(e($opportunity->benefits)) !!}
                </div>
            </div>
            @endif

        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            
            <!-- Organization Info -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Organization</h2>
                <div class="flex items-start space-x-3 mb-3">
                    <img src="{{ $opportunity->organization->user->avatar_url ?? '/images/default-org.png' }}" 
                         alt="{{ $opportunity->organization->organization_name }}"
                         class="w-12 h-12 rounded-lg object-cover">
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ $opportunity->organization->organization_name }}</h3>
                        <p class="text-sm text-gray-600">{{ $opportunity->organization->organization_type }}</p>
                    </div>
                </div>
                <a href="{{ route('admin.organizations.show', $opportunity->organization->org_id) }}" 
                   class="block w-full text-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                    View Organization
                </a>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Actions</h2>
                <div class="space-y-2">
                    <button onclick="changeStatus({{ $opportunity->opportunity_id }})" 
                            class="w-full px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition">
                        <i class="fas fa-edit mr-2"></i>Change Status
                    </button>
                    <button onclick="deleteOpportunity({{ $opportunity->opportunity_id }})" 
                            class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                        <i class="fas fa-trash mr-2"></i>Delete
                    </button>
                </div>
            </div>

        </div>
    </div>

</div>

@push('scripts')
<script>
function changeStatus(oppId) {
    // Copy from index.blade.php
    document.getElementById('statusOppId').value = oppId;
    document.getElementById('statusModal').classList.remove('hidden');
}

function deleteOpportunity(oppId) {
    if (!confirm('Are you sure you want to delete this opportunity?')) return;
    
    fetch(`/admin/opportunities/${oppId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '{{ route("admin.opportunities.index") }}';
        }
    });
}
</script>
@endpush
@endsection