@extends('admin.layout')

@section('title', 'User Management')
@section('breadcrumb', 'Users')

@section('content')
<div class="space-y-6">
    
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">User Management</h1>
            <p class="text-sm text-gray-600 mt-1">Manage all users, volunteers, and organizations</p>
        </div>
        <button onclick="openCreateModal()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
            <i class="fas fa-plus mr-2"></i> Add User
        </button>
    </div>
    
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            
            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Name, email, phone..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            
            <!-- User Type -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">User Type</label>
                <select name="user_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Types</option>
                    <option value="Volunteer" {{ request('user_type') == 'Volunteer' ? 'selected' : '' }}>Volunteer</option>
                    <option value="Organization" {{ request('user_type') == 'Organization' ? 'selected' : '' }}>Organization</option>
                    <option value="Admin" {{ request('user_type') == 'Admin' ? 'selected' : '' }}>Admin</option>
                </select>
            </div>
            
            <!-- Status -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            
            <!-- Actions -->
            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    <i class="fas fa-search mr-2"></i> Filter
                </button>
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                    <i class="fas fa-redo"></i>
                </a>
            </div>
            
        </form>
    </div>
    
    <!-- Bulk Actions -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4" x-data="{ selectedCount: 0 }" x-show="selectedCount > 0" x-cloak>
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-700">
                <span x-text="selectedCount"></span> user(s) selected
            </span>
            <div class="flex space-x-2">
                <button onclick="bulkAction('activate')" class="px-4 py-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 text-sm">
                    <i class="fas fa-check-circle mr-2"></i> Activate
                </button>
                <button onclick="bulkAction('deactivate')" class="px-4 py-2 bg-yellow-100 text-yellow-700 rounded-lg hover:bg-yellow-200 text-sm">
                    <i class="fas fa-ban mr-2"></i> Deactivate
                </button>
                <button onclick="bulkAction('delete')" class="px-4 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 text-sm">
                    <i class="fas fa-trash mr-2"></i> Delete
                </button>
            </div>
        </div>
    </div>
    
    <!-- Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left">
                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)" 
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            User
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Type
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Location
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Joined
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <input type="checkbox" class="user-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" 
                                   value="{{ $user->user_id }}" onchange="updateSelectedCount()">
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($user->first_name . ' ' . $user->last_name) }}&background=random" 
                                     alt="Avatar" class="w-10 h-10 rounded-full">
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $user->first_name }} {{ $user->last_name }}</p>
                                    <p class="text-sm text-gray-500">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 text-xs font-medium rounded-full 
                                {{ $user->user_type == 'Volunteer' ? 'bg-blue-100 text-blue-800' : 
                                   ($user->user_type == 'Organization' ? 'bg-purple-100 text-purple-800' : 'bg-red-100 text-red-800') }}">
                                {{ $user->user_type }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $user->city ?? 'N/A' }}, {{ $user->district ?? '' }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 text-xs font-medium rounded-full 
                                {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $user->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('admin.users.show', $user->user_id) }}" 
                                   class="text-indigo-600 hover:text-indigo-900" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button onclick="editUser({{ $user->user_id }})" 
                                        class="text-blue-600 hover:text-blue-900" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="toggleStatus({{ $user->user_id }}, {{ $user->is_active ? 'false' : 'true' }})" 
                                        class="text-yellow-600 hover:text-yellow-900" title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}">
                                    <i class="fas fa-{{ $user->is_active ? 'ban' : 'check-circle' }}"></i>
                                </button>
                                <button onclick="deleteUser({{ $user->user_id }})" 
                                        class="text-red-600 hover:text-red-900" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-users text-4xl mb-4 text-gray-300"></i>
                            <p class="text-lg font-medium">No users found</p>
                            <p class="text-sm mt-2">Try adjusting your search or filter criteria</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($users->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $users->links() }}
        </div>
        @endif
        
    </div>
    
</div>

