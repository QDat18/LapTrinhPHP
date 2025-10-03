@extends('layouts.admin')

@section('title', 'Platform Analytics')
@section('breadcrumb', 'Analytics')

@section('content')
<div class="space-y-6">
    
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">Platform Analytics</h2>
            <p class="text-gray-600 mt-1">Real-time insights and performance metrics</p>
        </div>
        <div class="flex space-x-3">
            <select id="periodSelector" onchange="changePeriod(this.value)" 
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                <option value="7days" {{ $period == '7days' ? 'selected' : '' }}>Last 7 days</option>
                <option value="30days" {{ $period == '30days' ? 'selected' : '' }}>Last 30 days</option>
                <option value="90days" {{ $period == '90days' ? 'selected' : '' }}>Last 90 days</option>
                <option value="year" {{ $period == 'year' ? 'selected' : '' }}>Last year</option>
            </select>
            <a href="{{ route('admin.analytics.impact') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                <i class="fas fa-chart-line mr-2"></i>Impact Report
            </a>
        </div>
    </div>
    
    <!-- Key Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
                @if($growth['new_users'] > 0)
                <span class="text-sm font-medium text-green-600">
                    <i class="fas fa-arrow-up"></i> {{ $growth['new_users'] }}
                </span>
                @endif
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ number_format($metrics['total_users']) }}</p>
            <p class="text-gray-600 text-sm mt-1">Total Users</p>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clipboard-list text-green-600 text-xl"></i>
                </div>
                @if($growth['new_opportunities'] > 0)
                <span class="text-sm font-medium text-green-600">
                    <i class="fas fa-arrow-up"></i> {{ $growth['new_opportunities'] }}
                </span>
                @endif
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ number_format($metrics['active_opportunities']) }}</p>
            <p class="text-gray-600 text-sm mt-1">Active Opportunities</p>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-purple-600 text-xl"></i>
                </div>
                @if($growth['hours_logged'] > 0)
                <span class="text-sm font-medium text-green-600">
                    <i class="fas fa-arrow-up"></i> {{ number_format($growth['hours_logged']) }}h
                </span>
                @endif
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ number_format($metrics['total_volunteer_hours']) }}</p>
            <p class="text-gray-600 text-sm mt-1">Volunteer Hours</p>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-building text-orange-600 text-xl"></i>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ number_format($metrics['total_organizations']) }}</p>
            <p class="text-gray-600 text-sm mt-1">Verified Organizations</p>
        </div>
    </div>
    
    <!-- Charts Row 1 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- User Growth Chart -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">User Registration Trend</h3>
            <canvas id="userGrowthChart" height="250"></canvas>
        </div>
        
        <!-- Application Status Chart -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Application Status</h3>
                <span class="text-sm text-gray-500">{{ number_format($metrics['total_applications']) }} total</span>
            </div>
            <canvas id="applicationChart" height="250"></canvas>
        </div>
    </div>
    
    <!-- Charts Row 2 -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Top Categories -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Categories</h3>
            <div class="space-y-3">
                @forelse($topCategories as $category)
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium text-gray-700">{{ $category->category_name }}</span>
                        <span class="text-sm text-gray-500">{{ $category->count }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-indigo-600 h-2 rounded-full transition-all" 
                             style="width: {{ $topCategories->first() ? ($category->count / $topCategories->first()->count) * 100 : 0 }}%"></div>
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-sm text-center py-4">No data available</p>
                @endforelse
            </div>
        </div>
        
        <!-- Monthly Volunteer Hours -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 lg:col-span-2">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Volunteer Hours Trend</h3>
            <canvas id="monthlyHoursChart" height="180"></canvas>
        </div>
    </div>
    
    <!-- Top Lists -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Volunteers -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900">Top Volunteers</h3>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($topVolunteers as $index => $volunteer)
                <div class="px-6 py-4 hover:bg-gray-50 transition">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold">
                            {{ $index + 1 }}
                        </div>
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($volunteer->first_name . ' ' . $volunteer->last_name) }}&background=random" 
                             class="w-12 h-12 rounded-full border-2 border-gray-200" alt="">
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">{{ $volunteer->first_name }} {{ $volunteer->last_name }}</p>
                            <p class="text-sm text-gray-500">
                                <i class="fas fa-clock mr-1"></i>{{ number_format($volunteer->total_hours) }} hours
                            </p>
                        </div>
                    </div>
                </div>
                @empty
                <div class="px-6 py-12 text-center">
                    <p class="text-gray-500">No volunteers yet</p>
                </div>
                @endforelse
            </div>
        </div>
        
        <!-- Top Organizations -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900">Top Organizations</h3>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($topOrganizations as $index => $org)
                <div class="px-6 py-4 hover:bg-gray-50 transition">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-green-500 to-teal-600 rounded-full flex items-center justify-center text-white font-bold">
                                {{ $index + 1 }}
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ Str::limit($org->organization_name, 30) }}</p>
                                <p class="text-sm text-gray-500">
                                    <i class="fas fa-users mr-1"></i>{{ $org->volunteer_count }} volunteers
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-yellow-500">
                                <i class="fas fa-star"></i> {{ number_format($org->rating, 1) }}
                            </span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="px-6 py-12 text-center">
                    <p class="text-gray-500">No organizations yet</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
    
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Prepare data
const userTrendData = @json($userTrend);
const applicationsByStatus = @json($applicationsByStatus);
const monthlyHoursData = @json($monthlyHours);

// User Growth Chart
new Chart(document.getElementById('userGrowthChart'), {
    type: 'line',
    data: {
        labels: userTrendData.map(d => new Date(d.date).toLocaleDateString('en-US', {month: 'short', day: 'numeric'})),
        datasets: [{
            label: 'New Users',
            data: userTrendData.map(d => d.count),
            borderColor: 'rgb(99, 102, 241)',
            backgroundColor: 'rgba(99, 102, 241, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { 
            y: { beginAtZero: true, ticks: { precision: 0 } }
        }
    }
});

// Application Chart
new Chart(document.getElementById('applicationChart'), {
    type: 'doughnut',
    data: {
        labels: Object.keys(applicationsByStatus),
        datasets: [{
            data: Object.values(applicationsByStatus),
            backgroundColor: ['#F59E0B', '#10B981', '#EF4444', '#3B82F6']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } }
    }
});

// Monthly Hours Chart
new Chart(document.getElementById('monthlyHoursChart'), {
    type: 'bar',
    data: {
        labels: monthlyHoursData.map(d => `${d.year}-${String(d.month).padStart(2, '0')}`),
        datasets: [{
            label: 'Hours',
            data: monthlyHoursData.map(d => d.total_hours),
            backgroundColor: 'rgba(99, 102, 241, 0.8)',
            borderRadius: 6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});

function changePeriod(period) {
    window.location.href = `?period=${period}`;
}
</script>
@endpush
@endsection