@extends('layouts.admin')

@section('title', 'Export Organizations')
@section('breadcrumb', 'Organizations / Export')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    
    <!-- Header -->
    <div class="text-center">
        <h2 class="text-2xl font-bold text-gray-900">Export Organizations Data</h2>
        <p class="text-gray-600 mt-2">Download organization data in CSV format</p>
    </div>
    
    <!-- Export Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
        
        <form action="{{ route('admin.organizations.export') }}" method="GET">
            
            <!-- Filters -->
            <div class="space-y-4 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Export Filters</h3>
                
                <div class="grid grid-cols-2 gap-4">
                    <!-- Verification Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Verification Status</label>
                        <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <option value="">All Status</option>
                            <option value="Verified">Verified</option>
                            <option value="Pending">Pending</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                    
                    <!-- Organization Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Organization Type</label>
                        <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <option value="">All Types</option>
                            <option value="NGO">NGO</option>
                            <option value="NPO">NPO</option>
                            <option value="Charity">Charity</option>
                            <option value="School">School</option>
                            <option value="Hospital">Hospital</option>
                            <option value="Community Group">Community Group</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Info Box -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex">
                    <i class="fas fa-info-circle text-blue-600 mt-0.5 mr-3"></i>
                    <div class="text-sm text-blue-800">
                        <p class="font-medium mb-1">Export will include:</p>
                        <ul class="list-disc list-inside space-y-1">
                            <li>Organization ID, Name, Type</li>
                            <li>Contact information (Email, Phone)</li>
                            <li>Verification status and dates</li>
                            <li>Statistics (Volunteers, Opportunities, Rating)</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.organizations.index') }}" 
                   class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    <i class="fas fa-download mr-2"></i>Download CSV
                </button>
            </div>
        </form>
        
    </div>
    
    <!-- Quick Export Buttons -->
    <div class="grid grid-cols-3 gap-4">
        <a href="{{ route('admin.organizations.export', ['status' => 'Verified']) }}" 
           class="p-4 border-2 border-gray-200 rounded-lg hover:border-green-500 hover:bg-green-50 transition text-center">
            <i class="fas fa-check-circle text-3xl text-green-600 mb-2"></i>
            <p class="font-medium text-gray-900">Verified Only</p>
            <p class="text-xs text-gray-500 mt-1">Export verified organizations</p>
        </a>
        
        <a href="{{ route('admin.organizations.export', ['status' => 'Pending']) }}" 
           class="p-4 border-2 border-gray-200 rounded-lg hover:border-yellow-500 hover:bg-yellow-50 transition text-center">
            <i class="fas fa-clock text-3xl text-yellow-600 mb-2"></i>
            <p class="font-medium text-gray-900">Pending Only</p>
            <p class="text-xs text-gray-500 mt-1">Export pending organizations</p>
        </a>
        
        <a href="{{ route('admin.organizations.export') }}" 
           class="p-4 border-2 border-gray-200 rounded-lg hover:border-indigo-500 hover:bg-indigo-50 transition text-center">
            <i class="fas fa-database text-3xl text-indigo-600 mb-2"></i>
            <p class="font-medium text-gray-900">Export All</p>
            <p class="text-xs text-gray-500 mt-1">Export all organizations</p>
        </a>
    </div>
    
</div>
@endsection