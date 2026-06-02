<?php

namespace App\Services;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Database\Eloquent\Collection;

class SubCategoryService
{
    /**
     * Get all sub-categories for a category.
     */
    public function getAll(Category $category): Collection
    {
        return $category->subCategories()->orderBy('name')->get();
    }

    /**
     * Create a new sub-category under the given category.
     */
    public function create(Category $category, array $data): SubCategory
    {
        return $category->subCategories()->create($data);
    }

    /**
     * Update an existing sub-category.
     */
    public function update(SubCategory $subCategory, array $data): SubCategory
    {
        $subCategory->update($data);

        return $subCategory->fresh();
    }

    /**
     * Delete a sub-category.
     */
    public function delete(SubCategory $subCategory): void
    {
        $subCategory->delete();
    }
}
