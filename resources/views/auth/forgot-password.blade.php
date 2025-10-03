<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Forgot Password - Volunteer Connect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-purple-50 to-indigo-100 min-h-screen flex items-center justify-center p-4">
    
    <div class="w-full max-w-md">
        
        <!-- Logo & Title -->
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-block">
                <h1 class="text-4xl font-bold text-indigo-600 mb-2">
                    <i class="fas fa-hands-helping"></i> Volunteer Connect
                </h1>
            </a>
            <p class="text-gray-600">Reset your password</p>
        </div>

        <!-- Forgot Password Form -->
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            
            <!-- Header -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-8 py-6">
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-full mb-4">
                        <i class="fas fa-key text-3xl text-indigo-600"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-white">
                        Forgot Password?
                    </h2>
                    <p class="text-indigo-100 mt-2">No worries, we'll send you reset instructions</p>
                </div>
            </div>

            <!-- Form -->
            <div class="p-8">
                
                <!-- Success Message -->
                @if(session('status'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6 flex items-start">
                    <i class="fas fa-check-circle mr-3 mt-0.5 text-lg"></i>
                    <div>
                        <p class="font-semibold">Email Sent Successfully!</p>
                        <p class="text-sm mt-1">{{ session('status') }}</p>
                    </div>
                </div>
                @endif

                <!-- Error Messages -->
                @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-circle mr-3 mt-0.5 text-lg"></i>
                        <div>
                            <p class="font-semibold">Oops! Something went wrong</p>
                            <ul class="list-disc list-inside text-sm mt-1">
                                @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Info Message -->
                <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded mb-6 flex items-start">
                    <i class="fas fa-info-circle mr-3 mt-0.5 text-lg"></i>
                    <p class="text-sm">
                        Enter your email address and we'll send you a link to reset your password.
                    </p>
                </div>

                <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                    @csrf

                    <!-- Email Input -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-envelope mr-2 text-indigo-600"></i>
                            Email Address
                        </label>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                               placeholder="Enter your registered email">
                        @error('email')
                        <p class="text-red-500 text-sm mt-1 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                            class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-3 rounded-lg font-semibold text-lg hover:from-indigo-700 hover:to-purple-700 transform hover:scale-[1.02] transition-all duration-200 shadow-lg">
                        <i class="fas fa-paper-plane mr-2"></i> Send Reset Link
                    </button>
                </form>

                <!-- Back to Login -->
                <div class="text-center pt-6 border-t mt-6">
                    <a href="{{ route('login') }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 font-semibold transition">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Login
                    </a>
                </div>
            </div>
        </div>

        <!-- Additional Help -->
        <div class="text-center mt-6">
            <p class="text-sm text-gray-600 mb-2">Still having trouble?</p>
            <a href="#" class="text-sm text-indigo-600 hover:underline font-semibold">
                <i class="fas fa-headset mr-1"></i> Contact Support
            </a>
        </div>

        <!-- Security Note -->
        <div class="bg-white rounded-lg shadow-md p-4 mt-6">
            <div class="flex items-start">
                <i class="fas fa-shield-alt text-indigo-600 text-xl mr-3 mt-1"></i>
                <div>
                    <h3 class="font-semibold text-gray-800 mb-1">Security Tips</h3>
                    <ul class="text-xs text-gray-600 space-y-1">
                        <li>• The reset link will expire in 60 minutes</li>
                        <li>• Check your spam folder if you don't receive the email</li>
                        <li>• Never share your reset link with anyone</li>
                        <li>• Use a strong and unique password</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-hide success message after 10 seconds
        setTimeout(() => {
            const successAlert = document.querySelector('.bg-green-100');
            if (successAlert) {
                successAlert.style.transition = 'opacity 0.5s';
                successAlert.style.opacity = '0';
                setTimeout(() => successAlert.remove(), 500);
            }
        }, 10000);

        // Email validation
        const emailInput = document.querySelector('input[name="email"]');
        const form = document.querySelector('form');

        form.addEventListener('submit', function(e) {
            const email = emailInput.value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!emailRegex.test(email)) {
                e.preventDefault();
                
                // Remove existing error message if any
                const existingError = emailInput.parentElement.querySelector('.custom-error');
                if (existingError) existingError.remove();

                // Add error message
                const errorMsg = document.createElement('p');
                errorMsg.className = 'text-red-500 text-sm mt-1 flex items-center custom-error';
                errorMsg.innerHTML = '<i class="fas fa-exclamation-circle mr-1"></i> Please enter a valid email address';
                emailInput.parentElement.appendChild(errorMsg);
                emailInput.classList.add('border-red-500');
            }
        });

        // Remove error styling on input
        emailInput.addEventListener('input', function() {
            this.classList.remove('border-red-500');
            const customError = this.parentElement.querySelector('.custom-error');
            if (customError) customError.remove();
        });
    </script>
</body>
</html>