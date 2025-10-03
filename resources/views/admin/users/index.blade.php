@extends('layouts.admin')

@section('title', 'User Management')
@section('breadcrumb', 'Users')

@section('content')
    <div class="space-y-6">

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">User Management</h2>
                <p class="text-gray-600 mt-1">Manage all users, volunteers, and organizations</p>
            </div>
            <div class="flex space-x-3">
                <button onclick="openEmailModal('selected')"
                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                    <i class="fas fa-envelope mr-2"></i>Email Selected
                </button>
                <a href="{{ route('admin.users.create') }}"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    <i class="fas fa-plus mr-2"></i>Add User
                </a>
            </div>
        </div>

        <!-- Filters & Search -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <form action="{{ route('admin.users.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}"
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="Search by name, email...">
                    </div>
                </div>

                <!-- User Type Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">User Type</label>
                    <select name="user_type"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="">All Types</option>
                        <option value="Volunteer" {{ request('user_type') == 'Volunteer' ? 'selected' : '' }}>Volunteer
                        </option>
                        <option value="Organization" {{ request('user_type') == 'Organization' ? 'selected' : '' }}>
                            Organization</option>
                        <option value="Admin" {{ request('user_type') == 'Admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                    </select>
                </div>

                <div class="md:col-span-4 flex justify-end space-x-2">
                    <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                        <i class="fas fa-filter mr-2"></i>Apply Filters
                    </button>
                    <a href="{{ route('admin.users.index') }}"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                        <i class="fas fa-redo mr-2"></i>Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <input type="checkbox" id="selectAll" class="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500">
                    <span class="text-sm text-gray-600"><span id="selectedCount">0</span> selected</span>
                </div>
                <div class="flex space-x-2">
                    <button onclick="bulkAction('activate')"
                        class="px-3 py-1 text-sm text-green-600 hover:bg-green-50 rounded">
                        <i class="fas fa-check-circle mr-1"></i>Activate
                    </button>
                    <button onclick="bulkAction('deactivate')"
                        class="px-3 py-1 text-sm text-red-600 hover:bg-red-50 rounded">
                        <i class="fas fa-ban mr-1"></i>Deactivate
                    </button>
                    <button onclick="exportUsers()" class="px-3 py-1 text-sm text-blue-600 hover:bg-blue-50 rounded">
                        <i class="fas fa-download mr-1"></i>Export
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left">
                                <input type="checkbox" class="w-4 h-4 text-indigo-600 rounded">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Joined</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last
                                Login</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($users as $user)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <input type="checkbox" class="user-checkbox w-4 h-4 text-indigo-600 rounded"
                                        value="{{ $user->user_id }}">
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->first_name . ' ' . $user->last_name) }}&background=random"
                                            alt="Avatar" class="w-10 h-10 rounded-full mr-3">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $user->first_name }}
                                                {{ $user->last_name }}</p>
                                            <p class="text-sm text-gray-500">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 text-xs font-medium rounded-full
                                        {{ $user->user_type == 'Volunteer' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $user->user_type == 'Organization' ? 'bg-purple-100 text-purple-800' : '' }}
                                        {{ $user->user_type == 'Admin' ? 'bg-red-100 text-red-800' : '' }}">
                                        {{ $user->user_type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($user->is_active)
                                        <span class="flex items-center text-sm text-green-600">
                                            <span class="w-2 h-2 bg-green-600 rounded-full mr-2"></span>Active
                                        </span>
                                    @else
                                        <span class="flex items-center text-sm text-gray-500">
                                            <span class="w-2 h-2 bg-gray-400 rounded-full mr-2"></span>Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $user->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="{{ route('admin.users.show', $user->user_id) }}"
                                            class="text-indigo-600 hover:text-indigo-900" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.users.edit', $user->user_id) }}"
                                            class="text-blue-600 hover:text-blue-900" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="emailUser({{ $user->user_id }})"
                                            class="text-green-600 hover:text-green-900" title="Email">
                                            <i class="fas fa-envelope"></i>
                                        </button>
                                        @if($user->is_active)
                                            <button onclick="toggleStatus({{ $user->user_id }}, 'deactivate')"
                                                class="text-orange-600 hover:text-orange-900" title="Deactivate">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        @else
                                            <button onclick="toggleStatus({{ $user->user_id }}, 'activate')"
                                                class="text-green-600 hover:text-green-900" title="Activate">
                                                <i class="fas fa-check-circle"></i>
                                            </button>
                                        @endif
                                        <button onclick="deleteUser({{ $user->user_id }})"
                                            class="text-red-600 hover:text-red-900" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-500">
                                        <i class="fas fa-users text-5xl mb-4 text-gray-300"></i>
                                        <p class="text-lg font-medium">No users found</p>
                                        <p class="text-sm mt-1">Try adjusting your filters</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <!-- Email Modal -->
            <div id="emailModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
                <div class="bg-white rounded-lg p-8 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-gray-900">Send Email to Selected Users</h3>
                        <button onclick="closeEmailModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>

                    <form id="emailForm">
                        @csrf
                        <input type="hidden" name="user_ids" id="emailUserIds">

                        <!-- Recipients Info -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                            <p class="text-sm text-blue-800">
                                <i class="fas fa-info-circle mr-2"></i>
                                Email will be sent to <span id="recipientCount" class="font-semibold">0</span> selected
                                users
                            </p>
                        </div>

                        <!-- Subject -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Subject <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="subject" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                placeholder="Email subject...">
                        </div>

                        <!-- Message -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Message <span class="text-red-500">*</span>
                            </label>
                            <textarea name="message" rows="8" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                placeholder="Email message..."></textarea>
                        </div>

                        <!-- Email Template -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Quick Templates</label>
                            <select onchange="applyEmailTemplate(this.value)"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                <option value="">Select a template...</option>
                                <option value="welcome">Welcome Message</option>
                                <option value="announcement">System Announcement</option>
                                <option value="reminder">Account Reminder</option>
                                <option value="update">Platform Update</option>
                            </select>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="closeEmailModal()"
                                class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" id="sendEmailBtn"
                                class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                                <i class="fas fa-paper-plane mr-2"></i>Send Email
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Import Modal -->
            <div id="importModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
                <div class="bg-white rounded-lg p-8 max-w-2xl w-full mx-4">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-gray-900">Import Users from File</h3>
                        <button onclick="closeImportModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>

                    <form id="importForm" enctype="multipart/form-data">
                        @csrf

                        <!-- File Upload Area -->
                        <div class="mb-6">
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                                <input type="file" name="file" id="importFile" accept=".csv,.xlsx,.xls" class="hidden"
                                    onchange="handleFileSelect(event)">
                                <div id="uploadArea">
                                    <i class="fas fa-cloud-upload-alt text-6xl text-gray-400 mb-4"></i>
                                    <p class="text-lg font-medium text-gray-700 mb-2">Drop your file here or click to browse
                                    </p>
                                    <p class="text-sm text-gray-500 mb-4">Supports CSV, XLSX, XLS files</p>
                                    <button type="button" onclick="document.getElementById('importFile').click()"
                                        class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                                        Select File
                                    </button>
                                </div>
                                <div id="fileInfo" class="hidden">
                                    <i class="fas fa-file-excel text-6xl text-green-600 mb-4"></i>
                                    <p class="text-lg font-medium text-gray-900 mb-2" id="fileName"></p>
                                    <p class="text-sm text-gray-500 mb-4" id="fileSize"></p>
                                    <button type="button" onclick="clearFile()" class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-times mr-2"></i>Remove File
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Template Download -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                            <div class="flex items-start">
                                <i class="fas fa-info-circle text-blue-600 mt-0.5 mr-3"></i>
                                <div class="flex-1">
                                    <p class="text-sm text-blue-800 mb-2">
                                        <strong>Required columns:</strong> first_name, last_name, email, phone, user_type,
                                        city, password
                                    </p>
                                    <a href="{{ route('admin.users.download-template') }}"
                                        class="text-sm text-indigo-600 hover:underline">
                                        <i class="fas fa-download mr-1"></i>Download CSV Template
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Options -->
                        <div class="mb-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="skip_duplicates" checked class="mr-2 rounded">
                                <span class="text-sm text-gray-700">Skip duplicate emails</span>
                            </label>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="closeImportModal()"
                                class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" id="importBtn"
                                class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                                <i class="fas fa-upload mr-2"></i>Import Users
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Pagination -->
            @if($users->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

    </div>

    @push('scripts')
        <script>
            // Select All Functionality
            document.getElementById('selectAll').addEventListener('change', function () {
                const checkboxes = document.querySelectorAll('.user-checkbox');
                checkboxes.forEach(cb => cb.checked = this.checked);
                updateSelectedCount();
            });

            // Update selected count
            document.querySelectorAll('.user-checkbox').forEach(cb => {
                cb.addEventListener('change', updateSelectedCount);
            });

            function updateSelectedCount() {
                const count = document.querySelectorAll('.user-checkbox:checked').length;
                document.getElementById('selectedCount').textContent = count;
            }

            // Email single user
            function emailUser(userId) {
                // Open email modal with single user
                fetch(`/admin/users/${userId}`)
                    .then(response => response.json())
                    .then(data => {
                        openEmailModal('single', userId, data.user);
                    });
            }

            // Toggle user status
            function toggleStatus(userId, action) {
                const actionText = action === 'activate' ? 'activate' : 'deactivate';
                if (confirm(`Are you sure you want to ${actionText} this user?`)) {
                    fetch(`/admin/users/${userId}/${action}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json'
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showToast(`User ${actionText}d successfully`, 'success');
                                setTimeout(() => location.reload(), 1000);
                            }
                        })
                        .catch(() => showToast('An error occurred', 'error'));
                }
            }

            // Delete user
            function deleteUser(userId) {
                if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                    fetch(`/admin/users/${userId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json'
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showToast('User deleted successfully', 'success');
                                setTimeout(() => location.reload(), 1000);
                            }
                        })
                        .catch(() => showToast('An error occurred', 'error'));
                }
            }

            // Bulk actions
            function bulkAction(action) {
                const selectedIds = Array.from(document.querySelectorAll('.user-checkbox:checked'))
                    .map(cb => cb.value);

                if (selectedIds.length === 0) {
                    showToast('Please select at least one user', 'warning');
                    return;
                }

                if (confirm(`Are you sure you want to ${action} ${selectedIds.length} user(s)?`)) {
                    fetch('/admin/users/bulk-action', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ user_ids: selectedIds, action: action })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showToast(`${selectedIds.length} user(s) ${action}d successfully`, 'success');
                                setTimeout(() => location.reload(), 1000);
                            }
                        })
                        .catch(() => showToast('An error occurred', 'error'));
                }
            }

            // Export users
            function exportUsers() {
                const params = new URLSearchParams(window.location.search);
                window.location.href = '/admin/users/export?' + params.toString();
            }
        </script>
    @endpush
@endsection