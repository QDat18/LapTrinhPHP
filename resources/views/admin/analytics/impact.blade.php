@extends('layouts.admin')

@section('title', 'Impact Report')
@section('breadcrumb', 'Analytics / Impact')

@section('content')
<div class="space-y-6">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Social Impact Report</h2>
            <p class="text-gray-600 mt-1">Measuring our collective contribution to society</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <form method="GET" class="flex gap-2">
                <input type="date" name="start_date" value="{{ $startDate }}" 
                       class="px-4 py-2 border border-gray-300 rounded-lg">
                <input type="date" name="end_date" value="{{ $endDate }}" 
                       class="px-4 py-2 border border-gray-300 rounded-lg">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
            </form>
            <button onclick="window.print()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                <i class="fas fa-print mr-2"></i>Print
            </button>
        </div>
    </div>
    
    <!-- Hero Impact Stats -->
    <div class="bg-gradient-to-br from-indigo-600 to-purple-700 rounded-xl shadow-lg p-8 text-white">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="text-center">
                <p class="text-5xl font-bold mb-2">{{ number_format($impact['total_volunteer_hours']) }}</p>
                <p class="text-indigo-100">Total Hours Contributed</p>
            </div>
            <div class="text-center">
                <p class="text-5xl font-bold mb-2">{{ number_format($impact['total_volunteers']) }}</p>
                <p class="text-indigo-100">Active Volunteers</p>
            </div>
            <div class="text-center">
                <p class="text-5xl font-bold mb-2">{{ number_format($impact['total_organizations']) }}</p>
                <p class="text-indigo-100">Partner Organizations</p>
            </div>
            <div class="text-center">
                <p class="text-5xl font-bold mb-2">{{ number_format($impact['total_activities']) }}</p>
                <p class="text-indigo-100">Activities Completed</p>
            </div>
        </div>
    </div>
    
    <!-- Economic Value -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                <i class="fas fa-dollar-sign text-green-600 text-2xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Estimated Economic Value</h3>
            <p class="text-4xl font-bold text-green-600 mb-2">
                {{ number_format($impact['economic_value']) }} VNĐ
            </p>
            <p class="text-sm text-gray-600">Based on average hourly rate of 50,000 VNĐ</p>
        </div>
    </div>
    
    <!-- Impact by Category -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Impact by Category</h3>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Chart -->
            <div>
                <canvas id="impactByCategoryChart" height="300"></canvas>
            </div>
            
            <!-- Details -->
            <div class="space-y-4">
                @foreach($impactByCategory as $category)
                <div class="p-4 border border-gray-200 rounded-lg hover:border-indigo-300 transition">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-semibold text-gray-900">{{ $category->category_name }}</h4>
                        <span class="text-2xl font-bold text-indigo-600">{{ number_format($category->total_hours) }}h</span>
                    </div>
                    <div class="flex items-center justify-between text-sm text-gray-600">
                        <span><i class="fas fa-users mr-1"></i>{{ $category->volunteer_count }} volunteers</span>
                        <span>{{ number_format(($category->total_hours / max($impact['total_volunteer_hours'], 1)) * 100, 1) }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-3">
                        <div class="bg-indigo-600 h-2 rounded-full" 
                             style="width: {{ ($category->total_hours / max($impact['total_volunteer_hours'], 1)) * 100 }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    
    <!-- Geographic Distribution -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Geographic Distribution</h3>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            @foreach($geographicDistribution as $location)
            <div class="p-4 border border-gray-200 rounded-lg text-center hover:border-indigo-300 hover:shadow-md transition">
                <i class="fas fa-map-marker-alt text-3xl text-indigo-600 mb-3"></i>
                <h4 class="font-semibold text-gray-900 mb-2">{{ $location->city }}</h4>
                <p class="text-2xl font-bold text-indigo-600 mb-1">{{ number_format($location->total_hours) }}</p>
                <p class="text-sm text-gray-600">hours contributed</p>
                <p class="text-xs text-gray-500 mt-2">{{ $location->volunteer_count }} volunteers</p>
            </div>
            @endforeach
        </div>
    </div>
    
    <!-- Stories of Impact -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Key Highlights</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="p-6 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-graduation-cap text-blue-600 text-xl"></i>
                </div>
                <h4 class="font-semibold text-gray-900 mb-2">Education Impact</h4>
                <p class="text-3xl font-bold text-blue-600 mb-2">
                    {{ number_format($impactByCategory->where('category_name', 'Education')->first()->total_hours ?? 0) }}
                </p>
                <p class="text-sm text-gray-600">hours dedicated to education initiatives</p>
            </div>
            
            <div class="p-6 bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-leaf text-green-600 text-xl"></i>
                </div>
                <h4 class="font-semibold text-gray-900 mb-2">Environment</h4>
                <p class="text-3xl font-bold text-green-600 mb-2">
                    {{ number_format($impactByCategory->where('category_name', 'Environment')->first()->total_hours ?? 0) }}
                </p>
                <p class="text-sm text-gray-600">hours protecting our planet</p>
            </div>
            
            <div class="p-6 bg-gradient-to-br from-red-50 to-pink-50 rounded-lg">
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-heartbeat text-red-600 text-xl"></i>
                </div>
                <h4 class="font-semibold text-gray-900 mb-2">Healthcare</h4>
                <p class="text-3xl font-bold text-red-600 mb-2">
                    {{ number_format($impactByCategory->where('category_name', 'Healthcare')->first()->total_hours ?? 0) }}
                </p>
                <p class="text-sm text-gray-600">hours supporting health initiatives</p>
            </div>
        </div>
    </div>
    
    <!-- Summary Statement -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-lg shadow-lg p-8 text-white text-center">
        <i class="fas fa-heart text-5xl mb-4 opacity-80"></i>
        <h3 class="text-2xl font-bold mb-4">Together, We're Making a Difference</h3>
        <p class="text-lg text-purple-100 max-w-3xl mx-auto">
            From {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}, 
            our community has contributed <strong>{{ number_format($impact['total_volunteer_hours']) }} hours</strong> of volunteer work, 
            creating meaningful impact across <strong>{{ $impactByCategory->count() }} categories</strong> and touching lives in 
            <strong>{{ $geographicDistribution->count() }} cities</strong>.
        </p>
    </div>
    
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0"></script>
<script>
// Impact by Category Chart
const impactData = @json($impactByCategory);
new Chart(document.getElementById('impactByCategoryChart'), {
    type: 'pie',
    data: {
        labels: impactData.map(d => d.category_name),
        datasets: [{
            data: impactData.map(d => d.total_hours),
            backgroundColor: [
                '#3B82F6', '#10B981', '#F59E0B', '#EF4444', 
                '#8B5CF6', '#EC4899', '#06B6D4', '#84CC16'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'right' },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.label + ': ' + context.parsed.toLocaleString() + ' hours';
                    }
                }
            }
        }
    }
});
</script>
@endpush
@endsection