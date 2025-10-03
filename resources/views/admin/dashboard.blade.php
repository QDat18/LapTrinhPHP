@extends('layouts.admin')

@section('title', 'Dashboard')
@section('breadcrumb', 'Dashboard')

@section('content')
<div class="space-y-6">
    
    <!-- Quick Actions Bar -->
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold mb-2">Welcome back, Admin!</h2>
                <p class="opacity-90">Here's what's happening with your platform today.</p>
            </div>
            <div class="flex space-x-3">
                <button onclick="openEmailModal('all')" class="bg-white text-indigo-600 px-4 py-2 rounded-lg hover:bg-gray-100 transition">
                    <i class="fas fa-envelope mr-2"></i>Send Email
                </button>
                <a href="{{ route('admin.analytics.index') }}" class="bg-white/20 backdrop-blur-sm text-white px-4 py-2 rounded-lg hover:bg-white/30 transition">
                    <i class="fas fa-chart-line mr-2"></i>Analytics
                </a>
            </div>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Total Users -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600">Total Users</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_users'] ?? 0) }}</p>
                    <p class="text-sm text-green-600 mt-2">
                        <i class="fas fa-arrow-up"></i> +{{ $stats['new_users_this_month'] ?? 0 }} this month
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-blue-600 text-2xl"></i>
                </div>
            </div>
            <button onclick="openEmailModal('volunteers')" class="mt-4 text-sm text-blue-600 hover:text-blue-700 font-medium">
                <i class="fas fa-envelope mr-1"></i>Email Volunteers
            </button>
        </div>
        
        <!-- Total Organizations -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600">Organizations</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_orgs'] ?? 0) }}</p>
                    <p class="text-sm text-yellow-600 mt-2">
                        <i class="fas fa-clock"></i> {{ $stats['pending_verifications'] ?? 0 }} pending
                    </p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-building text-purple-600 text-2xl"></i>
                </div>
            </div>
            <button onclick="openEmailModal('organizations')" class="mt-4 text-sm text-purple-600 hover:text-purple-700 font-medium">
                <i class="fas fa-envelope mr-1"></i>Email Organizations
            </button>
        </div>
        
        <!-- Active Opportunities -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600">Active Opportunities</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['active_opportunities'] ?? 0) }}</p>
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
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600">Applications</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_applications'] ?? 0) }}</p>
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
    
    <!-- Email Management Section -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-envelope text-indigo-600 mr-2"></i>Email Management
                </h3>
                <button onclick="openEmailModal('all')" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    <i class="fas fa-paper-plane mr-2"></i>Compose Email
                </button>
            </div>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <button onclick="openEmailModal('volunteers')" class="p-4 border-2 border-blue-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition text-left">
                    <div class="flex items-center justify-between mb-2">
                        <i class="fas fa-user-friends text-blue-600 text-2xl"></i>
                        <span class="text-2xl font-bold text-blue-600">{{ $stats['total_volunteers'] ?? 0 }}</span>
                    </div>
                    <p class="text-sm font-medium text-gray-700">Email All Volunteers</p>
                    <p class="text-xs text-gray-500 mt-1">Send announcements to volunteers</p>
                </button>
                
                <button onclick="openEmailModal('organizations')" class="p-4 border-2 border-purple-200 rounded-lg hover:border-purple-400 hover:bg-purple-50 transition text-left">
                    <div class="flex items-center justify-between mb-2">
                        <i class="fas fa-building text-purple-600 text-2xl"></i>
                        <span class="text-2xl font-bold text-purple-600">{{ $stats['total_orgs'] ?? 0 }}</span>
                    </div>
                    <p class="text-sm font-medium text-gray-700">Email All Organizations</p>
                    <p class="text-xs text-gray-500 mt-1">Send updates to organizations</p>
                </button>
                
                <button onclick="openEmailModal('active')" class="p-4 border-2 border-green-200 rounded-lg hover:border-green-400 hover:bg-green-50 transition text-left">
                    <div class="flex items-center justify-between mb-2">
                        <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                        <span class="text-2xl font-bold text-green-600">{{ $stats['active_users'] ?? 0 }}</span>
                    </div>
                    <p class="text-sm font-medium text-gray-700">Email Active Users</p>
                    <p class="text-xs text-gray-500 mt-1">Target engaged users</p>
                </button>
            </div>
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
                <div class="px-6 py-4 hover:bg-gray-50 transition">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <img src="https://ui-avatars.com/api/?name={{ $user->first_name }}+{{ $user->last_name }}&background=random" 
                                 alt="Avatar" class="w-10 h-10 rounded-full">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $user->first_name }} {{ $user->last_name }}</p>
                                <p class="text-xs text-gray-500">{{ $user->email }}</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="px-3 py-1 text-xs font-medium rounded-full {{ $user->user_type == 'Volunteer' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                {{ $user->user_type }}
                            </span>
                            <button onclick="openEmailModal('single', {{ $user->user_id }})" class="text-gray-400 hover:text-indigo-600">
                                <i class="fas fa-envelope"></i>
                            </button>
                        </div>
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
                <div class="px-6 py-4 hover:bg-gray-50 transition">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">{{ $org->organization_name }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $org->organization_type }} • {{ $org->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="approveOrg({{ $org->org_id }})" class="px-3 py-1 bg-green-100 text-green-700 rounded-lg text-xs hover:bg-green-200 transition">
                                <i class="fas fa-check"></i>
                            </button>
                            <button onclick="rejectOrg({{ $org->org_id }})" class="px-3 py-1 bg-red-100 text-red-700 rounded-lg text-xs hover:bg-red-200 transition">
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
    
