@extends('layouts.admin')

@section('title', 'Custom Reports')
@section('breadcrumb', 'Reports')

@section('content')
<div class="space-y-6">
    
    <!-- Header -->
    <div>
        <h2 class="text-3xl font-bold text-gray-900">Custom Reports</h2>
        <p class="text-gray-600 mt-1">Generate detailed reports with custom parameters</p>
    </div>
    
    <!-- Report Builder -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-xl font-semibold text-gray-900 mb-6">Report Builder</h3>
        
        <form action="{{ route('admin.analytics.custom-report') }}" method="POST" id="reportForm">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Report Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Report Type *</label>
                    <select name="report_type" id="reportType" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select Report Type</option>
                        <option value="users">Users Report</option>
                        <option value="opportunities">Opportunities Report</option>
                        <option value="applications">Applications Report</option>
                        <option value="activities">Activities Report</option>
                        <option value="organizations">Organizations Report</option>
                    </select>
                </div>
                
                <!-- Format -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Export Format *</label>
                    <select name="format" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="csv">CSV (Excel Compatible)</option>
                        <option value="excel">Excel (.xlsx)</option>
                        <option value="pdf">PDF Document</option>
                    </select>
                </div>
                
                <!-- Date Range -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Start Date *</label>
                    <input type="date" name="start_date" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">End Date *</label>
                    <input type="date" name="end_date" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            
            <!-- Dynamic Filters based on Report Type -->
            <div id="dynamicFilters" class="mb-6 p-4 bg-gray-50 rounded-lg hidden">
                <h4 class="font-medium text-gray-900 mb-4">Additional Filters</h4>
                <div id="filterContent" class="grid grid-cols-1 md:grid-cols-3 gap-4"></div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="resetForm()" 
                        class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Reset
                </button>
                <button type="submit" 
                        class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    <i class="fas fa-file-download mr-2"></i>Generate Report
                </button>
            </div>
        </form>
    </div>
    
    <!-- Quick Reports -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition cursor-pointer" 
             onclick="quickReport('users', '30days')">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
                <i class="fas fa-arrow-right text-gray-400"></i>
            </div>
            <h3 class="font-semibold text-gray-900 mb-2">User Growth Report</h3>
            <p class="text-sm text-gray-600">Last 30 days user registration data</p>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition cursor-pointer"
             onclick="quickReport('activities', '30days')">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-green-600 text-xl"></i>
                </div>
                <i class="fas fa-arrow-right text-gray-400"></i>
            </div>
            <h3 class="font-semibold text-gray-900 mb-2">Volunteer Hours Report</h3>
            <p class="text-sm text-gray-600">Monthly volunteer activity summary</p>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition cursor-pointer"
             onclick="quickReport('applications', '30days')">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-alt text-purple-600 text-xl"></i>
                </div>
                <i class="fas fa-arrow-right text-gray-400"></i>
            </div>
            <h3 class="font-semibold text-gray-900 mb-2">Application Statistics</h3>
            <p class="text-sm text-gray-600">Application trends and conversion rates</p>
        </div>
    </div>
    
    <!-- Recent Reports -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Recent Reports</h3>
        </div>
        <div class="divide-y divide-gray-200">
            <div class="px-6 py-4 hover:bg-gray-50 transition">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-file-csv text-blue-600"></i>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900">Users Report - October 2025</h4>
                            <p class="text-sm text-gray-500">Generated on Oct 4, 2025</p>
                        </div>
                    </div>
                    <button class="px-4 py-2 text-sm text-indigo-600 hover:bg-indigo-50 rounded-lg">
                        <i class="fas fa-download mr-2"></i>Download
                    </button>
                </div>
            </div>
            
            <div class="px-6 py-4 hover:bg-gray-50 transition">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-file-excel text-green-600"></i>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900">Activities Report - Q3 2025</h4>
                            <p class="text-sm text-gray-500">Generated on Sep 30, 2025</p>
                        </div>
                    </div>
                    <button class="px-4 py-2 text-sm text-indigo-600 hover:bg-indigo-50 rounded-lg">
                        <i class="fas fa-download mr-2"></i>Download
                    </button>
                </div>
            </div>
            
            <div class="px-6 py-4 hover:bg-gray-50 transition">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-file-pdf text-red-600"></i>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900">Impact Report - 2025</h4>
                            <p class="text-sm text-gray-500">Generated on Aug 15, 2025</p>
                        </div>
                    </div>
                    <button class="px-4 py-2 text-sm text-indigo-600 hover:bg-indigo-50 rounded-lg">
                        <i class="fas fa-download mr-2"></i>Download
                    </button>
                </div>
            </div>
        </div>
    </div>
    
</div>

@push('scripts')
<script>
const filterTemplates = {
    users: `
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">User Type</label>
            <select name="filters[user_type]" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">All Types</option>
                <option value="Volunteer">Volunteer</option>
                <option value="Organization">Organization</option>
                <option value="Admin">Admin</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select name="filters[is_active]" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">All Status</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>
    `,
    opportunities: `
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select name="filters[status]" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">All Status</option>
                <option value="Active">Active</option>
                <option value="Paused">Paused</option>
                <option value="Completed">Completed</option>
                <option value="Cancelled">Cancelled</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
            <select name="filters[category_id]" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">All Categories</option>
            </select>
        </div>
    `,
    applications: `
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select name="filters[status]" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">All Status</option>
                <option value="Pending">Pending</option>
                <option value="Accepted">Accepted</option>
                <option value="Rejected">Rejected</option>
                <option value="Under Review">Under Review</option>
            </select>
        </div>
    `,
    activities: `
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select name="filters[status]" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">All Status</option>
                <option value="Verified">Verified</option>
                <option value="Pending">Pending</option>
                <option value="Disputed">Disputed</option>
            </select>
        </div>
    `,
    organizations: `
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Verification Status</label>
            <select name="filters[verification_status]" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">All Status</option>
                <option value="Verified">Verified</option>
                <option value="Pending">Pending</option>
                <option value="Rejected">Rejected</option>
            </select>
        </div>
    `
};

document.getElementById('reportType').addEventListener('change', function() {
    const filterDiv = document.getElementById('dynamicFilters');
    const filterContent = document.getElementById('filterContent');
    
    if (this.value && filterTemplates[this.value]) {
        filterContent.innerHTML = filterTemplates[this.value];
        filterDiv.classList.remove('hidden');
    } else {
        filterDiv.classList.add('hidden');
    }
});

function resetForm() {
    document.getElementById('reportForm').reset();
    document.getElementById('dynamicFilters').classList.add('hidden');
}

function quickReport(type, period) {
    const endDate = new Date();
    const startDate = new Date();
    
    if (period === '30days') {
        startDate.setDate(startDate.getDate() - 30);
    }
    
    const form = document.getElementById('reportForm');
    form.querySelector('[name="report_type"]').value = type;
    form.querySelector('[name="start_date"]').value = startDate.toISOString().split('T')[0];
    form.querySelector('[name="end_date"]').value = endDate.toISOString().split('T')[0];
    
    // Trigger change event to show filters
    document.getElementById('reportType').dispatchEvent(new Event('change'));
    
    // Scroll to form
    form.scrollIntoView({ behavior: 'smooth' });
}
</script>
@endpush
@endsection