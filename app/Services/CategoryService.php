<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Str;

class CategoryService
{
    /**
     * Get all categories.
     */
    public function getAllCategories()
    {
        return Category::all();
    }

    /**
     * Find category by ID.
     */
    public function findCategory(int $id): ?Category
    {
        return Category::find($id);
    }

    /**
     * Create category.
     */
    public function createCategory(array $data): Category
    {
        return Category::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'status' => 'active',
        ]);
    }

    /**
     * Update category.
     */
    public function updateCategory(Category $category, array $data): Category
    {
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $category->update($data);

        return $category;
    }

    /**
     * Deactivate category with reason.
     */
    public function deactivateCategory(Category $category, string $reason): Category
    {
        $category->update([
            'status' => 'inactive',
            'deactivation_reason' => $reason
        ]);

        return $category;
    }

    /**
     * Delete category.
     */
    public function deleteCategory(Category $category): bool
    {
        return $category->delete();
    }
}
