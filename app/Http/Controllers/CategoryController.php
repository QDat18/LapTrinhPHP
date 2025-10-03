<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('opportunities')
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function getCategories()
    {
        $categories = Category::where('is_active', true)
            ->orderBy('display_order')
            ->get();

        return response()->json($categories);
    }

    public function show($id)
    {
        $category = Category::with(['opportunities' => function ($query) {
            $query->where('status', 'Active')
                ->latest()
                ->take(12);
        }])
            ->findOrFail($id);

        $opportunitiesCount = $category->opportunities()->where('status', 'Active')->count();

        return view('admin.categories.show', compact('category', 'opportunitiesCount'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_name' => 'required|string|max:50|unique:categories,category_name',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:7',
            'display_order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $category = Category::create([
                'category_name' => $request->category_name,
                'description' => $request->description,
                'icon' => $request->icon ?? 'fas fa-heart',
                'color' => $request->color ?? '#6366f1',
                'display_order' => $request->display_order ?? 0,
                'is_active' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
                'category' => $category
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create category'
            ], 500);
        }
    }
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'category_name' => 'required|string|max:50|unique:categories,category_name,' . $id . ',category_id',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:7',
            'display_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $category->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully',
                'category' => $category
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update category'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $category = Category::findOrFail($id);

            // Check if category has opportunities
            $opportunitiesCount = $category->opportunities()->count();

            if ($opportunitiesCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete category with existing opportunities'
                ], 400);
            }

            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete category'
            ], 500);
        }
    }

    /**
     * Toggle category status (Admin only)
     */
    public function toggleStatus($id)
    {
        try {
            $category = Category::findOrFail($id);

            $category->update([
                'is_active' => !$category->is_active
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category status updated',
                'is_active' => $category->is_active
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status'
            ], 500);
        }
    }

    /**
     * Reorder categories (Admin only)
     */
    public function reorder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'categories' => 'required|array',
            'categories.*.category_id' => 'required|exists:categories,category_id',
            'categories.*.display_order' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            foreach ($request->categories as $item) {
                Category::where('category_id', $item['category_id'])
                    ->update(['display_order' => $item['display_order']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Categories reordered successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder categories'
            ], 500);
        }
    }

    /**
     * Get category statistics
     */
    public function statistics($id)
    {
        $category = Category::withCount([
            'opportunities',
            'opportunities as active_opportunities_count' => function ($query) {
                $query->where('status', 'Active');
            }
        ])->findOrFail($id);

        $stats = [
            'total_opportunities' => $category->opportunities_count,
            'active_opportunities' => $category->active_opportunities_count,
            'total_applications' => \App\Models\Application::whereHas('opportunity', function ($q) use ($id) {
                $q->where('category_id', $id);
            })->count(),
        ];

        return response()->json($stats);
    }
}