</div>

<!-- Email Modal -->
<div id="emailModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between sticky top-0 bg-white">
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-envelope text-indigo-600 mr-2"></i>Compose Email
            </h3>
            <button onclick="closeEmailModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <form id="emailForm" action="{{ route('admin.emails.send') }}" method="POST" class="p-6">
            @csrf
            
            <!-- Recipients -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Recipients</label>
                <input type="hidden" name="recipient_type" id="recipientType">
                <input type="hidden" name="user_id" id="userId">
                <div id="recipientInfo" class="p-3 bg-gray-50 rounded-lg text-sm text-gray-700"></div>
            </div>
            
            <!-- Subject -->
            <div class="mb-4">
                <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                <input type="text" id="subject" name="subject" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                       placeholder="Enter email subject">
            </div>
            
            <!-- Message -->
            <div class="mb-4">
                <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                <textarea id="message" name="message" rows="8" required
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                          placeholder="Write your message here..."></textarea>
            </div>
            
            <!-- Email Templates -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Quick Templates</label>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" onclick="loadTemplate('welcome')" class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">
                        Welcome Email
                    </button>
                    <button type="button" onclick="loadTemplate('update')" class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">
                        Platform Update
                    </button>
                    <button type="button" onclick="loadTemplate('reminder')" class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">
                        Activity Reminder
                    </button>
                    <button type="button" onclick="loadTemplate('announcement')" class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">
                        Announcement
                    </button>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                <button type="button" onclick="closeEmailModal()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    <i class="fas fa-paper-plane mr-2"></i>Send Email
                </button>
            </div>
        </form>
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
    
    // Email Modal Functions
    function openEmailModal(type, userId = null) {
        document.getElementById('emailModal').classList.remove('hidden');
        document.getElementById('recipientType').value = type;
        document.getElementById('userId').value = userId || '';
        
        let recipientText = '';
        switch(type) {
            case 'all':
                recipientText = 'All Users (Volunteers + Organizations)';
                break;
            case 'volunteers':
                recipientText = 'All Volunteers';
                break;
            case 'organizations':
                recipientText = 'All Organizations';
                break;
            case 'active':
                recipientText = 'Active Users Only';
                break;
            case 'single':
                recipientText = 'Single User';
                break;
        }
        
        document.getElementById('recipientInfo').innerHTML = `
            <i class="fas fa-users mr-2"></i>Sending to: <strong>${recipientText}</strong>
        `;
    }
    
    function closeEmailModal() {
        document.getElementById('emailModal').classList.add('hidden');
        document.getElementById('emailForm').reset();
    }
    
    function loadTemplate(template) {
        const templates = {
            welcome: {
                subject: 'Welcome to VolunteerConnect!',
                message: 'Dear User,\n\nWelcome to VolunteerConnect! We\'re excited to have you join our community...'
            },
            update: {
                subject: 'Platform Update - New Features Available',
                message: 'Hello,\n\nWe\'re excited to announce new features on VolunteerConnect...'
            },
            reminder: {
                subject: 'Reminder: Upcoming Activity',
                message: 'Hi there,\n\nThis is a friendly reminder about your upcoming volunteering activity...'
            },
            announcement: {
                subject: 'Important Announcement',
                message: 'Dear Community,\n\nWe have an important announcement to share...'
            }
        };
        
        if (templates[template]) {
            document.getElementById('subject').value = templates[template].subject;
            document.getElementById('message').value = templates[template].message;
        }
    }
    
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
    
    // Email Form Submission
    document.getElementById('emailForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch(this.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Email sent successfully!', 'success');
                closeEmailModal();
            } else {
                showToast('Failed to send email', 'error');
            }
        })
        .catch(error => {
            showToast('An error occurred', 'error');
        });
    });
</script>
@endpush
@endsection