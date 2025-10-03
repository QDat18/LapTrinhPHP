@extends('layouts.admin')

@section('title', 'User Details')
@section('breadcrumb', 'Users / Details')

@section('content')
<div class="space-y-6">
    
    <!-- Back Button -->
    <div>
        <a href="{{ route('admin.users.index') }}" 
           class="inline-flex items-center text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left mr-2"></i>Back to Users
        </a>
    </div>
    
    <!-- User Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <!-- Cover -->
        <div class="h-32 bg-gradient-to-r from-indigo-500 to-purple-600"></div>
        
        <!-- Profile Info -->
        <div class="px-8 pb-8">
            <div class="flex items-end justify-between -mt-16 mb-6">
                <div class="flex items-end space-x-4">
                    <img src="{{ $user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->first_name . ' ' . $user->last_name) }}" 
                         class="w-32 h-32 rounded-full border-4 border-white shadow-lg bg-white" alt="Avatar">
                    <div class="pb-2">
                        <h1 class="text-3xl font-bold text-gray-900">{{ $user->first_name }} {{ $user->last_name }}</h1>
                        <div class="flex items-center space-x-4 mt-2">
                            <span class="px-3 py-1 text-sm font-medium rounded-full
                                {{ $user->user_type == 'Volunteer' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $user->user_type == 'Organization' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $user->user_type == 'Admin' ? 'bg-purple-100 text-purple-800' : '' }}">
                                <i class="fas fa-{{ $user->user_type == 'Volunteer' ? 'user' : ($user->user_type == 'Organization' ? 'building' : 'crown') }} mr-1"></i>
                                {{ $user->user_type }}
                            </span>
                            <span class="px-3 py-1 text-sm font-medium rounded-full
                                {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            @if($user->is_verified)
                            <span class="px-3 py-1 text-sm font-medium bg-blue-100 text-blue-800 rounded-full">
                                <i class="fas fa-check-circle mr-1"></i>Verified
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex space-x-2">
                    <a href="{{ route('admin.users.edit', $user->user_id) }}" 
                       class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                        <i class="fas fa-edit mr-2"></i>Edit
                    </a>
                    @if($user->is_active)
                    <button onclick="suspendUser({{ $user->user_id }})" 
                            class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition">
                        <i class="fas fa-pause mr-2"></i>Suspend
                    </button>
                    @else
                    <button onclick="activateUser({{ $user->user_id }})" 
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                        <i class="fas fa-play mr-2"></i>Activate
                    </button>
                    @endif
                    <button onclick="deleteUser({{ $user->user_id }})" 
                            class="px-4 py-2 border border-red-300 text-red-600 rounded-lg hover:bg-red-50 transition">
                        <i class="fas fa-trash mr-2"></i>Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Column - Main Info -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Personal Information -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Personal Information</h3>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500">Full Name</p>
                        <p class="text-sm font-medium text-gray-900">{{ $user->first_name }} {{ $user->last_name }}</p>
                    </div>
                    
                    <div>
                        <p class="text-xs text-gray-500">Email</p>
                        <p class="text-sm font-medium text-gray-900">{{ $user->email }}</p>
                    </div>
                    
                    @if($user->phone)
                    <div>
                        <p class="text-xs text-gray-500">Phone</p>
                        <p class="text-sm font-medium text-gray-900">{{ $user->phone }}</p>
                    </div>
                    @endif
                    
                    @if($user->date_of_birth)
                    <div>
                        <p class="text-xs text-gray-500">Date of Birth</p>
                        <p class="text-sm font-medium text-gray-900">{{ \Carbon\Carbon::parse($user->date_of_birth)->format('M d, Y') }}</p>
                    </div>
                    @endif
                    
                    @if($user->gender)
                    <div>
                        <p class="text-xs text-gray-500">Gender</p>
                        <p class="text-sm font-medium text-gray-900">{{ $user->gender }}</p>
                    </div>
                    @endif
                    
                    @if($user->city)
                    <div>
                        <p class="text-xs text-gray-500">Location</p>
                        <p class="text-sm font-medium text-gray-900">{{ $user->city }}{{ $user->district ? ', ' . $user->district : '' }}</p>
                    </div>
                    @endif
                </div>
                
                @if($user->address)
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <p class="text-xs text-gray-500">Full Address</p>
                    <p class="text-sm font-medium text-gray-900">{{ $user->address }}</p>
                </div>
                @endif
            </div>
            
            <!-- Volunteer Profile (if user is volunteer) -->
            @if($user->user_type == 'Volunteer' && $user->volunteerProfile)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Volunteer Profile</h3>
                
                <div class="space-y-4">
                    @if($user->volunteerProfile->bio)
                    <div>
                        <p class="text-xs text-gray-500">Bio</p>
                        <p class="text-sm text-gray-700">{{ $user->volunteerProfile->bio }}</p>
                    </div>
                    @endif
                    
                    <div class="grid grid-cols-2 gap-4">
                        @if($user->volunteerProfile->occupation)
                        <div>
                            <p class="text-xs text-gray-500">Occupation</p>
                            <p class="text-sm font-medium text-gray-900">{{ $user->volunteerProfile->occupation }}</p>
                        </div>
                        @endif
                        
                        @if($user->volunteerProfile->education_level)
                        <div>
                            <p class="text-xs text-gray-500">Education</p>
                            <p class="text-sm font-medium text-gray-900">{{ $user->volunteerProfile->education_level }}</p>
                        </div>
                        @endif
                        
                        <div>
                            <p class="text-xs text-gray-500">Total Hours</p>
                            <p class="text-sm font-medium text-gray-900">{{ number_format($user->volunteerProfile->total_volunteer_hours) }} hours</p>
                        </div>
                        
                        <div>
                            <p class="text-xs text-gray-500">Rating</p>
                            <p class="text-sm font-medium text-gray-900">
                                <i class="fas fa-star text-yellow-500"></i> {{ number_format($user->volunteerProfile->volunteer_rating, 1) }}
                            </p>
                        </div>
                    </div>
                    
                    @if($user->volunteerProfile->skills)
                    <div>
                        <p class="text-xs text-gray-500 mb-2">Skills</p>
                        <div class="flex flex-wrap gap-2">
                            @php
                                $skills = is_string($user->volunteerProfile->skills) ? explode(',', $user->volunteerProfile->skills) : [];
                            @endphp
                            @foreach($skills as $skill)
                            <span class="px-3 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                                {{ trim($skill) }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif
            
            <!-- Organization Profile (if user is organization) -->
            @if($user->user_type == 'Organization' && $user->organization)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Organization Profile</h3>
                
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-gray-500">Organization Name</p>
                            <p class="text-sm font-medium text-gray-900">{{ $user->organization->organization_name }}</p>
                        </div>
                        
                        <div>
                            <p class="text-xs text-gray-500">Type</p>
                            <p class="text-sm font-medium text-gray-900">{{ $user->organization->organization_type }}</p>
                        </div>
                        
                        <div>
                            <p class="text-xs text-gray-500">Verification Status</p>
                            <span class="px-2 py-1 text-xs font-medium rounded-full
                                {{ $user->organization->verification_status == 'Verified' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $user->organization->verification_status == 'Pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $user->organization->verification_status == 'Rejected' ? 'bg-red-100 text-red-800' : '' }}">
                                {{ $user->organization->verification_status }}
                            </span>
                        </div>
                        
                        <div>
                            <p class="text-xs text-gray-500">Total Opportunities</p>
                            <p class="text-sm font-medium text-gray-900">{{ $user->organization->total_opportunities }}</p>
                        </div>
                    </div>
                    
                    @if($user->organization->description)
                    <div>
                        <p class="text-xs text-gray-500">Description</p>
                        <p class="text-sm text-gray-700">{{ $user->organization->description }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif
            
            <!-- Activity Log -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Activity Log</h3>
                
                <div class="space-y-3">
                    <div class="flex items-start">
                        <div class="w-2 h-2 bg-green-500 rounded-full mt-2 mr-3"></div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-900">Account created</p>
                            <p class="text-xs text-gray-500">{{ $user->created_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                    
                    @if($user->last_login_at)
                    <div class="flex items-start">
                        <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 mr-3"></div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-900">Last login</p>
                            <p class="text-xs text-gray-500">{{ $user->last_login_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                    @endif
                    
                    <div class="flex items-start">
                        <div class="w-2 h-2 bg-purple-500 rounded-full mt-2 mr-3"></div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-900">Last updated</p>
                            <p class="text-xs text-gray-500">{{ $user->updated_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
        
        <!-- Right Column - Additional Info -->
        <div class="space-y-6">
            
            <!-- Account Status -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Account Status</h3>
                
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700">Status</span>
                        <span class="px-2 py-1 text-xs font-medium rounded-full
                            {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700">Email Verified</span>
                        <span class="px-2 py-1 text-xs font-medium rounded-full
                            {{ $user->is_verified ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $user->is_verified ? 'Verified' : 'Not Verified' }}
                        </span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700">User Type</span>
                        <span class="px-2 py-1 text-xs font-medium rounded-full
                            {{ $user->user_type == 'Volunteer' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $user->user_type == 'Organization' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $user->user_type == 'Admin' ? 'bg-purple-100 text-purple-800' : '' }}">
                            {{ $user->user_type }}
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Statistics (for volunteers) -->
            @if($user->user_type == 'Volunteer' && $user->volunteerProfile)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistics</h3>
                
                <div class="space-y-4">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm text-gray-700">Volunteer Hours</span>
                            <span class="text-sm font-semibold text-gray-900">{{ number_format($user->volunteerProfile->total_volunteer_hours) }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ min(100, ($user->volunteerProfile->total_volunteer_hours / 100) * 100) }}%"></div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm text-gray-700">Rating</span>
                            <span class="text-sm font-semibold text-gray-900">{{ number_format($user->volunteerProfile->volunteer_rating, 1) }}/5.0</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-yellow-500 h-2 rounded-full" style="width: {{ ($user->volunteerProfile->volunteer_rating / 5) * 100 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            
            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                
                <div class="space-y-2">
                    <a href="{{ route('admin.users.edit', $user->user_id) }}" 
                       class="block w-full px-4 py-2 text-center bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 transition">
                        <i class="fas fa-edit mr-2"></i>Edit Profile
                    </a>
                    
                    @if($user->is_active)
                    <button onclick="suspendUser({{ $user->user_id }})" 
                            class="block w-full px-4 py-2 text-center bg-yellow-50 text-yellow-600 rounded-lg hover:bg-yellow-100 transition">
                        <i class="fas fa-pause mr-2"></i>Suspend Account
                    </button>
                    @else
                    <button onclick="activateUser({{ $user->user_id }})" 
                            class="block w-full px-4 py-2 text-center bg-green-50 text-green-600 rounded-lg hover:bg-green-100 transition">
                        <i class="fas fa-play mr-2"></i>Activate Account
                    </button>
                    @endif
                    
                    <button onclick="resetPassword()" 
                            class="block w-full px-4 py-2 text-center bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition">
                        <i class="fas fa-key mr-2"></i>Reset Password
                    </button>
                    
                    <button onclick="deleteUser({{ $user->user_id }})" 
                            class="block w-full px-4 py-2 text-center bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition">
                        <i class="fas fa-trash mr-2"></i>Delete Account
                    </button>
                </div>
            </div>
            
        </div>
        
    </div>
    
</div>

@push('scripts')
<script>
function suspendUser(userId) {
    if (confirm('Are you sure you want to suspend this user?')) {
        fetch(`/admin/users/${userId}/suspend`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('User suspended successfully!');
                location.reload();
            } else {
                alert(data.message || 'Failed to suspend user');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }
}

function activateUser(userId) {
    if (confirm('Are you sure you want to activate this user?')) {
        fetch(`/admin/users/${userId}/activate`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('User activated successfully!');
                location.reload();
            } else {
                alert(data.message || 'Failed to activate user');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }
}

function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        fetch(`/admin/users/${userId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('User deleted successfully!');
                window.location.href = '{{ route("admin.users.index") }}';
            } else {
                alert(data.message || 'Failed to delete user');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }
}

function resetPassword() {
    alert('Reset password functionality will send an email to the user with a password reset link.');
}
</script>
@endpush
@endsection