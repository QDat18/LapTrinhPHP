<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register - Volunteer Connect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-purple-50 to-indigo-100 min-h-screen">
    
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            
            <!-- Logo & Title -->
            <div class="text-center mb-8">
                <a href="{{ route('home') }}" class="inline-block">
                    <h1 class="text-4xl font-bold text-indigo-600 mb-2">
                        <i class="fas fa-hands-helping"></i> Volunteer Connect
                    </h1>
                </a>
                <p class="text-gray-600">Join our community and make a difference</p>
            </div>

            <!-- Registration Form -->
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                
                <!-- Header -->
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-8 py-6">
                    <h2 class="text-2xl font-bold text-white text-center">
                        Create Your Account
                    </h2>
                    <p class="text-indigo-100 text-center mt-2">Start your volunteer journey today</p>
                </div>

                <!-- Form -->
                <form method="POST" action="{{ route('register') }}" class="p-8 space-y-6">
                    @csrf

                    <!-- Success/Error Messages -->
                    @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        {{ session('success') }}
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        {{ session('error') }}
                    </div>
                    @endif

                    <!-- Account Type Selection -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            I am registering as: <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="relative cursor-pointer">
                                <input type="radio" name="user_type" value="Volunteer" 
                                       class="peer sr-only" required checked>
                                <div class="block p-6 bg-gray-50 border-2 border-gray-200 rounded-lg peer-checked:border-indigo-600 peer-checked:bg-indigo-50 transition">
                                    <div class="text-center">
                                        <i class="fas fa-user text-4xl text-indigo-600 mb-3"></i>
                                        <h3 class="font-bold text-gray-800">Volunteer</h3>
                                        <p class="text-sm text-gray-600 mt-2">I want to help and contribute</p>
                                    </div>
                                </div>
                            </label>

                            <label class="relative cursor-pointer">
                                <input type="radio" name="user_type" value="Organization" 
                                       class="peer sr-only" required>
                                <div class="block p-6 bg-gray-50 border-2 border-gray-200 rounded-lg peer-checked:border-indigo-600 peer-checked:bg-indigo-50 transition">
                                    <div class="text-center">
                                        <i class="fas fa-building text-4xl text-indigo-600 mb-3"></i>
                                        <h3 class="font-bold text-gray-800">Organization</h3>
                                        <p class="text-sm text-gray-600 mt-2">I need volunteers for my cause</p>
                                    </div>
                                </div>
                            </label>
                        </div>
                        @error('user_type')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Personal Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                First Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="first_name" value="{{ old('first_name') }}" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="John">
                            @error('first_name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Last Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="last_name" value="{{ old('last_name') }}" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="Doe">
                            @error('last_name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-envelope mr-2 text-indigo-600"></i>
                                Email Address <span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="john@example.com">
                            @error('email')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-phone mr-2 text-indigo-600"></i>
                                Phone Number <span class="text-red-500">*</span>
                            </label>
                            <input type="tel" name="phone" value="{{ old('phone') }}" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="0912345678">
                            @error('phone')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Personal Details -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Date of Birth
                            </label>
                            <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('date_of_birth')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Gender
                            </label>
                            <select name="gender" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Select gender</option>
                                <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                                <option value="Other" {{ old('gender') == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('gender')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-map-marker-alt mr-2 text-indigo-600"></i>
                                City <span class="text-red-500">*</span>
                            </label>
                            <select name="city" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Select city</option>
                                <option value="Hanoi" {{ old('city') == 'Hanoi' ? 'selected' : '' }}>Hanoi</option>
                                <option value="Ho Chi Minh" {{ old('city') == 'Ho Chi Minh' ? 'selected' : '' }}>Ho Chi Minh City</option>
                                <option value="Da Nang" {{ old('city') == 'Da Nang' ? 'selected' : '' }}>Da Nang</option>
                                <option value="Hai Phong" {{ old('city') == 'Hai Phong' ? 'selected' : '' }}>Hai Phong</option>
                                <option value="Can Tho" {{ old('city') == 'Can Tho' ? 'selected' : '' }}>Can Tho</option>
                            </select>
                            @error('city')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                District
                            </label>
                            <input type="text" name="district" value="{{ old('district') }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="District name">
                            @error('district')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Address
                        </label>
                        <textarea name="address" rows="2" 
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                  placeholder="Street address">{{ old('address') }}</textarea>
                        @error('address')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-lock mr-2 text-indigo-600"></i>
                                Password <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" name="password" id="password" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                       placeholder="Minimum 8 characters">
                                <button type="button" onclick="togglePassword('password')" 
                                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-indigo-600">
                                    <i class="fas fa-eye" id="password-icon"></i>
                                </button>
                            </div>
                            <div class="mt-2 h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div id="password-strength" class="h-full transition-all duration-300"></div>
                            </div>
                            <p id="password-text" class="text-xs mt-1"></p>
                            @error('password')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-lock mr-2 text-indigo-600"></i>
                                Confirm Password <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" name="password_confirmation" id="password_confirmation" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                       placeholder="Re-enter password">
                                <button type="button" onclick="togglePassword('password_confirmation')" 
                                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-indigo-600">
                                    <i class="fas fa-eye" id="password_confirmation-icon"></i>
                                </button>
                            </div>
                            <p id="password-match" class="text-xs mt-1"></p>
                        </div>
                    </div>

                    <!-- Terms & Conditions -->
                    <div class="flex items-start">
                        <input type="checkbox" name="terms" id="terms" required
                               class="mt-1 h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <label for="terms" class="ml-2 text-sm text-gray-700">
                            I agree to the <a href="#" class="text-indigo-600 hover:underline">Terms of Service</a> 
                            and <a href="#" class="text-indigo-600 hover:underline">Privacy Policy</a>
                            <span class="text-red-500">*</span>
                        </label>
                    </div>
                    @error('terms')
                    <p class="text-red-500 text-sm">{{ $message }}</p>
                    @enderror

                    <!-- Submit Button -->
                    <button type="submit" 
                            class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-4 rounded-lg font-semibold text-lg hover:from-indigo-700 hover:to-purple-700 transform hover:scale-[1.02] transition-all duration-200 shadow-lg">
                        <i class="fas fa-user-plus mr-2"></i> Create Account
                    </button>

                    <!-- Login Link -->
                    <div class="text-center pt-4 border-t">
                        <p class="text-gray-600">
                            Already have an account? 
                            <a href="{{ route('login') }}" class="text-indigo-600 hover:underline font-semibold">
                                Login here
                            </a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId + '-icon');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Password strength checker
        const passwordInput = document.getElementById('password');
        const strengthBar = document.getElementById('password-strength');
        const strengthText = document.getElementById('password-text');

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;

            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;

            strengthBar.className = 'h-full transition-all duration-300';
            
            if (strength === 0) {
                strengthBar.style.width = '0%';
                strengthText.textContent = '';
            } else if (strength <= 1) {
                strengthBar.style.width = '25%';
                strengthBar.classList.add('bg-red-500');
                strengthText.textContent = 'Weak password';
                strengthText.className = 'text-xs mt-1 text-red-500';
            } else if (strength === 2) {
                strengthBar.style.width = '50%';
                strengthBar.classList.add('bg-orange-500');
                strengthText.textContent = 'Fair password';
                strengthText.className = 'text-xs mt-1 text-orange-500';
            } else if (strength === 3) {
                strengthBar.style.width = '75%';
                strengthBar.classList.add('bg-yellow-500');
                strengthText.textContent = 'Good password';
                strengthText.className = 'text-xs mt-1 text-yellow-600';
            } else {
                strengthBar.style.width = '100%';
                strengthBar.classList.add('bg-green-500');
                strengthText.textContent = 'Strong password';
                strengthText.className = 'text-xs mt-1 text-green-500';
            }
        });

        // Password match checker
        const confirmPassword = document.getElementById('password_confirmation');
        const matchText = document.getElementById('password-match');

        confirmPassword.addEventListener('input', function() {
            if (this.value === '') {
                matchText.textContent = '';
                return;
            }
            
            if (this.value === passwordInput.value) {
                matchText.textContent = 'Passwords match ✓';
                matchText.className = 'text-xs mt-1 text-green-500';
            } else {
                matchText.textContent = 'Passwords do not match';
                matchText.className = 'text-xs mt-1 text-red-500';
            }
        });

        passwordInput.addEventListener('input', function() {
            if (confirmPassword.value !== '') {
                if (confirmPassword.value === this.value) {
                    matchText.textContent = 'Passwords match ✓';
                    matchText.className = 'text-xs mt-1 text-green-500';
                } else {
                    matchText.textContent = 'Passwords do not match';
                    matchText.className = 'text-xs mt-1 text-red-500';
                }
            }
        });
    </script>
</body>
</html>