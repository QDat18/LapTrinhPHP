{{-- resources/views/applications/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Apply for Opportunity')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="mb-6 text-sm">
            <ol class="flex items-center space-x-2 text-gray-600">
                <li><a href="{{ route('home') }}" class="hover:text-indigo-600">Home</a></li>
                <li><span class="mx-2">/</span></li>
                <li><a href="{{ route('opportunities.index') }}" class="hover:text-indigo-600">Opportunities</a></li>
                <li><span class="mx-2">/</span></li>
                <li><a href="{{ route('opportunities.show', $opportunity->opportunity_id) }}" class="hover:text-indigo-600">{{ $opportunity->title }}</a></li>
                <li><span class="mx-2">/</span></li>
                <li class="text-gray-900 font-medium">Apply</li>
            </ol>
        </nav>

        <!-- Opportunity Summary -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-start space-x-4">
                <img src="{{ $opportunity->organization->user->avatar_url ?? '/images/default-org.png' }}" 
                     alt="{{ $opportunity->organization->organization_name }}"
                     class="w-16 h-16 rounded-lg object-cover">
                <div class="flex-1">
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $opportunity->title }}</h1>
                    <p class="text-gray-600 mb-2">{{ $opportunity->organization->organization_name }}</p>
                    <div class="flex flex-wrap gap-2">
                        <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm">
                            {{ $opportunity->category->category_name }}
                        </span>
                        <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm">
                            <i class="fas fa-map-marker-alt mr-1"></i>{{ $opportunity->location }}
                        </span>
                        <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm">
                            <i class="fas fa-clock mr-1"></i>{{ $opportunity->time_commitment }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Application Form -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Submit Your Application</h2>

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('applications.store', $opportunity->opportunity_id) }}" method="POST">
                @csrf

                <!-- Motivation Letter -->
                <div class="mb-6">
                    <label for="motivation_letter" class="block text-sm font-medium text-gray-700 mb-2">
                        Motivation Letter <span class="text-red-500">*</span>
                    </label>
                    <p class="text-sm text-gray-500 mb-2">Tell us why you want to volunteer for this opportunity (100-2000 characters)</p>
                    <textarea name="motivation_letter" 
                              id="motivation_letter" 
                              rows="6"
                              required
                              minlength="100"
                              maxlength="2000"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                              placeholder="Share your passion, relevant experience, and what you hope to contribute...">{{ old('motivation_letter') }}</textarea>
                    <div class="text-right text-sm text-gray-500 mt-1">
                        <span id="char-count">0</span> / 2000 characters
                    </div>
                </div>

                <!-- Relevant Experience -->
                <div class="mb-6">
                    <label for="relevant_experience" class="block text-sm font-medium text-gray-700 mb-2">
                        Relevant Experience (Optional)
                    </label>
                    <p class="text-sm text-gray-500 mb-2">Describe any relevant skills or past volunteer experience</p>
                    <textarea name="relevant_experience" 
                              id="relevant_experience" 
                              rows="4"
                              maxlength="1000"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                              placeholder="List any relevant skills, certifications, or previous volunteer work...">{{ old('relevant_experience') }}</textarea>
                </div>

                <!-- Availability Note -->
                <div class="mb-6">
                    <label for="availability_note" class="block text-sm font-medium text-gray-700 mb-2">
                        Availability Note (Optional)
                    </label>
                    <p class="text-sm text-gray-500 mb-2">Specify your availability for this opportunity</p>
                    <textarea name="availability_note" 
                              id="availability_note" 
                              rows="3"
                              maxlength="500"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                              placeholder="e.g., Available weekends from 9 AM - 5 PM...">{{ old('availability_note') }}</textarea>
                </div>

                <!-- Requirements Checklist -->
                @if($opportunity->required_skills || $opportunity->min_age > 16)
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <h3 class="font-medium text-gray-900 mb-3">Please confirm you meet the requirements:</h3>
                    <div class="space-y-2">
                        @if($opportunity->min_age > 16)
                        <label class="flex items-center">
                            <input type="checkbox" required class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">I am at least {{ $opportunity->min_age }} years old</span>
                        </label>
                        @endif
                        @if($opportunity->required_skills)
                        <label class="flex items-center">
                            <input type="checkbox" required class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">I have reviewed the required skills: {{ $opportunity->required_skills }}</span>
                        </label>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Terms Agreement -->
                <div class="mb-6">
                    <label class="flex items-start">
                        <input type="checkbox" name="terms" required class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 mt-1">
                        <span class="ml-2 text-sm text-gray-700">
                            I agree to the <a href="#" class="text-indigo-600 hover:underline">volunteer terms and conditions</a> 
                            and understand that this application is subject to organization approval.
                        </span>
                    </label>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-between pt-6 border-t">
                    <a href="{{ route('opportunities.show', $opportunity->opportunity_id) }}" 
                       class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-8 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                        <i class="fas fa-paper-plane mr-2"></i>Submit Application
                    </button>
                </div>
            </form>
        </div>

        <!-- Tips Section -->
        <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <h3 class="font-medium text-gray-900 mb-2 flex items-center">
                <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
                Application Tips
            </h3>
            <ul class="space-y-1 text-sm text-gray-700">
                <li>• Be specific about your skills and how they relate to this opportunity</li>
                <li>• Show genuine enthusiasm and commitment</li>
                <li>• Proofread your application before submitting</li>
                <li>• Organizations typically respond within 7 days</li>
            </ul>
        </div>
    </div>
</div>

<script>
// Character counter
document.getElementById('motivation_letter').addEventListener('input', function() {
    const count = this.value.length;
    document.getElementById('char-count').textContent = count;
    
    if (count < 100) {
        document.getElementById('char-count').classList.add('text-red-500');
    } else {
        document.getElementById('char-count').classList.remove('text-red-500');
    }
});
</script>
@endsection