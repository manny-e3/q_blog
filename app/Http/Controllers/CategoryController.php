<?php

namespace App\Http\Controllers;

use App\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Get all categories.
     */
    public function index()
    {
        $categories = $this->categoryService->getAllCategories();
        return response()->json($categories);
    }

    /**
     * Get single category.
     */
    public function show($id)
    {
        $category = $this->categoryService->findCategory($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found.'], 404);
        }

        return response()->json($category);
    }

    /**
     * Create category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        $category = $this->categoryService->createCategory($validated);

        return response()->json($category, 201);
    }

    /**
     * Update category.
     */
    public function update(Request $request, $id)
    {
        $category = $this->categoryService->findCategory($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found.'], 404);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($category->id)],
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
        ]);

        $updatedCategory = $this->categoryService->updateCategory($category, $validated);

        return response()->json($updatedCategory);
    }

    /**
     * Deactivate category with reason.
     */
    public function deactivate(Request $request, $id)
    {
        $category = $this->categoryService->findCategory($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found.'], 404);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:1000'
        ]);

        $deactivatedCategory = $this->categoryService->deactivateCategory($category, $validated['reason']);

        return response()->json([
            'message' => 'Category deactivated successfully.',
            'category' => $deactivatedCategory
        ]);
    }

    /**
     * Delete category.
     */
    public function destroy($id)
    {
        $category = $this->categoryService->findCategory($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found.'], 404);
        }

        $this->categoryService->deleteCategory($category);

        return response()->json([
            'message' => 'Category deleted successfully.'
        ]);
    }
}
