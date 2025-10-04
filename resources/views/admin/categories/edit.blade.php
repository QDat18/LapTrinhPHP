@extends('layouts.admin')

@section('title', 'Edit Category')
@section('breadcrumb', 'Categories / Edit')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Edit Category</h2>
            <p class="text-gray-600 mt-1">Update category information</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.categories.index') }}" 
               class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </a>
        </div>
    </div>
    
    <!-- Form -->
    <form id="editCategoryForm" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        @csrf
        @method('PUT')
        
        <!-- Category Name -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Category Name <span class="text-red-500">*</span>
            </label>
            <input type="text" name="category_name" value="{{ old('category_name', $category->category_name) }}" required
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
        </div>
        
        <!-- Description -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
            <textarea name="description" rows="4"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">{{ old('description', $category->description) }}</textarea>
        </div>
        
        <!-- Icon & Color -->
        <div class="grid grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Icon Class</label>
                <input type="text" name="icon" value="{{ old('icon', $category->icon) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                       placeholder="fas fa-heart">
                <p class="text-xs text-gray-500 mt-1">
                    <a href="https://fontawesome.com/icons" target="_blank" class="text-indigo-600 hover:underline">
                        Browse Font Awesome icons
                    </a>
                </p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Color</label>
                <div class="flex items-center space-x-3">
                    <input type="color" name="color" id="colorPicker" value="{{ old('color', $category->color ?? '#6366f1') }}"
                           class="h-10 w-20 border border-gray-300 rounded cursor-pointer">
                    <input type="text" id="colorText" value="{{ old('color', $category->color ?? '#6366f1') }}" readonly
                           class="flex-1 px-4 py-2 border border-gray-300 rounded-lg bg-gray-50">
                </div>
            </div>
        </div>
        
        <!-- Display Order -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Display Order</label>
            <input type="number" name="display_order" value="{{ old('display_order', $category->display_order ?? 0) }}" min="0"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            <p class="text-xs text-gray-500 mt-1">Lower numbers appear first</p>
        </div>
        
        <!-- Preview -->
        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
            <label class="block text-sm font-medium text-gray-700 mb-3">Preview</label>
            <div class="flex items-center space-x-4">
                <div id="iconPreview" class="w-16 h-16 rounded-lg flex items-center justify-center"
                     style="background-color: {{ $category->color ?? '#6366f1' }}20">
                    <i class="{{ $category->icon ?? 'fas fa-tag' }} text-3xl" style="color: {{ $category->color ?? '#6366f1' }}"></i>
                </div>
                <div>
                    <h3 id="namePreview" class="font-semibold text-gray-900">{{ $category->category_name }}</h3>
                    <p id="descPreview" class="text-sm text-gray-600">{{ $category->description ?? 'No description' }}</p>
                </div>
            </div>
        </div>
        
        <!-- Active Status -->
        <div class="mb-6">
            <label class="flex items-center">
                <input type="checkbox" name="is_active" value="1" {{ $category->is_active ? 'checked' : '' }}
                       class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <span class="ml-2 text-sm text-gray-700">Active (visible to users)</span>
            </label>
        </div>
        
        <!-- Stats -->
        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-center text-sm text-blue-800">
                <i class="fas fa-info-circle mr-2"></i>
                <span>This category is used in <strong>{{ $category->opportunities_count ?? 0 }}</strong> opportunities</span>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex justify-between pt-6 border-t border-gray-200">
            <button type="button" onclick="deleteCategory()" 
                    class="px-6 py-2 border border-red-300 text-red-600 rounded-lg hover:bg-red-50">
                <i class="fas fa-trash mr-2"></i>Delete Category
            </button>
            <div class="flex space-x-3">
                <a href="{{ route('admin.categories.index') }}" 
                   class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" id="submitBtn"
                        class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    <i class="fas fa-save mr-2"></i>Update Category
                </button>
            </div>
        </div>
    </form>
    
</div>

@push('scripts')
<script>
// Live Preview
document.querySelector('[name="category_name"]').addEventListener('input', function(e) {
    document.getElementById('namePreview').textContent = e.target.value || 'Category Name';
});

document.querySelector('[name="description"]').addEventListener('input', function(e) {
    document.getElementById('descPreview').textContent = e.target.value || 'No description';
});

document.querySelector('[name="icon"]').addEventListener('input', function(e) {
    const iconPreview = document.querySelector('#iconPreview i');
    iconPreview.className = (e.target.value || 'fas fa-tag') + ' text-3xl';
});

document.getElementById('colorPicker').addEventListener('input', function(e) {
    const color = e.target.value;
    document.getElementById('colorText').value = color;
    document.getElementById('iconPreview').style.backgroundColor = color + '20';
    document.querySelector('#iconPreview i').style.color = color;
});

// Form Submit
document.getElementById('editCategoryForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Updating...';
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    fetch('{{ route("admin.categories.update", $category->category_id) }}', {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Category updated successfully!');
            window.location.href = '{{ route("admin.categories.index") }}';
        } else {
            alert(data.message || 'Failed to update category');
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

// Delete Category
function deleteCategory() {
    if (confirm('Are you sure you want to delete this category? This action cannot be undone.')) {
        fetch('{{ route("admin.categories.destroy", $category->category_id) }}', {
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
                window.location.href = '{{ route("admin.categories.index") }}';
            } else {
                alert(data.message || 'Failed to delete category');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }
}
</script>
@endpush
@endsection