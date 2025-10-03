@extends('layouts.admin')

@section('title', 'Create User')
@section('breadcrumb', 'Users / Create')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Create New User</h2>
                <p class="text-gray-600 mt-1">Add a new user to the system</p>
            </div>
            <a href="{{ route('admin.users.index') }}"
                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </a>
        </div>

        <!-- Form -->
        <form id="createUserForm" class="space-y-6">
            @csrf

            <!-- Basic Information -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- First Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            First Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="first_name" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="John">
                        <span class="text-red-500 text-xs mt-1 hidden error-first_name"></span>
                    </div>

                    <!-- Last Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Last Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="last_name" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="Doe">
                        <span class="text-red-500 text-xs mt-1 hidden error-last_name"></span>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="john@example.com">
                        <span class="text-red-500 text-xs mt-1 hidden error-email"></span>
                    </div>

                    <!-- Phone -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Phone <span class="text-red-500">*</span>
                        </label>
                        <input type="tel" name="phone" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="0123456789">
                        <span class="text-red-500 text-xs mt-1 hidden error-phone"></span>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Avatar</label>
                        <div class="flex items-center space-x-4">
                            <img id="avatarPreview" src="https://ui-avatars.com/api/?name=New+User"
                                class="w-20 h-20 rounded-full border-2 border-gray-200" alt="Avatar">
                            <div>
                                <input type="file" name="avatar" id="avatarInput" accept="image/*" class="hidden"
                                    onchange="previewAvatar(event)">
                                <button type="button" onclick="document.getElementById('avatarInput').click()"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                                    <i class="fas fa-upload mr-2"></i>Upload Avatar
                                </button>
                                <p class="text-xs text-gray-500 mt-2">JPG, PNG or GIF. Max 2MB</p>
                            </div>
                        </div>
                    </div>

                    <!-- User Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            User Type <span class="text-red-500">*</span>
                        </label>
                        <select name="user_type" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">Select Type</option>
                            <option value="Volunteer">Volunteer</option>
                            <option value="Organization">Organization</option>
                            <option value="Admin">Admin</option>
                        </select>
                        <span class="text-red-500 text-xs mt-1 hidden error-user_type"></span>
                    </div>

                    <!-- City -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            City <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="city" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="Hanoi">
                        <span class="text-red-500 text-xs mt-1 hidden error-city"></span>
                    </div>
                </div>
            </div>

            <!-- Security -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Security</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Password -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="password" required minlength="8"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="Min 8 characters">
                        <span class="text-red-500 text-xs mt-1 hidden error-password"></span>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Confirm Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="password_confirmation" required minlength="8"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="Re-enter password">
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-4">
                <a href="{{ route('admin.users.index') }}"
                    class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </a>
                <button type="submit" id="submitBtn"
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    <i class="fas fa-save mr-2"></i>Create User
                </button>
            </div>
        </form>

    </div>

    @push('scripts')
        <script>
            function previewAvatar(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        document.getElementById('avatarPreview').src = e.target.result;
                    }
                    reader.readAsDataURL(file);
                }
            }
            document.getElementById('createUserForm').addEventListener('submit', function (e) {
                e.preventDefault();

                // Clear previous errors
                document.querySelectorAll('.text-red-500').forEach(el => el.classList.add('hidden'));

                const formData = new FormData(this);
                const submitBtn = document.getElementById('submitBtn');
                const originalText = submitBtn.innerHTML;

                // Disable button and show loading
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creating...';

                fetch('{{ route("admin.users.store") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json',
                    },
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show success message
                            alert('User created successfully!');
                            // Redirect to users list
                            window.location.href = '{{ route("admin.users.index") }}';
                        } else {
                            // Show error message
                            alert(data.message || 'Failed to create user');
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred. Please try again.');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    });
            });
        </script>
    @endpush
@endsection