<!-- Create/Edit Modal -->
<div id="userModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900" id="modalTitle">Add New User</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <form id="userForm" method="POST" class="p-6">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <input type="hidden" name="user_id" id="userId">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">First Name *</label>
                    <input type="text" name="first_name" id="firstName" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Last Name *</label>
                    <input type="text" name="last_name" id="lastName" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                    <input type="email" name="email" id="email" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone *</label>
                    <input type="tel" name="phone" id="phone" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">User Type *</label>
                    <select name="user_type" id="userType" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select Type</option>
                        <option value="Volunteer">Volunteer</option>
                        <option value="Organization">Organization</option>
                        <option value="Admin">Admin</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">City *</label>
                    <select name="city" id="city" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select City</option>
                        <option value="Hanoi">Hanoi</option>
                        <option value="Ho Chi Minh">Ho Chi Minh City</option>
                        <option value="Da Nang">Da Nang</option>
                        <option value="Hai Phong">Hai Phong</option>
                        <option value="Can Tho">Can Tho</option>
                    </select>
                </div>
                
                <div class="md:col-span-2" id="passwordField">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password *</label>
                    <input type="password" name="password" id="password"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <p class="text-xs text-gray-500 mt-1">Leave blank to keep current password (when editing)</p>
                </div>
                
            </div>
            
            <div class="flex items-center justify-end space-x-3 mt-6 pt-6 border-t border-gray-200">
                <button type="button" onclick="closeModal()" 
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    <i class="fas fa-save mr-2"></i> Save User
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Select All Checkbox
    function toggleSelectAll(checkbox) {
        document.querySelectorAll('.user-checkbox').forEach(cb => {
            cb.checked = checkbox.checked;
        });
        updateSelectedCount();
    }
    
    // Update Selected Count
    function updateSelectedCount() {
        const count = document.querySelectorAll('.user-checkbox:checked').length;
        document.querySelector('[x-data]').__x.$data.selectedCount = count;
    }
    
    // Bulk Actions
    function bulkAction(action) {
        const selectedIds = Array.from(document.querySelectorAll('.user-checkbox:checked'))
            .map(cb => cb.value);
        
        if (selectedIds.length === 0) {
            showToast('Please select at least one user', 'warning');
            return;
        }
        
        if (!confirm(`Are you sure you want to ${action} ${selectedIds.length} user(s)?`)) {
            return;
        }
        
        fetch('{{ route("admin.users.bulk-action") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: action,
                user_ids: selectedIds
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                setTimeout(() => location.reload(), 1000);
            }
        })
        .catch(error => {
            showToast('Action failed', 'error');
        });
    }
    
    // Open Create Modal
    function openCreateModal() {
        document.getElementById('modalTitle').textContent = 'Add New User';
        document.getElementById('userForm').reset();
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('userId').value = '';
        document.getElementById('passwordField').querySelector('input').required = true;
        document.getElementById('userModal').classList.remove('hidden');
    }
    
    // Edit User
    function editUser(userId) {
        fetch(`/admin/users/${userId}`)
            .then(response => response.json())
            .then(user => {
                document.getElementById('modalTitle').textContent = 'Edit User';
                document.getElementById('formMethod').value = 'PUT';
                document.getElementById('userId').value = user.user_id;
                document.getElementById('firstName').value = user.first_name;
                document.getElementById('lastName').value = user.last_name;
                document.getElementById('email').value = user.email;
                document.getElementById('phone').value = user.phone;
                document.getElementById('userType').value = user.user_type;
                document.getElementById('city').value = user.city;
                document.getElementById('passwordField').querySelector('input').required = false;
                document.getElementById('userModal').classList.remove('hidden');
            });
    }
    
    // Close Modal
    function closeModal() {
        document.getElementById('userModal').classList.add('hidden');
    }
    
    // Toggle User Status
    function toggleStatus(userId, activate) {
        fetch(`/admin/users/${userId}/status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ is_active: activate })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                setTimeout(() => location.reload(), 1000);
            }
        });
    }
    
    // Delete User
    function deleteUser(userId) {
        if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
            return;
        }
        
        fetch(`/admin/users/${userId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('User deleted successfully', 'success');
                setTimeout(() => location.reload(), 1000);
            }
        });
    }
    
    // Handle Form Submit
    document.getElementById('userForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const method = document.getElementById('formMethod').value;
        const userId = document.getElementById('userId').value;
        const url = userId ? `/admin/users/${userId}` : '{{ route("admin.users.store") }}';
        
        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                closeModal();
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(data.message || 'An error occurred', 'error');
            }
        })
        .catch(error => {
            showToast('Failed to save user', 'error');
        });
    });
</script>
@endpush
@endsection