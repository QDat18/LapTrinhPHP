@extends('layouts.admin')

@section('title', 'Organizations Management')
@section('breadcrumb', 'Organizations')

@section('content')
<div class="space-y-6">
    
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Organizations Management</h2>
            <p class="text-gray-600 mt-1">Manage and verify organizations</p>
        </div>
        <div class="flex space-x-3">
            <button onclick="openEmailModal('organizations')" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                <i class="fas fa-envelope mr-2"></i>Email All Orgs
            </button>
            <button onclick="exportOrgs()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                <i class="fas fa-download mr-2"></i>Export
            </button>
        </div>
    </div>
    
    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-building text-blue-600"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Verified</p>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['verified'] ?? 0 }}</p>
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
                    <p class="text-sm text-gray-600">Rejected</p>
                    <p class="text-2xl font-bold text-red-600">{{ $stats['rejected'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-times-circle text-red-600"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form action="{{ route('admin.organizations.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                       placeholder="Search organizations...">
            </div>
            
            <!-- Verification Status -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="">All Status</option>
                    <option value="Verified" {{ request('status') == 'Verified' ? 'selected' : '' }}>Verified</option>
                    <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                    <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            
            <!-- Organization Type -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="">All Types</option>
                    <option value="NGO">NGO</option>
                    <option value="NPO">NPO</option>
                    <option value="Charity">Charity</option>
                    <option value="School">School</option>
                    <option value="Hospital">Hospital</option>
                </select>
            </div>
            
            <div class="md:col-span-4 flex justify-end space-x-2">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    <i class="fas fa-filter mr-2"></i>Apply
                </button>
                <a href="{{ route('admin.organizations.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Reset
                </a>
            </div>
        </form>
    </div>
    
    <!-- Organizations Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($organizations as $org)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition">
            <div class="p-6">
                <!-- Header -->
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <img src="{{ $org->logo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($org->organization_name) }}" 
                             class="w-12 h-12 rounded-full" alt="Logo">
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ Str::limit($org->organization_name, 30) }}</h3>
                            <p class="text-xs text-gray-500">{{ $org->organization_type }}</p>
                        </div>
                    </div>
                    <span class="px-2 py-1 text-xs font-medium rounded-full
                        {{ $org->verification_status == 'Verified' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $org->verification_status == 'Pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ $org->verification_status == 'Rejected' ? 'bg-red-100 text-red-800' : '' }}">
                        {{ $org->verification_status }}
                    </span>
                </div>
                
                <!-- Info -->
                <div class="space-y-2 mb-4">
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-star w-4 mr-2 text-yellow-500"></i>
                        <span>{{ number_format($org->rating, 1) }} Rating</span>
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-users w-4 mr-2 text-blue-500"></i>
                        <span>{{ $org->volunteer_count }} Volunteers</span>
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-clipboard-list w-4 mr-2 text-green-500"></i>
                        <span>{{ $org->total_opportunities }} Opportunities</span>
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-calendar w-4 mr-2 text-purple-500"></i>
                        <span>Joined {{ $org->created_at->format('M Y') }}</span>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="flex space-x-2 pt-4 border-t border-gray-200">
                    <a href="{{ route('admin.organizations.show', $org->org_id) }}" 
                       class="flex-1 px-3 py-2 text-center text-sm bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100">
                        <i class="fas fa-eye mr-1"></i>View
                    </a>
                    
                    @if($org->verification_status == 'Pending')
                        <button onclick="verifyOrg({{ $org->org_id }}, 'approve')" 
                                class="flex-1 px-3 py-2 text-center text-sm bg-green-50 text-green-600 rounded-lg hover:bg-green-100">
                            <i class="fas fa-check mr-1"></i>Approve
                        </button>
                        <button onclick="verifyOrg({{ $org->org_id }}, 'reject')" 
                                class="flex-1 px-3 py-2 text-center text-sm bg-red-50 text-red-600 rounded-lg hover:bg-red-100">
                            <i class="fas fa-times mr-1"></i>Reject
                        </button>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="md:col-span-3 bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
            <i class="fas fa-building text-6xl text-gray-300 mb-4"></i>
            <p class="text-lg font-medium text-gray-900">No organizations found</p>
            <p class="text-gray-500 mt-1">Try adjusting your filters</p>
        </div>
        @endforelse
    </div>
    
    <!-- Pagination -->
    @if($organizations->hasPages())
    <div class="flex justify-center">
        {{ $organizations->links() }}
    </div>
    @endif
    
</div>

@push('scripts')
<script>
    function verifyOrg(orgId, action) {
        const actionText = action === 'approve' ? 'approve' : 'reject';
        if (confirm(`Are you sure you want to ${actionText} this organization?`)) {
            fetch(`/admin/organizations/${orgId}/${action}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(`Organization ${actionText}ed successfully`, 'success');
                    setTimeout(() => location.reload(), 1000);
                }
            })
            .catch(() => showToast('An error occurred', 'error'));
        }
    }
    
    function exportOrgs() {
        const params = new URLSearchParams(window.location.search);
        window.location.href = '/admin/organizations/export?' + params.toString();
    }
</script>
@endpush
@endsection