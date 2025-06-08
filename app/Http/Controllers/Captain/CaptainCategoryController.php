<?php

namespace App\Http\Controllers\Captain;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\FundSettings;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CaptainCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query();
        $query = $this->filter($query, $request);

        $categories = $query->with('subcategories')
                          ->orderBy('group_name')
                          ->orderBy('name')
                          ->paginate(10);

        return Inertia::render('captain/CaptainCategory', [
            'categories' => [
                'data' => CategoryResource::collection($categories)->resolve(),
                'current_page' => $categories->currentPage(),
                'last_page' => $categories->lastPage(),
                'per_page' => $categories->perPage(),
                'total' => $categories->total(),
            ],
            'search' => $request->input('search'),
        ]);
    }

    private function filter($query, Request $request)
    {
        // Search filter
        $search = $request->input('search');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Type filter
        $type = $request->input('type');
        if ($type) {
            $query->where('group_name', $type);
        }

        return $query;
    }

    public function create(CategoryRequest $request)
    {
        $validated = $request->validated();
        
        $category = Category::create($validated);
    
        return redirect()->back()->with([
            'success' => 'Category created successfully',
            'category' => new CategoryResource($category)
        ]);
    }

    public function update(CategoryRequest $request, Category $category)
    {
        $CategorySetting = FundSettings::where('name', 'categories')->first();
        if ($CategorySetting && $CategorySetting->is_locked) {
            return redirect()->back()->withErrors([
                'categories' => 'Categories are locked and cannot be modified.'
            ]);
        }

        $validated = $request->validated();

        $category->update($validated);

        return redirect()->back()->with([
            'success' => 'Category updated successfully',
            'category' => new CategoryResource($category)
        ]);
    }

    public function destroy(Category $category)
    {
        $CategorySetting = FundSettings::where('name', 'categories')->first();
        if ($CategorySetting && $CategorySetting->is_locked) {
            return redirect()->back()->withErrors([
                'categories' => 'Categories are locked and cannot be deleted.'
            ]);
        }

        $category->delete();

        return redirect()->back()->with([
            'success' => 'Category deleted successfully',
            'category' => new CategoryResource($category)
        ]);
    }
}