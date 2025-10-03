@extends('admin.layout')

@section('title', 'Opportunities Management')
@section('breadcrumb', 'Opportunities')

@section('content')
<div class="space-y-6">
    
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Opportunities Management</h1>
            <p class="text-sm text-gray-600 mt-1">Manage volunteer opportunities</p>
        </div>
        <div class="flex space-x-3">
            <button onclick="exportOpportunities()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                <i class="fas fa-file-excel mr-2"></i> Export
            </button>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clipboard-list text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Active</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['active'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Paused</p>
                    <p class="text-2xl font-bold text-yellow-600 mt-1">{{ $stats['paused'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-pause-circle text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Completed</p>
                    <p class="text-2xl font-bold text-indigo-600 mt-1">{{ $stats['completed'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-double text-indigo-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Cancelled</p>
                    <p class="text-2xl font-bold text-red-600 mt-1">{{ $stats['cancelled'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form method="GET" action="{{ route('admin.opportunities.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Title, location..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Status</option>
                    <option value="Active" {{ request('status') == 'Active' ? 'selected' : '' }}>Active</option>
                    <option value="Paused" {{ request('status') == 'Paused' ? 'selected' : '' }}>Paused</option>
                    <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                    <option value="Cancelled" {{ request('status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                <select name="category" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Categories</option>
                    @foreach($categories ?? [] as $category)
                    <option value="{{ $category->category_id }}" {{ request('category') == $category->category_id ? 'selected' : '' }}>
                        {{ $category->category_name }}
                    </option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Organization</label>
                <input type="text" name="organization" value="{{ request('organization') }}" 
                       placeholder="Organization name..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            
            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    <i class="fas fa-search mr-2"></i> Filter
                </button>
                <a href="{{ route('admin.opportunities.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
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
                            Opportunity
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Organization
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Category
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Applications
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Date
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($opportunities as $opp)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ Str::limit($opp->title, 50) }}</p>
                                <p class="text-sm text-gray-500">
                                    <i class="fas fa-map-marker-alt mr-1"></i>{{ $opp->location }}
                                </p>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $opp->organization->organization_name }}
                        </td>
                        <td class="px-6 py-4">
                            @if($opp->category)
                            <span class="px-3 py-1 text-xs font-medium rounded-full" 
                                  style="background-color: {{ $opp->category->color }}20; color: {{ $opp->category->color }}">
                                <i class="{{ $opp->category->icon }} mr-1"></i>
                                {{ $opp->category->category_name }}
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 text-xs font-medium rounded-full 
                                {{ $opp->status == 'Active' ? 'bg-green-100 text-green-800' : 
                                   ($opp->status == 'Paused' ? 'bg-yellow-100 text-yellow-800' : 
                                   ($opp->status == 'Completed' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800')) }}">
                                {{ $opp->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <div class="flex items-center">
                                <span class="font-medium text-gray-900">{{ $opp->application_count }}</span>
                                <span class="text-gray-500 mx-1">/</span>
                                <span class="text-gray-500">{{ $opp->volunteers_needed }}</span>
                                @if($opp->application_count >= $opp->volunteers_needed)
                                <span class="ml-2 text-green-600">
                                    <i class="fas fa-check-circle"></i>
                                </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $opp->start_date->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-2">
                                <button onclick="viewOpportunity({{ $opp->opportunity_id }})" 
                                        class="text-indigo-600 hover:text-indigo-900" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <a href="{{ route('opportunities.show', $opp->opportunity_id) }}" target="_blank"
                                   class="text-blue-600 hover:text-blue-900" title="View Public Page">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                                <button onclick="changeStatus({{ $opp->opportunity_id }})" 
                                        class="text-yellow-600 hover:text-yellow-900" title="Change Status">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteOpportunity({{ $opp->opportunity_id }})" 
                                        class="text-red-600 hover:text-red-900" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-clipboard-list text-4xl mb-4 text-gray-300"></i>
                            <p class="text-lg font-medium">No opportunities found</p>
                            <p class="text-sm mt-2">Try adjusting your search or filter criteria</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($opportunities->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $opportunities->links() }}
        </div>
        @endif
        
    </div>
    
</div>

<!-- View Details Modal -->
<div id="viewModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Opportunity Details</h3>
            <button onclick="closeViewModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div id="opportunityDetails" class="p-6">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>

<!-- Change Status Modal -->
<div id="statusModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Change Opportunity Status</h3>
        </div>
        
        <form id="statusForm" class="p-6">
            <input type="hidden" id="statusOppId">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">New Status</label>
                <select id="newStatus" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="Active">Active</option>
                    <option value="Paused">Paused</option>
                    <option value="Completed">Completed</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>
            
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="closeStatusModal()" 
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    Update Status
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function viewOpportunity(oppId) {
        fetch(`/admin/opportunities/${oppId}`)
            .then(response => response.json())
            .then(opp => {
                const html = `
                    <div class="space-y-6">
                        <div class="grid grid-cols-2 gap-6">
                            <div class="col-span-2">
                                <h4 class="text-xl font-bold text-gray-900">${opp.title}</h4>
                                <p class="text-gray-600 mt-1">${opp.organization.organization_name}</p>
                            </div>
                            
                            <div>
                                <label class="text-sm font-medium text-gray-700">Category</label>
                                <p class="mt-1">${opp.category ? opp.category.category_name : 'N/A'}</p>
                            </div>
                            
                            <div>
                                <label class="text-sm font-medium text-gray-700">Status</label>
                                <p class="mt-1">
                                    <span class="px-3 py-1 text-xs font-medium rounded-full ${
                                        opp.status === 'Active' ? 'bg-green-100 text-green-800' : 
                                        opp.status === 'Paused' ? 'bg-yellow-100 text-yellow-800' : 
                                        opp.status === 'Completed' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800'
                                    }">
                                        ${opp.status}
                                    </span>
                                </p>
                            </div>
                            
                            <div>
                                <label class="text-sm font-medium text-gray-700">Location</label>
                                <p class="mt-1">${opp.location}</p>
                            </div>
                            
                            <div>
                                <label class="text-sm font-medium text-gray-700">Time Commitment</label>
                                <p class="mt-1">${opp.time_commitment}</p>
                            </div>
                            
                            <div>
                                <label class="text-sm font-medium text-gray-700">Schedule Type</label>
                                <p class="mt-1">${opp.schedule_type}</p>
                            </div>
                            
                            <div>
                                <label class="text-sm font-medium text-gray-700">Experience Needed</label>
                                <p class="mt-1">${opp.experience_needed}</p>
                            </div>
                            
                            <div>
                                <label class="text-sm font-medium text-gray-700">Start Date</label>
                                <p class="mt-1">${new Date(opp.start_date).toLocaleDateString()}</p>
                            </div>
                            
                            <div>
                                <label class="text-sm font-medium text-gray-700">End Date</label>
                                <p class="mt-1">${opp.end_date ? new Date(opp.end_date).toLocaleDateString() : 'Ongoing'}</p>
                            </div>
                            
                            <div>
                                <label class="text-sm font-medium text-gray-700">Application Deadline</label>
                                <p class="mt-1">${new Date(opp.application_deadline).toLocaleDateString()}</p>
                            </div>
                            
                            <div>
                                <label class="text-sm font-medium text-gray-700">Minimum Age</label>
                                <p class="mt-1">${opp.min_age} years</p>
                            </div>
                        </div>
                        
                        <div>
                            <label class="text-sm font-medium text-gray-700">Description</label>
                            <p class="mt-1 text-gray-900">${opp.description}</p>
                        </div>
                        
                        ${opp.requirements ? `
                        <div>
                            <label class="text-sm font-medium text-gray-700">Requirements</label>
                            <p class="mt-1 text-gray-900">${opp.requirements}</p>
                        </div>
                        ` : ''}
                        
                        ${opp.benefits ? `
                        <div>
                            <label class="text-sm font-medium text-gray-700">Benefits</label>
                            <p class="mt-1 text-gray-900">${opp.benefits}</p>
                        </div>
                        ` : ''}
                        
                        <div class="grid grid-cols-4 gap-4 pt-4 border-t">
                            <div class="text-center">
                                <p class="text-2xl font-bold text-indigo-600">${opp.volunteers_needed}</p>
                                <p class="text-sm text-gray-600">Volunteers Needed</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-green-600">${opp.volunteers_registered || 0}</p>
                                <p class="text-sm text-gray-600">Registered</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-blue-600">${opp.application_count}</p>
                                <p class="text-sm text-gray-600">Applications</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-purple-600">${opp.view_count}</p>
                                <p class="text-sm text-gray-600">Views</p>
                            </div>
                        </div>
                    </div>
                `;
                document.getElementById('opportunityDetails').innerHTML = html;
                document.getElementById('viewModal').classList.remove('hidden');
            });
    }
    
    function closeViewModal() {
        document.getElementById('viewModal').classList.add('hidden');
    }
    
    function changeStatus(oppId) {
        document.getElementById('statusOppId').value = oppId;
        document.getElementById('statusModal').classList.remove('hidden');
    }
    
    function closeStatusModal() {
        document.getElementById('statusModal').classList.add('hidden');
    }
    
    document.getElementById('statusForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const oppId = document.getElementById('statusOppId').value;
        const newStatus = document.getElementById('newStatus').value;
        
        fetch(`/admin/opportunities/${oppId}/status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ status: newStatus })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Status updated successfully', 'success');
                closeStatusModal();
                setTimeout(() => location.reload(), 1000);
            }
        });
    });
    
    function deleteOpportunity(oppId) {
        if (!confirm('Are you sure you want to delete this opportunity? This action cannot be undone.')) return;
        
        fetch(`/admin/opportunities/${oppId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Opportunity deleted successfully', 'success');
                setTimeout(() => location.reload(), 1000);
            }
        });
    }
    
    function exportOpportunities() {
        window.location.href = '{{ route("admin.opportunities.export") }}';
    }
</script>
@endpush
@endsection