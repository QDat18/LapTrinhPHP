
@extends('admin.layout')

@section('title', 'Dashboard')
@section('breadcrumb', 'Dashboard')

@section('content')
<div class="space-y-6">
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Total Users -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Users</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_users'] ?? 0 }}</p>
                    <p class="text-sm text-green-600 mt-2">
                        <i class="fas fa-arrow-up"></i> +{{ $stats['new_users_this_month'] ?? 0 }} this month
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>
        
        <!-- Total Organizations -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Organizations</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_orgs'] ?? 0 }}</p>
                    <p class="text-sm text-yellow-600 mt-2">
                        <i class="fas fa-clock"></i> {{ $stats['pending_verifications'] ?? 0 }} pending
                    </p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-building text-purple-600 text-2xl"></i>
                </div>
            </div>
        </div>
        
        <!-- Active Opportunities -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Active Opportunities</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['active_opportunities'] ?? 0 }}</p>
                    <p class="text-sm text-indigo-600 mt-2">
                        <i class="fas fa-calendar"></i> {{ $stats['upcoming'] ?? 0 }} upcoming
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clipboard-list text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>
        
        <!-- Total Applications -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Applications</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_applications'] ?? 0 }}</p>
                    <p class="text-sm text-orange-600 mt-2">
                        <i class="fas fa-hourglass-half"></i> {{ $stats['pending_applications'] ?? 0 }} pending
                    </p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-alt text-orange-600 text-2xl"></i>
                </div>
            </div>
        </div>
        
    </div>
    
    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- User Growth Chart -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-800">User Growth</h3>
                <select class="text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    <option>Last 7 days</option>
                    <option>Last 30 days</option>
                    <option>Last 6 months</option>
                </select>
            </div>
            <canvas id="userGrowthChart" height="80"></canvas>
        </div>
        
        <!-- Applications Status -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-800">Application Status</h3>
                <button class="text-sm text-indigo-600 hover:text-indigo-700">View All</button>
            </div>
            <canvas id="applicationStatusChart" height="80"></canvas>
        </div>
        
    </div>
    
    <!-- Tables Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Recent Users -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800">Recent Users</h3>
                    <a href="{{ route('admin.users.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700">View All</a>
                </div>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($recentUsers ?? [] as $user)
                <div class="px-6 py-4 hover:bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <img src="https://ui-avatars.com/api/?name={{ $user->first_name }}+{{ $user->last_name }}&background=random" 
                                 alt="Avatar" class="w-10 h-10 rounded-full">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $user->first_name }} {{ $user->last_name }}</p>
                                <p class="text-xs text-gray-500">{{ $user->email }}</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 text-xs font-medium rounded-full {{ $user->user_type == 'Volunteer' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                            {{ $user->user_type }}
                        </span>
                    </div>
                </div>
                @empty
                <div class="px-6 py-8 text-center text-gray-500">
                    <i class="fas fa-users text-4xl mb-2"></i>
                    <p>No recent users</p>
                </div>
                @endforelse
            </div>
        </div>
        
        <!-- Pending Verifications -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800">Pending Verifications</h3>
                    <a href="{{ route('admin.organizations.index', ['status' => 'pending']) }}" class="text-sm text-indigo-600 hover:text-indigo-700">View All</a>
                </div>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($pendingOrgs ?? [] as $org)
                <div class="px-6 py-4 hover:bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">{{ $org->organization_name }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $org->organization_type }} • {{ $org->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="approveOrg({{ $org->org_id }})" class="px-3 py-1 bg-green-100 text-green-700 rounded-lg text-xs hover:bg-green-200">
                                <i class="fas fa-check"></i>
                            </button>
                            <button onclick="rejectOrg({{ $org->org_id }})" class="px-3 py-1 bg-red-100 text-red-700 rounded-lg text-xs hover:bg-red-200">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                @empty
                <div class="px-6 py-8 text-center text-gray-500">
                    <i class="fas fa-check-circle text-4xl mb-2"></i>
                    <p>No pending verifications</p>
                </div>
                @endforelse
            </div>
        </div>
        
    </div>
    
    <!-- Activity Log -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Recent Activity</h3>
        </div>
        <div class="px-6 py-4">
            <div class="space-y-4">
                @forelse($recentActivities ?? [] as $activity)
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-{{ $activity['color'] }}-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-{{ $activity['icon'] }} text-{{ $activity['color'] }}-600 text-sm"></i>
                        </div>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm text-gray-900">{{ $activity['description'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $activity['time'] }}</p>
                    </div>
                </div>
                @empty
                <div class="text-center text-gray-500 py-4">
                    <i class="fas fa-history text-4xl mb-2"></i>
                    <p>No recent activity</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
    
</div>

@push('scripts')
<script>
    // User Growth Chart
    const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
    new Chart(userGrowthCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartData['userGrowth']['labels'] ?? ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']) !!},
            datasets: [{
                label: 'New Users',
                data: {!! json_encode($chartData['userGrowth']['data'] ?? [12, 19, 15, 25, 22, 30, 28]) !!},
                borderColor: 'rgb(99, 102, 241)',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
    
    // Application Status Chart
    const applicationStatusCtx = document.getElementById('applicationStatusChart').getContext('2d');
    new Chart(applicationStatusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Accepted', 'Rejected', 'Under Review'],
            datasets: [{
                data: {!! json_encode($chartData['applicationStatus'] ?? [45, 30, 15, 10]) !!},
                backgroundColor: [
                    'rgb(251, 191, 36)',
                    'rgb(34, 197, 94)',
                    'rgb(239, 68, 68)',
                    'rgb(59, 130, 246)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    
    // Approve Organization
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
            .catch(error => {
                showToast('Failed to approve organization', 'error');
            });
        }
    }
    
    // Reject Organization
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
            .catch(error => {
                showToast('Failed to reject organization', 'error');
            });
        }
    }
</script>
@endpush
@endsection