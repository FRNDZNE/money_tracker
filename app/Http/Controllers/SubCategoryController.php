<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubCategoryRequest;
use App\Http\Requests\UpdateSubCategoryRequest;
use App\Models\Category;
use App\Models\SubCategory;
use App\Services\SubCategoryService;
use Illuminate\Http\RedirectResponse;

class SubCategoryController extends Controller
{
    public function __construct(
        private readonly SubCategoryService $subCategoryService,
    ) {}

    public function store(StoreSubCategoryRequest $request, Category $category): RedirectResponse
    {
        abort_if($category->user_id !== auth()->id(), 403);

        $this->subCategoryService->create($category, $request->validated());

        return redirect()->route('categories.index')
            ->with('success', 'Sub-category created successfully.');
    }

    public function update(UpdateSubCategoryRequest $request, Category $category, SubCategory $subCategory): RedirectResponse
    {
        abort_if($category->user_id !== auth()->id(), 403);

        $this->subCategoryService->update($subCategory, $request->validated());

        return redirect()->route('categories.index')
            ->with('success', 'Sub-category updated successfully.');
    }

    public function destroy(Category $category, SubCategory $subCategory): RedirectResponse
    {
        abort_if($category->user_id !== auth()->id(), 403);

        $this->subCategoryService->delete($subCategory);

        return redirect()->route('categories.index')
            ->with('success', 'Sub-category deleted successfully.');
    }
}
