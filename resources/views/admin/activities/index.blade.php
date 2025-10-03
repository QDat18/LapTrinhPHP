@extends('layouts.admin')

@section('title', 'Activities Management')
@section('breadcrumb', 'Activities')

@section('content')
<div class="space-y-6">
    
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Volunteer Activities</h2>
            <p class="text-gray-600 mt-1">Monitor and manage volunteer hours</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.activities.disputes') }}" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                <i class="fas fa-exclamation-triangle mr-2"></i>Disputes ({{ $disputeCount ?? 0 }})
            </a>
            <button onclick="exportActivities()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                <i class="fas fa-download mr-2"></i>Export
            </button>
        </div>
    </div>
    
    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total Hours</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_hours'] ?? 0) }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-blue-600"></i>
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
                    <i class="fas fa-hourglass-half text-yellow-600"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Disputed</p>
                    <p class="text-2xl font-bold text-red-600">{{ $stats['disputed'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-exclamation-circle text-red-600"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form action="{{ route('admin.activities.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                       placeholder="Search...">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="">All Status</option>
                    <option value="Verified">Verified</option>
                    <option value="Pending">Pending</option>
                    <option value="Disputed">Disputed</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            
            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    Apply
                </button>
                <a href="{{ route('admin.activities.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Reset
                </a>
            </div>
        </form>
    </div>
    
    <!-- Activities Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Volunteer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Opportunity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Organization</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hours</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($activities as $activity)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($activity->volunteer->first_name) }}" 
                                     class="w-8 h-8 rounded-full mr-3" alt="Avatar">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $activity->volunteer->first_name }} {{ $activity->volunteer->last_name }}
                                    </p>
                                    <p class="text-xs text-gray-500">{{ $activity->volunteer->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ Str::limit($activity->opportunity->title, 40) }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $activity->organization->organization_name }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($activity->activity_date)->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-semibold text-indigo-600">{{ $activity->hours_worked }}h</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 text-xs font-medium rounded-full
                                {{ $activity->status == 'Verified' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $activity->status == 'Pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $activity->status == 'Disputed' ? 'bg-red-100 text-red-800' : '' }}">
                                {{ $activity->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('admin.activities.show', $activity->activity_id) }}" 
                               class="text-indigo-600 hover:text-indigo-900">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <i class="fas fa-clock text-6xl text-gray-300 mb-4"></i>
                            <p class="text-lg font-medium text-gray-900">No activities found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($activities->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $activities->links() }}
        </div>
        @endif
    </div>
    
</div>

@push('scripts')
<script>
    function exportActivities() {
        const params = new URLSearchParams(window.location.search);
        window.location.href = '/admin/activities/export?' + params.toString();
    }
</script>
@endpush
@endsection