{{-- resources/views/user/profile.blade.php --}}
@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        
        <!-- Profile Header -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <div class="h-32 bg-gradient-to-r from-indigo-500 to-purple-600"></div>
            <div class="px-6 pb-6">
                <div class="flex items-end -mt-16 mb-4">
                    <img src="{{ $user->avatar_url ? Storage::url($user->avatar_url) : '/images/default-avatar.png' }}" 
                         alt="{{ $user->full_name }}"
                         class="w-32 h-32 rounded-full border-4 border-white object-cover">
                    <div class="ml-6 mb-2">
                        <h1 class="text-2xl font-bold text-gray-900">{{ $user->full_name }}</h1>
                        <p class="text-gray-600">{{ $user->user_type }}</p>
                        @if($user->email_verified_at)
                        <span class="inline-block mt-1 px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full">
                            <i class="fas fa-check-circle mr-1"></i>Verified
                        </span>
                        @endif
                    </div>
                    <div class="ml-auto mb-2">
                        <a href="{{ route('user.edit-profile') }}" 
                           class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                            <i class="fas fa-edit mr-2"></i>Edit Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Main Info -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Personal Information -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Personal Information</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-gray-600">Email</label>
                            <p class="text-gray-900 font-medium">{{ $user->email }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Phone</label>
                            <p class="text-gray-900 font-medium">{{ $user->phone ?? 'Not provided' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Date of Birth</label>
                            <p class="text-gray-900 font-medium">
                                {{ $user->date_of_birth ? date('M d, Y', strtotime($user->date_of_birth)) : 'Not provided' }}
                            </p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Gender</label>
                            <p class="text-gray-900 font-medium">{{ $user->gender ?? 'Not provided' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">City</label>
                            <p class="text-gray-900 font-medium">{{ $user->city ?? 'Not provided' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">District</label>
                            <p class="text-gray-900 font-medium">{{ $user->district ?? 'Not provided' }}</p>
                        </div>
                        <div class="col-span-2">
                            <label class="text-sm text-gray-600">Address</label>
                            <p class="text-gray-900 font-medium">{{ $user->address ?? 'Not provided' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Volunteer Profile -->
                @if($user->isVolunteer() && $user->volunteerProfile)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-gray-900">Volunteer Profile</h2>
                        <a href="{{ route('volunteer.profile') }}" 
                           class="text-indigo-600 hover:text-indigo-700 text-sm">
                            View Full Profile <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                    
                    <div class="space-y-4">
                        @if($user->volunteerProfile->bio)
                        <div>
                            <label class="text-sm text-gray-600">Bio</label>
                            <p class="text-gray-900">{{ $user->volunteerProfile->bio }}</p>
                        </div>
                        @endif

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm text-gray-600">Total Hours</label>
                                <p class="text-2xl font-bold text-indigo-600">
                                    {{ $user->volunteerProfile->total_volunteer_hours }}
                                </p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-600">Rating</label>
                                <p class="text-2xl font-bold text-yellow-500">
                                    {{ number_format($user->volunteerProfile->volunteer_rating, 1) }}
                                    <i class="fas fa-star text-lg"></i>
                                </p>
                            </div>
                        </div>

                        @if($user->volunteerProfile->skills)
                        <div>
                            <label class="text-sm text-gray-600 mb-2 block">Skills</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach(explode(',', $user->volunteerProfile->skills) as $skill)
                                <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm">
                                    {{ trim($skill) }}
                                </span>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        @if($user->volunteerProfile->availability)
                        <div>
                            <label class="text-sm text-gray-600">Availability</label>
                            <p class="text-gray-900 font-medium">{{ $user->volunteerProfile->availability }}</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Organization Profile -->
                @if($user->isOrganization() && $user->organization)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-gray-900">Organization Profile</h2>
                        <a href="{{ route('organization.profile') }}" 
                           class="text-indigo-600 hover:text-indigo-700 text-sm">
                            View Full Profile <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm text-gray-600">Organization Name</label>
                            <p class="text-xl font-bold text-gray-900">{{ $user->organization->organization_name }}</p>
                        </div>

                        <div>
                            <label class="text-sm text-gray-600">Type</label>
                            <p class="text-gray-900 font-medium">{{ $user->organization->organization_type }}</p>
                        </div>

                        <div>
                            <label class="text-sm text-gray-600">Verification Status</label>
                            <span class="px-3 py-1 rounded-full text-sm font-medium
                                {{ $user->organization->verification_status === 'Verified' ? 'bg-green-100 text-green-700' : 
                                   ($user->organization->verification_status === 'Pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                {{ $user->organization->verification_status }}
                            </span>
                        </div>

                        @if($user->organization->description)
                        <div>
                            <label class="text-sm text-gray-600">Description</label>
                            <p class="text-gray-900">{{ Str::limit($user->organization->description, 200) }}</p>
                        </div>
                        @endif

                        <div class="grid grid-cols-3 gap-4 pt-4 border-t">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-indigo-600">{{ $user->organization->total_opportunities }}</div>
                                <div class="text-sm text-gray-600">Opportunities</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600">{{ $user->organization->volunteer_count }}</div>
                                <div class="text-sm text-gray-600">Volunteers</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-yellow-500">{{ number_format($user->organization->rating, 1) }}</div>
                                <div class="text-sm text-gray-600">Rating</div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Account Settings -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Account Settings</h2>
                    <div class="space-y-3">
                        <a href="{{ route('user.change-password') }}" 
                           class="flex items-center justify-between p-3 border rounded-lg hover:bg-gray-50 transition">
                            <div class="flex items-center">
                                <i class="fas fa-key text-gray-400 mr-3"></i>
                                <span class="text-gray-900">Change Password</span>
                            </div>
                            <i class="fas fa-chevron-right text-gray-400"></i>
                        </a>
                        <a href="{{ route('notifications.index') }}" 
                           class="flex items-center justify-between p-3 border rounded-lg hover:bg-gray-50 transition">
                            <div class="flex items-center">
                                <i class="fas fa-bell text-gray-400 mr-3"></i>
                                <span class="text-gray-900">Notification Settings</span>
                            </div>
                            <i class="fas fa-chevron-right text-gray-400"></i>
                        </a>
                        <button onclick="document.getElementById('deactivate-modal').classList.remove('hidden')"
                                class="flex items-center justify-between p-3 border border-red-300 rounded-lg hover:bg-red-50 transition w-full text-left">
                            <div class="flex items-center">
                                <i class="fas fa-user-slash text-red-500 mr-3"></i>
                                <span class="text-red-600">Deactivate Account</span>
                            </div>
                            <i class="fas fa-chevron-right text-red-400"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                
                <!-- Quick Stats -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Account Status</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Member Since</span>
                            <span class="font-medium text-gray-900">{{ $user->created_at->format('M Y') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Last Login</span>
                            <span class="font-medium text-gray-900">
                                {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Account Status</span>
                            <span class="px-2 py-1 {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }} rounded-full text-xs font-medium">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Public Profile -->
                <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg shadow-md p-6 text-white">
                    <h3 class="text-lg font-bold mb-2">Public Profile</h3>
                    <p class="text-sm opacity-90 mb-4">Share your profile with others</p>
                    <a href="{{ route('user.public-profile', $user->user_id) }}" 
                       target="_blank"
                       class="block w-full px-4 py-2 bg-white text-indigo-600 rounded-lg text-center hover:bg-gray-100 transition font-medium">
                        View Public Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Deactivate Account Modal -->
<div id="deactivate-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-md w-full p-6">
        <h3 class="text-xl font-bold text-gray-900 mb-4">Deactivate Account</h3>
        <p class="text-gray-600 mb-4">
            Are you sure you want to deactivate your account? This action will hide your profile and you won't be able to access the platform.
        </p>
        
        <form action="{{ route('user.deactivate') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Enter your password to confirm
                </label>
                <input type="password" 
                       name="password" 
                       required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Reason (Optional)
                </label>
                <textarea name="reason" 
                          rows="3"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                          placeholder="Help us improve by telling us why..."></textarea>
            </div>
            
            <div class="flex space-x-3">
                <button type="button" 
                        onclick="document.getElementById('deactivate-modal').classList.add('hidden')"
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="submit" 
                        class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                    Deactivate
                </button>
            </div>
        </form>
    </div>
</div>
@endsection