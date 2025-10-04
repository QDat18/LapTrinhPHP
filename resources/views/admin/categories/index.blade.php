@extends('layouts.admin')

@section('title', 'Categories Management')
@section('breadcrumb', 'Categories')

@section('content')
<div class="space-y-6">
    
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Categories Management</h2>
            <p class="text-gray-600 mt-1">Manage opportunity categories</p>
        </div>
        <button onclick="openCreateModal()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
            <i class="fas fa-plus mr-2"></i>Add Category
        </button>
    </div>
    
    <!-- Categories Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($categories as $category)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 rounded-lg flex items-center justify-center" 
                     style="background-color: {{ $category->color ?? '#6366f1' }}20">
                    <i class="{{ $category->icon ?? 'fas fa-tag' }} text-2xl" 
                       style="color: {{ $category->color ?? '#6366f1' }}"></i>
                </div>
                <div class="flex items-center space-x-2">
                    <button onclick="toggleCategory({{ $category->category_id }})" 
                            class="text-sm px-2 py-1 rounded {{ $category->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ $category->is_active ? 'Active' : 'Inactive' }}
                    </button>
                </div>
            </div>
            
            <h3 class="font-semibold text-gray-900 mb-2">{{ $category->category_name }}</h3>
            <p class="text-sm text-gray-600 mb-4">{{ Str::limit($category->description, 60) }}</p>
            
            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                <span class="text-sm text-gray-500">
                    <i class="fas fa-clipboard-list mr-1"></i>
                    {{ $category->opportunities_count ?? 0 }} opportunities
                </span>
                <div class="flex space-x-2">
                    <button onclick="editCategory({{ $category->category_id }})" 
                            class="text-indigo-600 hover:text-indigo-800">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="deleteCategory({{ $category->category_id }})" 
                            class="text-red-600 hover:text-red-800">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    
</div>

<!-- Create Modal -->
<div id="createModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-gray-900">Add New Category</h3>
            <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <form id="createForm">
            @csrf
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Category Name *</label>
                <input type="text" name="category_name" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea name="description" rows="3"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Icon Class</label>
                    <input type="text" name="icon" placeholder="fas fa-heart"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Color</label>
                    <input type="color" name="color" value="#6366f1"
                           class="w-full h-10 px-2 py-1 border border-gray-300 rounded-lg">
                </div>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Display Order</label>
                <input type="number" name="display_order" value="0"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeCreateModal()" 
                        class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    Create Category
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-gray-900">Edit Category</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <form id="editForm">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit_category_id">
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Category Name *</label>
                <input type="text" id="edit_category_name" name="category_name" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea id="edit_description" name="description" rows="3"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Icon Class</label>
                    <input type="text" id="edit_icon" name="icon"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Color</label>
                    <input type="color" id="edit_color" name="color"
                           class="w-full h-10 px-2 py-1 border border-gray-300 rounded-lg">
                </div>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Display Order</label>
                <input type="number" id="edit_display_order" name="display_order"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeEditModal()" 
                        class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    Update Category
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// Create Modal
function openCreateModal() {
    document.getElementById('createModal').classList.remove('hidden');
}

function closeCreateModal() {
    document.getElementById('createModal').classList.add('hidden');
    document.getElementById('createForm').reset();
}

document.getElementById('createForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    fetch('{{ route("admin.categories.store") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(Object.fromEntries(new FormData(this)))
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Category created successfully!');
            location.reload();
        } else {
            alert(data.message || 'Failed to create category');
        }
    });
});

// Edit Modal
function editCategory(id) {
    fetch(`/admin/categories/${id}/edit`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('edit_category_id').value = data.category_id;
            document.getElementById('edit_category_name').value = data.category_name;
            document.getElementById('edit_description').value = data.description || '';
            document.getElementById('edit_icon').value = data.icon || '';
            document.getElementById('edit_color').value = data.color || '#6366f1';
            document.getElementById('edit_display_order').value = data.display_order || 0;
            
            document.getElementById('editModal').classList.remove('hidden');
        });
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

document.getElementById('editForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const id = document.getElementById('edit_category_id').value;
    const formData = Object.fromEntries(new FormData(this));
    
    fetch(`/admin/categories/${id}`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Category updated successfully!');
            location.reload();
        } else {
            alert(data.message || 'Failed to update category');
        }
    });
});

// Toggle Active
function toggleCategory(id) {
    fetch(`/admin/categories/${id}/toggle`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

// Delete
function deleteCategory(id) {
    if (confirm('Are you sure you want to delete this category?')) {
        fetch(`/admin/categories/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Category deleted successfully!');
                location.reload();
            } else {
                alert(data.message || 'Failed to delete category');
            }
        });
    }
}
</script>
@endpush
@endsection