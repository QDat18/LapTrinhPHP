@extends('admin.layout')

@section('title', 'Organizations Management')
@section('breadcrumb', 'Organizations')

@section('content')
<div class="space-y-6">
    
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Organizations Management</h1>
            <p class="text-sm text-gray-600 mt-1">Manage and verify organizations</p>
        </div>
        <div class="flex space-x-3">
            <button onclick="exportOrganizations()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                <i class="fas fa-file-excel mr-2"></i> Export
            </button>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-building text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Verified</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['verified'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Pending</p>
                    <p class="text-2xl font-bold text-yellow-600 mt-1">{{ $stats['pending'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Rejected</p>
                    <p class="text-2xl font-bold text-red-600 mt-1">{{ $stats['rejected'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form method="GET" action="{{ route('admin.organizations.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Organization name..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Status</option>
                    <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                    <option value="Verified" {{ request('status') == 'Verified' ? 'selected' : '' }}>Verified</option>
                    <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Types</option>
                    <option value="NGO" {{ request('type') == 'NGO' ? 'selected' : '' }}>NGO</option>
                    <option value="NPO" {{ request('type') == 'NPO' ? 'selected' : '' }}>NPO</option>
                    <option value="Charity" {{ request('type') == 'Charity' ? 'selected' : '' }}>Charity</option>
                    <option value="School" {{ request('type') == 'School' ? 'selected' : '' }}>School</option>
                    <option value="Hospital" {{ request('type') == 'Hospital' ? 'selected' : '' }}>Hospital</option>
                </select>
            </div>
            
            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    <i class="fas fa-search mr-2"></i> Filter
                </button>
                <a href="{{ route('admin.organizations.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                    <i class="fas fa-redo"></i>
                </a>
            </div>
            
        </form>
    </div>
    
    <!-- Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Organization
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Type
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Contact
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Opportunities
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Registered
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($organizations as $org)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center text-white font-bold">
                                    {{ strtoupper(substr($org->organization_name, 0, 2)) }}
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $org->organization_name }}</p>
                                    <p class="text-sm text-gray-500">{{ $org->user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800">
                                {{ $org->organization_type }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <div>{{ $org->contact_person ?? 'N/A' }}</div>
                            <div class="text-gray-500">{{ $org->user->phone }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 text-xs font-medium rounded-full 
                                {{ $org->verification_status == 'Verified' ? 'bg-green-100 text-green-800' : 
                                   ($org->verification_status == 'Pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ $org->verification_status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $org->total_opportunities }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $org->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-2">
                                <button onclick="viewOrganization({{ $org->org_id }})" 
                                        class="text-indigo-600 hover:text-indigo-900" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                
                                @if($org->verification_status == 'Pending')
                                <button onclick="approveOrganization({{ $org->org_id }})" 
                                        class="text-green-600 hover:text-green-900" title="Approve">
                                    <i class="fas fa-check-circle"></i>
                                </button>
                                <button onclick="rejectOrganization({{ $org->org_id }})" 
                                        class="text-red-600 hover:text-red-900" title="Reject">
                                    <i class="fas fa-times-circle"></i>
                                </button>
                                @endif
                                
                                <button onclick="deleteOrganization({{ $org->org_id }})" 
                                        class="text-red-600 hover:text-red-900" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-building text-4xl mb-4 text-gray-300"></i>
                            <p class="text-lg font-medium">No organizations found</p>
                            <p class="text-sm mt-2">Try adjusting your search or filter criteria</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($organizations->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $organizations->links() }}
        </div>
        @endif
        
    </div>
    
</div>

<!-- View Details Modal -->
<div id="viewModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Organization Details</h3>
            <button onclick="closeViewModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div id="organizationDetails" class="p-6">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>

@push('scripts')
<script>
    function viewOrganization(orgId) {
        fetch(`/admin/organizations/${orgId}`)
            .then(response => response.json())
            .then(org => {
                const html = `
                    <div class="space-y-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-medium text-gray-700">Organization Name</label>
                                <p class="mt-1 text-gray-900">${org.organization_name}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">Type</label>
                                <p class="mt-1 text-gray-900">${org.organization_type}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">Status</label>
                                <p class="mt-1">
                                    <span class="px-3 py-1 text-xs font-medium rounded-full ${
                                        org.verification_status === 'Verified' ? 'bg-green-100 text-green-800' : 
                                        org.verification_status === 'Pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'
                                    }">
                                        ${org.verification_status}
                                    </span>
                                </p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">Contact Person</label>
                                <p class="mt-1 text-gray-900">${org.contact_person || 'N/A'}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">Email</label>
                                <p class="mt-1 text-gray-900">${org.user.email}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">Phone</label>
                                <p class="mt-1 text-gray-900">${org.user.phone}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">Website</label>
                                <p class="mt-1 text-gray-900">${org.website || 'N/A'}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">Founded Year</label>
                                <p class="mt-1 text-gray-900">${org.founded_year || 'N/A'}</p>
                            </div>
                        </div>
                        
                        <div>
                            <label class="text-sm font-medium text-gray-700">Description</label>
                            <p class="mt-1 text-gray-900">${org.description || 'No description'}</p>
                        </div>
                        
                        <div>
                            <label class="text-sm font-medium text-gray-700">Mission Statement</label>
                            <p class="mt-1 text-gray-900">${org.mission_statement || 'No mission statement'}</p>
                        </div>
                        
                        <div class="grid grid-cols-3 gap-4 pt-4 border-t">
                            <div class="text-center">
                                <p class="text-2xl font-bold text-indigo-600">${org.total_opportunities}</p>
                                <p class="text-sm text-gray-600">Opportunities</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-green-600">${org.volunteer_count}</p>
                                <p class="text-sm text-gray-600">Volunteers</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-yellow-600">${org.rating}</p>
                                <p class="text-sm text-gray-600">Rating</p>
                            </div>
                        </div>
                    </div>
                `;
                document.getElementById('organizationDetails').innerHTML = html;
                document.getElementById('viewModal').classList.remove('hidden');
            });
    }
    
    function closeViewModal() {
        document.getElementById('viewModal').classList.add('hidden');
    }
    
    function approveOrganization(orgId) {
        if (!confirm('Are you sure you want to approve this organization?')) return;
        
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
        });
    }
    
    function rejectOrganization(orgId) {
        if (!confirm('Are you sure you want to reject this organization?')) return;
        
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
        });
    }
    
    function deleteOrganization(orgId) {
        if (!confirm('Are you sure you want to delete this organization? This action cannot be undone.')) return;
        
        fetch(`/admin/organizations/${orgId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Organization deleted successfully', 'success');
                setTimeout(() => location.reload(), 1000);
            }
        });
    }
    
    function exportOrganizations() {
        window.location.href = '{{ route("admin.organizations.export") }}';
    }
</script>
@endpush
@endsection