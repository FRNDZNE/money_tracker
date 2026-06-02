<?php

namespace App\Services;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class CategoryService
{
    /**
     * Get all categories for the given user, optionally filtered by type.
     */
    public function getAll(User $user, ?string $type = null): Collection
    {
        $query = $user->categories()->with('subCategories')->orderBy('type')->orderBy('name');

        if ($type !== null) {
            $query->where('type', $type);
        }

        return $query->get();
    }

    /**
     * Create a new category for the given user.
     */
    public function create(User $user, array $data): Category
    {
        return $user->categories()->create($data);
    }

    /**
     * Update an existing category.
     */
    public function update(Category $category, array $data): Category
    {
        $category->update($data);

        return $category->fresh();
    }

    /**
     * Delete a category.
     */
    public function delete(Category $category): void
    {
        $category->delete();
    }
}
