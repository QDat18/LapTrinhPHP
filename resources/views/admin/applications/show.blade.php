@extends('layouts.admin')

@section('title', 'Application Details')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="mb-6 text-sm">
        <ol class="flex items-center space-x-2 text-gray-600">
            <li><a href="{{ route('admin.dashboard') }}" class="hover:text-indigo-600">Dashboard</a></li>
            <li><span class="mx-2">/</span></li>
            <li><a href="{{ route('admin.applications.index') }}" class="hover:text-indigo-600">Applications</a></li>
            <li><span class="mx-2">/</span></li>
            <li class="text-gray-900 font-medium">#{{ $application->application_id }}</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="flex justify-between items-start mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Application #{{ $application->application_id }}</h1>
            <p class="text-gray-600 mt-1">Submitted on {{ $application->applied_date->format('F d, Y \a\t h:i A') }}</p>
        </div>
        <div class="flex gap-3">
            @php
                $statusColors = [
                    'Pending' => 'bg-yellow-100 text-yellow-800',
                    'Under Review' => 'bg-blue-100 text-blue-800',
                    'Accepted' => 'bg-green-100 text-green-800',
                    'Rejected' => 'bg-red-100 text-red-800',
                    'Withdrawn' => 'bg-gray-100 text-gray-800',
                ];
            @endphp
            <span class="px-4 py-2 text-sm font-semibold rounded-lg {{ $statusColors[$application->status] ?? 'bg-gray-100 text-gray-800' }}">
                {{ $application->status }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Opportunity Details -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Opportunity Details</h2>
                <div class="flex items-start space-x-4 mb-4 pb-4 border-b">
                    <img src="{{ $application->opportunity->organization->user->avatar_url ?? '/images/default-org.png' }}" 
                         alt="{{ $application->opportunity->organization->organization_name }}"
                         class="w-16 h-16 rounded-lg object-cover">
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $application->opportunity->title }}</h3>
                        <p class="text-gray-600">{{ $application->opportunity->organization->organization_name }}</p>
                        <div class="flex flex-wrap gap-2 mt-2">
                            <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm">
                                {{ $application->opportunity->category->category_name }}
                            </span>
                            <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm">
                                <i class="fas fa-map-marker-alt mr-1"></i>{{ $application->opportunity->location }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Start Date</p>
                        <p class="font-medium">{{ $application->opportunity->start_date->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Time Commitment</p>
                        <p class="font-medium">{{ $application->opportunity->time_commitment }}</p>
                    </div>
                </div>
            </div>

            <!-- Motivation Letter -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Motivation Letter</h2>
                <div class="prose max-w-none">
                    <p class="text-gray-700 whitespace-pre-line">{{ $application->motivation_letter }}</p>
                </div>
            </div>

            <!-- Relevant Experience -->
            @if($application->relevant_experience)
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Relevant Experience</h2>
                <div class="prose max-w-none">
                    <p class="text-gray-700 whitespace-pre-line">{{ $application->relevant_experience }}</p>
                </div>
            </div>
            @endif

            <!-- Availability Note -->
            @if($application->availability_note)
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Availability</h2>
                <p class="text-gray-700">{{ $application->availability_note }}</p>
            </div>
            @endif

            <!-- Organization Notes -->
            @if($application->organization_notes)
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Organization Notes</h2>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-gray-700">{{ $application->organization_notes }}</p>
                </div>
            </div>
            @endif

            <!-- Timeline -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Application Timeline</h2>
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-file-alt text-blue-600"></i>
                        </div>
                        <div class="ml-4">
                            <p class="font-medium text-gray-900">Application Submitted</p>
                            <p class="text-sm text-gray-600">{{ $application->applied_date->format('M d, Y \a\t h:i A') }}</p>
                            <p class="text-xs text-gray-500">{{ $application->applied_date->diffForHumans() }}</p>
                        </div>
                    </div>

                    @if($application->reviewed_date)
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-check text-green-600"></i>
                        </div>
                        <div class="ml-4">
                            <p class="font-medium text-gray-900">Application Reviewed</p>
                            <p class="text-sm text-gray-600">{{ $application->reviewed_date->format('M d, Y \a\t h:i A') }}</p>
                            <p class="text-xs text-gray-500">{{ $application->reviewed_date->diffForHumans() }}</p>
                        </div>
                    </div>
                    @endif

                    @if($application->interview_scheduled)
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-calendar text-purple-600"></i>
                        </div>
                        <div class="ml-4">
                            <p class="font-medium text-gray-900">Interview Scheduled</p>
                            <p class="text-sm text-gray-600">{{ \Carbon\Carbon::parse($application->interview_scheduled)->format('M d, Y \a\t h:i A') }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Volunteer Profile -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Volunteer Profile</h2>
                <div class="flex flex-col items-center text-center mb-4">
                    <img src="{{ $application->volunteer->avatar_url ?? '/images/default-avatar.png' }}" 
                         alt="{{ $application->volunteer->first_name }}"
                         class="w-24 h-24 rounded-full object-cover mb-3">
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ $application->volunteer->first_name }} {{ $application->volunteer->last_name }}
                    </h3>
                    <p class="text-gray-600 text-sm">{{ $application->volunteer->email }}</p>
                </div>

                <div class="space-y-3 border-t pt-4">
                    <div class="flex items-center text-sm">
                        <i class="fas fa-phone text-gray-400 w-5"></i>
                        <span class="ml-2 text-gray-700">{{ $application->volunteer->phone ?? 'N/A' }}</span>
                    </div>
                    <div class="flex items-center text-sm">
                        <i class="fas fa-map-marker-alt text-gray-400 w-5"></i>
                        <span class="ml-2 text-gray-700">{{ $application->volunteer->city ?? 'N/A' }}</span>
                    </div>
                    <div class="flex items-center text-sm">
                        <i class="fas fa-birthday-cake text-gray-400 w-5"></i>
                        <span class="ml-2 text-gray-700">
                            {{ $application->volunteer->date_of_birth ? \Carbon\Carbon::parse($application->volunteer->date_of_birth)->age . ' years old' : 'N/A' }}
                        </span>
                    </div>
                </div>

                @if($application->volunteer->volunteerProfile)
                <div class="mt-4 pt-4 border-t">
                    <h4 class="font-medium text-gray-900 mb-2">Volunteer Stats</h4>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-blue-50 rounded-lg p-3 text-center">
                            <p class="text-2xl font-bold text-blue-600">{{ $application->volunteer->volunteerProfile->total_volunteer_hours }}</p>
                            <p class="text-xs text-gray-600">Total Hours</p>
                        </div>
                        <div class="bg-green-50 rounded-lg p-3 text-center">
                            <p class="text-2xl font-bold text-green-600">{{ number_format($application->volunteer->volunteerProfile->volunteer_rating, 1) }}</p>
                            <p class="text-xs text-gray-600">Rating</p>
                        </div>
                    </div>
                </div>
                @endif

                <div class="mt-4 pt-4 border-t">
                    <a href="{{ route('admin.users.show', $application->volunteer->user_id) }}" 
                       class="block w-full text-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                        View Full Profile
                    </a>
                </div>
            </div>

            <!-- Organization Info -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Organization</h2>
                <div class="flex items-start space-x-3 mb-3">
                    <img src="{{ $application->opportunity->organization->user->avatar_url ?? '/images/default-org.png' }}" 
                         alt="{{ $application->opportunity->organization->organization_name }}"
                         class="w-12 h-12 rounded-lg object-cover">
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ $application->opportunity->organization->organization_name }}</h3>
                        <p class="text-sm text-gray-600">{{ $application->opportunity->organization->organization_type }}</p>
                    </div>
                </div>
                <div class="space-y-2 text-sm">
                    <div class="flex items-center">
                        <i class="fas fa-envelope text-gray-400 w-5"></i>
                        <span class="ml-2 text-gray-700">{{ $application->opportunity->organization->user->email }}</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-star text-gray-400 w-5"></i>
                        <span class="ml-2 text-gray-700">{{ number_format($application->opportunity->organization->rating, 1) }} Rating</span>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t">
                    <a href="{{ route('admin.organizations.show', $application->opportunity->organization->org_id) }}" 
                       class="block w-full text-center px-4 py-2 border border-indigo-600 text-indigo-600 rounded-lg hover:bg-indigo-50 transition">
                        View Organization
                    </a>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Quick Actions</h2>
                <div class="space-y-2">
                    <a href="{{ route('admin.applications.index') }}" 
                       class="block w-full text-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Applications
                    </a>
                    <button onclick="window.print()" 
                            class="block w-full text-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                        <i class="fas fa-print mr-2"></i>Print Application
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print {
        display: none !important;
    }
}
</style>
@endsection