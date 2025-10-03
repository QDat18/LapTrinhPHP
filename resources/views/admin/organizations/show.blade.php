@extends('layouts.admin')

@section('title', 'Organization Details')
@section('breadcrumb', 'Organizations / Details')

@section('content')
<div class="space-y-6">
    
    <!-- Back Button -->
    <div>
        <a href="{{ route('admin.organizations.index') }}" 
           class="inline-flex items-center text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left mr-2"></i>Back to Organizations
        </a>
    </div>
    
    <!-- Organization Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <!-- Cover -->
        <div class="h-32 bg-gradient-to-r from-indigo-500 to-purple-600"></div>
        
        <!-- Profile Info -->
        <div class="px-8 pb-8">
            <div class="flex items-end justify-between -mt-16 mb-6">
                <div class="flex items-end space-x-4">
                    <img src="{{ $organization->logo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($organization->organization_name) }}" 
                         class="w-32 h-32 rounded-lg border-4 border-white shadow-lg bg-white" alt="Logo">
                    <div class="pb-2">
                        <h1 class="text-3xl font-bold text-gray-900">{{ $organization->organization_name }}</h1>
                        <div class="flex items-center space-x-4 mt-2">
                            <span class="px-3 py-1 text-sm font-medium rounded-full
                                {{ $organization->verification_status == 'Verified' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $organization->verification_status == 'Pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $organization->verification_status == 'Rejected' ? 'bg-red-100 text-red-800' : '' }}">
                                <i class="fas fa-{{ $organization->verification_status == 'Verified' ? 'check-circle' : ($organization->verification_status == 'Pending' ? 'clock' : 'times-circle') }} mr-1"></i>
                                {{ $organization->verification_status }}
                            </span>
                            <span class="px-3 py-1 text-sm font-medium bg-blue-100 text-blue-800 rounded-full">
                                {{ $organization->organization_type }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex space-x-2">
                    @if($organization->verification_status == 'Pending')
                    <button onclick="approveOrg({{ $organization->org_id }})" 
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                        <i class="fas fa-check mr-2"></i>Approve
                    </button>
                    <button onclick="rejectOrg({{ $organization->org_id }})" 
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                        <i class="fas fa-times mr-2"></i>Reject
                    </button>
                    @endif
                    <button onclick="deleteOrg({{ $organization->org_id }})" 
                            class="px-4 py-2 border border-red-300 text-red-600 rounded-lg hover:bg-red-50 transition">
                        <i class="fas fa-trash mr-2"></i>Delete
                    </button>
                </div>
            </div>
            
            <!-- Stats Grid -->
            <div class="grid grid-cols-4 gap-4 mb-6">
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-2xl font-bold text-gray-900">{{ $organization->volunteer_count }}</div>
                    <div class="text-sm text-gray-600">Volunteers</div>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-2xl font-bold text-gray-900">{{ $organization->total_opportunities }}</div>
                    <div class="text-sm text-gray-600">Opportunities</div>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-2xl font-bold text-gray-900">{{ number_format($organization->rating, 1) }}</div>
                    <div class="text-sm text-gray-600">Rating</div>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-2xl font-bold text-gray-900">{{ $organization->founded_year ?? 'N/A' }}</div>
                    <div class="text-sm text-gray-600">Founded</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Column - Main Info -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- About -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">About</h3>
                @if($organization->description)
                <p class="text-gray-700 leading-relaxed mb-4">{{ $organization->description }}</p>
                @else
                <p class="text-gray-500 italic">No description provided</p>
                @endif
                
                @if($organization->mission_statement)
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <h4 class="text-sm font-semibold text-gray-700 mb-2">Mission Statement</h4>
                    <p class="text-gray-700 italic">{{ $organization->mission_statement }}</p>
                </div>
                @endif
            </div>
            
            <!-- Recent Opportunities -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Opportunities</h3>
                    <a href="{{ route('admin.opportunities.index', ['organization' => $organization->organization_name]) }}" 
                       class="text-sm text-indigo-600 hover:text-indigo-800">
                        View All <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                
                <div class="space-y-3">
                    @forelse($organization->opportunities()->latest()->take(5)->get() as $opp)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                        <div class="flex-1">
                            <h4 class="font-medium text-gray-900">{{ Str::limit($opp->title, 50) }}</h4>
                            <div class="flex items-center space-x-3 mt-1 text-xs text-gray-500">
                                <span><i class="fas fa-calendar mr-1"></i>{{ $opp->start_date->format('M d, Y') }}</span>
                                <span><i class="fas fa-users mr-1"></i>{{ $opp->application_count }} applications</span>
                                <span class="px-2 py-0.5 rounded-full text-xs
                                    {{ $opp->status == 'Active' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $opp->status == 'Paused' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $opp->status == 'Completed' ? 'bg-gray-100 text-gray-800' : '' }}">
                                    {{ $opp->status }}
                                </span>
                            </div>
                        </div>
                        <a href="{{ route('admin.opportunities.show', $opp->opportunity_id) }}" 
                           class="ml-3 text-indigo-600 hover:text-indigo-800">
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    @empty
                    <p class="text-gray-500 text-center py-4">No opportunities posted yet</p>
                    @endforelse
                </div>
            </div>
            
            <!-- Activity Log -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Activity Log</h3>
                <div class="space-y-3">
                    <div class="flex items-start">
                        <div class="w-2 h-2 bg-green-500 rounded-full mt-2 mr-3"></div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-900">Organization registered</p>
                            <p class="text-xs text-gray-500">{{ $organization->created_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                    @if($organization->verification_status == 'Verified')
                    <div class="flex items-start">
                        <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 mr-3"></div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-900">Organization verified</p>
                            <p class="text-xs text-gray-500">{{ $organization->updated_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            
        </div>
        
        <!-- Right Column - Additional Info -->
        <div class="space-y-6">
            
            <!-- Contact Information -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Contact Information</h3>
                
                <div class="space-y-3">
                    @if($organization->contact_person)
                    <div class="flex items-center text-gray-700">
                        <i class="fas fa-user w-5 mr-3 text-gray-400"></i>
                        <div>
                            <p class="text-xs text-gray-500">Contact Person</p>
                            <p class="text-sm font-medium">{{ $organization->contact_person }}</p>
                        </div>
                    </div>
                    @endif
                    
                    <div class="flex items-center text-gray-700">
                        <i class="fas fa-envelope w-5 mr-3 text-gray-400"></i>
                        <div>
                            <p class="text-xs text-gray-500">Email</p>
                            <p class="text-sm font-medium">{{ $organization->user->email }}</p>
                        </div>
                    </div>
                    
                    @if($organization->user->phone)
                    <div class="flex items-center text-gray-700">
                        <i class="fas fa-phone w-5 mr-3 text-gray-400"></i>
                        <div>
                            <p class="text-xs text-gray-500">Phone</p>
                            <p class="text-sm font-medium">{{ $organization->user->phone }}</p>
                        </div>
                    </div>
                    @endif
                    
                    @if($organization->website)
                    <div class="flex items-center text-gray-700">
                        <i class="fas fa-globe w-5 mr-3 text-gray-400"></i>
                        <div>
                            <p class="text-xs text-gray-500">Website</p>
                            <a href="{{ $organization->website }}" target="_blank" 
                               class="text-sm font-medium text-indigo-600 hover:underline">
                                {{ $organization->website }}
                            </a>
                        </div>
                    </div>
                    @endif
                    
                    @if($organization->user->address)
                    <div class="flex items-start text-gray-700">
                        <i class="fas fa-map-marker-alt w-5 mr-3 text-gray-400 mt-1"></i>
                        <div>
                            <p class="text-xs text-gray-500">Address</p>
                            <p class="text-sm font-medium">{{ $organization->user->address }}</p>
                            <p class="text-xs text-gray-500">{{ $organization->user->city }}, {{ $organization->user->district }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- Organization Details -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Organization Details</h3>
                
                <div class="space-y-3">
                    @if($organization->registration_number)
                    <div>
                        <p class="text-xs text-gray-500">Registration Number</p>
                        <p class="text-sm font-medium text-gray-900">{{ $organization->registration_number }}</p>
                    </div>
                    @endif
                    
                    @if($organization->founded_year)
                    <div>
                        <p class="text-xs text-gray-500">Founded Year</p>
                        <p class="text-sm font-medium text-gray-900">{{ $organization->founded_year }}</p>
                    </div>
                    @endif
                    
                    <div>
                        <p class="text-xs text-gray-500">Organization Type</p>
                        <p class="text-sm font-medium text-gray-900">{{ $organization->organization_type }}</p>
                    </div>
                    
                    <div>
                        <p class="text-xs text-gray-500">Member Since</p>
                        <p class="text-sm font-medium text-gray-900">{{ $organization->created_at->format('M d, Y') }}</p>
                    </div>
                    
                    <div>
                        <p class="text-xs text-gray-500">Last Updated</p>
                        <p class="text-sm font-medium text-gray-900">{{ $organization->updated_at->diffForHumans() }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Account Status -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Account Status</h3>
                
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700">Account Status</span>
                        <span class="px-2 py-1 text-xs font-medium rounded-full
                            {{ $organization->user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $organization->user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700">Verification</span>
                        <span class="px-2 py-1 text-xs font-medium rounded-full
                            {{ $organization->verification_status == 'Verified' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $organization->verification_status == 'Pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $organization->verification_status == 'Rejected' ? 'bg-red-100 text-red-800' : '' }}">
                            {{ $organization->verification_status }}
                        </span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700">Email Verified</span>
                        <span class="px-2 py-1 text-xs font-medium rounded-full
                            {{ $organization->user->is_verified ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $organization->user->is_verified ? 'Verified' : 'Not Verified' }}
                        </span>
                    </div>
                    
                    @if($organization->user->last_login_at)
                    <div>
                        <p class="text-xs text-gray-500">Last Login</p>
                        <p class="text-sm font-medium text-gray-900">{{ $organization->user->last_login_at->diffForHumans() }}</p>
                    </div>
                    @endif
                </div>
            </div>
            
        </div>
        
    </div>
    
</div>

@push('scripts')
<script>
function approveOrg(orgId) {
    if (confirm('Are you sure you want to approve this organization?')) {
        fetch(`/admin/organizations/${orgId}/approve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Organization approved successfully', 'success');
                setTimeout(() => location.reload(), 1000);
            }
        })
        .catch(() => showToast('An error occurred', 'error'));
    }
}

function rejectOrg(orgId) {
    if (confirm('Are you sure you want to reject this organization?')) {
        fetch(`/admin/organizations/${orgId}/reject`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Organization rejected', 'success');
                setTimeout(() => location.reload(), 1000);
            }
        })
        .catch(() => showToast('An error occurred', 'error'));
    }
}

function deleteOrg(orgId) {
    if (confirm('Are you sure you want to delete this organization? This action cannot be undone.')) {
        fetch(`/admin/organizations/${orgId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Organization deleted successfully', 'success');
                setTimeout(() => window.location.href = '/admin/organizations', 1000);
            }
        })
        .catch(() => showToast('An error occurred', 'error'));
    }
}

function showToast(message, type) {
    // Simple alert for now - you can replace with a proper toast notification
    alert(message);
}
</script>
@endpush
@endsection