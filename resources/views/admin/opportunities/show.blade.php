{{-- resources/views/opportunities/show.blade.php --}}
@extends('admin.layout')

@section('title', $opportunity->title)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        
        <!-- Breadcrumb -->
        <nav class="mb-6 text-sm">
            <ol class="flex items-center space-x-2 text-gray-600">
                <li><a href="{{ route('home') }}" class="hover:text-indigo-600">Home</a></li>
                <li><span class="mx-2">/</span></li>
                <li><a href="{{ route('opportunities.index') }}" class="hover:text-indigo-600">Opportunities</a></li>
                <li><span class="mx-2">/</span></li>
                <li class="text-gray-900 font-medium">{{ Str::limit($opportunity->title, 50) }}</li>
            </ol>
                </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Header Card -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-start space-x-4 mb-4">
                            <img src="{{ $opportunity->organization->user->avatar_url ?? '/images/default-org.png' }}" 
                                 alt="{{ $opportunity->organization->organization_name }}"
                                 class="w-20 h-20 rounded-lg object-cover">
                            <div class="flex-1">
                                <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $opportunity->title }}</h1>
                                <p class="text-lg text-gray-600 mb-2">
                                    <a href="{{ route('user.public-profile', $opportunity->organization->user_id) }}" 
                                       class="hover:text-indigo-600">
                                        {{ $opportunity->organization->organization_name }}
                                    </a>
                                </p>
                                <div class="flex items-center space-x-4 text-sm text-gray-500">
                                    <span><i class="fas fa-eye mr-1"></i>{{ $opportunity->view_count }} views</span>
                                    <span><i class="fas fa-file-alt mr-1"></i>{{ $opportunity->application_count }} applications</span>
                                    <span><i class="fas fa-clock mr-1"></i>Posted {{ $opportunity->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Category and Status -->
                        <div class="flex flex-wrap gap-2 mb-4">
                            <span class="px-3 py-1 rounded-full text-sm font-medium"
                                  style="background-color: {{ $opportunity->category->color }}20; color: {{ $opportunity->category->color }}">
                                <i class="{{ $opportunity->category->icon }} mr-1"></i>
                                {{ $opportunity->category->category_name }}
                            </span>
                            <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">
                                {{ $opportunity->status }}
                            </span>
                            @if($opportunity->experience_needed)
                            <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-medium">
                                {{ $opportunity->experience_needed }}
                            </span>
                            @endif
                        </div>

                        <!-- Key Info Grid -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-4 bg-gray-50 rounded-lg">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-indigo-600">{{ $opportunity->volunteers_needed }}</div>
                                <div class="text-sm text-gray-600">Volunteers Needed</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600">{{ $opportunity->volunteers_registered }}</div>
                                <div class="text-sm text-gray-600">Registered</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-purple-600">{{ $opportunity->volunteers_needed - $opportunity->volunteers_registered }}</div>
                                <div class="text-sm text-gray-600">Spots Left</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-orange-600">{{ $opportunity->min_age }}+</div>
                                <div class="text-sm text-gray-600">Min Age</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">About This Opportunity</h2>
                    <div class="prose max-w-none text-gray-700">
                        {!! nl2br(e($opportunity->description)) !!}
                    </div>
                </div>

                <!-- Requirements -->
                @if($opportunity->requirements)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Requirements</h2>
                    <div class="prose max-w-none text-gray-700">
                        {!! nl2br(e($opportunity->requirements)) !!}
                    </div>
                    @if($opportunity->required_skills)
                    <div class="mt-4">
                        <h3 class="font-medium text-gray-900 mb-2">Required Skills:</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach(explode(',', $opportunity->required_skills) as $skill)
                            <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm">
                                {{ trim($skill) }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Benefits -->
                @if($opportunity->benefits)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">What You'll Gain</h2>
                    <div class="prose max-w-none text-gray-700">
                        {!! nl2br(e($opportunity->benefits)) !!}
                    </div>
                </div>
                @endif

                <!-- Similar Opportunities -->
                @if($similarOpportunities->count() > 0)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Similar Opportunities</h2>
                    <div class="space-y-4">
                        @foreach($similarOpportunities as $similar)
                        <div class="flex items-start space-x-4 p-4 border rounded-lg hover:bg-gray-50 transition">
                            <img src="{{ $similar->organization->user->avatar_url ?? '/images/default-org.png' }}" 
                                 alt="{{ $similar->organization->organization_name }}"
                                 class="w-16 h-16 rounded-lg object-cover">
                            <div class="flex-1">
                                <h3 class="font-bold text-gray-900 hover:text-indigo-600">
                                    <a href="{{ route('opportunities.show', $similar->opportunity_id) }}">
                                        {{ $similar->title }}
                                    </a>
                                </h3>
                                <p class="text-sm text-gray-600">{{ $similar->organization->organization_name }}</p>
                                <div class="flex items-center space-x-3 text-xs text-gray-500 mt-2">
                                    <span><i class="fas fa-map-marker-alt mr-1"></i>{{ $similar->location }}</span>
                                    <span><i class="fas fa-clock mr-1"></i>{{ $similar->time_commitment }}</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                
                <!-- Apply Card -->
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-4">
                    @auth
                        @if(Auth::user()->isVolunteer())
                            @if($hasApplied)
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                                <p class="text-green-700 font-medium text-center">
                                    <i class="fas fa-check-circle mr-2"></i>You've already applied
                                </p>
                            </div>
                            <a href="{{ route('applications.my-applications') }}" 
                               class="block w-full px-4 py-3 bg-gray-200 text-gray-700 rounded-lg text-center hover:bg-gray-300 transition">
                                View My Applications
                            </a>
                            @elseif($opportunity->volunteers_registered >= $opportunity->volunteers_needed)
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                                <p class="text-yellow-700 font-medium text-center">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>This opportunity is full
                                </p>
                            </div>
                            @elseif($opportunity->application_deadline && strtotime($opportunity->application_deadline) < time())
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                                <p class="text-red-700 font-medium text-center">
                                    <i class="fas fa-times-circle mr-2"></i>Application deadline has passed
                                </p>
                            </div>
                            @else
                            <a href="{{ route('applications.create', $opportunity->opportunity_id) }}" 
                               class="block w-full px-4 py-3 bg-indigo-600 text-white rounded-lg text-center hover:bg-indigo-700 transition font-medium mb-3">
                                <i class="fas fa-paper-plane mr-2"></i>Apply Now
                            </a>
                            @endif

                            <!-- Favorite Button -->
                            <button onclick="toggleFavorite({{ $opportunity->opportunity_id }})" 
                                    class="w-full px-4 py-2 border-2 {{ $isFavorited ? 'border-red-500 text-red-500' : 'border-gray-300 text-gray-700' }} rounded-lg hover:bg-gray-50 transition">
                                <i class="{{ $isFavorited ? 'fas' : 'far' }} fa-heart mr-2"></i>
                                {{ $isFavorited ? 'Saved' : 'Save for Later' }}
                            </button>
                        @else
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                                <p class="text-blue-700 text-sm text-center">
                                    Only volunteers can apply for opportunities
                                </p>
                            </div>
                        @endif
                    @else
                    <a href="{{ route('login') }}" 
                       class="block w-full px-4 py-3 bg-indigo-600 text-white rounded-lg text-center hover:bg-indigo-700 transition font-medium mb-3">
                        Login to Apply
                    </a>
                    <a href="{{ route('register') }}" 
                       class="block w-full px-4 py-2 border-2 border-indigo-600 text-indigo-600 rounded-lg text-center hover:bg-indigo-50 transition">
                        Create Account
                    </a>
                    @endauth
                </div>

                <!-- Details Card -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Opportunity Details</h3>
                    <div class="space-y-3">
                        <div class="flex items-start">
                            <i class="fas fa-map-marker-alt text-indigo-600 mr-3 mt-1"></i>
                            <div>
                                <div class="text-sm text-gray-500">Location</div>
                                <div class="font-medium text-gray-900">{{ $opportunity->location }}</div>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-calendar-alt text-indigo-600 mr-3 mt-1"></i>
                            <div>
                                <div class="text-sm text-gray-500">Start Date</div>
                                <div class="font-medium text-gray-900">{{ date('F d, Y', strtotime($opportunity->start_date)) }}</div>
                            </div>
                        </div>
                        @if($opportunity->end_date)
                        <div class="flex items-start">
                            <i class="fas fa-calendar-check text-indigo-600 mr-3 mt-1"></i>
                            <div>
                                <div class="text-sm text-gray-500">End Date</div>
                                <div class="font-medium text-gray-900">{{ date('F d, Y', strtotime($opportunity->end_date)) }}</div>
                            </div>
                        </div>
                        @endif
                        <div class="flex items-start">
                            <i class="fas fa-clock text-indigo-600 mr-3 mt-1"></i>
                            <div>
                                <div class="text-sm text-gray-500">Time Commitment</div>
                                <div class="font-medium text-gray-900">{{ $opportunity->time_commitment }}</div>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-sync-alt text-indigo-600 mr-3 mt-1"></i>
                            <div>
                                <div class="text-sm text-gray-500">Schedule</div>
                                <div class="font-medium text-gray-900">{{ $opportunity->schedule_type }}</div>
                            </div>
                        </div>
                        @if($opportunity->application_deadline)
                        <div class="flex items-start">
                            <i class="fas fa-hourglass-half text-indigo-600 mr-3 mt-1"></i>
                            <div>
                                <div class="text-sm text-gray-500">Apply Before</div>
                                <div class="font-medium text-gray-900">{{ date('F d, Y', strtotime($opportunity->application_deadline)) }}</div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Organization Card -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">About the Organization</h3>
                    <div class="flex items-center space-x-3 mb-4">
                        <img src="{{ $opportunity->organization->user->avatar_url ?? '/images/default-org.png' }}" 
                             alt="{{ $opportunity->organization->organization_name }}"
                             class="w-16 h-16 rounded-lg object-cover">
                        <div>
                            <h4 class="font-bold text-gray-900">{{ $opportunity->organization->organization_name }}</h4>
                            <p class="text-sm text-gray-600">{{ $opportunity->organization->organization_type }}</p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-700 mb-4">{{ Str::limit($opportunity->organization->description, 150) }}</p>
                    <a href="{{ route('user.public-profile', $opportunity->organization->user_id) }}" 
                       class="block w-full px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-center hover:bg-gray-200 transition">
                        View Full Profile
                    </a>
                </div>

                <!-- Share Card -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Share This Opportunity</h3>
                    <div class="flex space-x-2">
                        <button onclick="shareOn('facebook')" 
                                class="flex-1 px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            <i class="fab fa-facebook-f"></i>
                        </button>
                        <button onclick="shareOn('twitter')" 
                                class="flex-1 px-3 py-2 bg-sky-500 text-white rounded-lg hover:bg-sky-600 transition">
                            <i class="fab fa-twitter"></i>
                        </button>
                        <button onclick="copyLink()" 
                                class="flex-1 px-3 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                            <i class="fas fa-link"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div