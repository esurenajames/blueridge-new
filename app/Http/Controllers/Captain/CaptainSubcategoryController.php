<?php

namespace App\Http\Controllers\Captain;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubcategoryRequest;
use App\Http\Resources\SubcategoryResource;
use App\Http\Resources\CategoryResource;
use App\Models\FundSettings;
use App\Models\Subcategory;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CaptainSubcategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Subcategory::query();
    
        $search = $request->input('search');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
    
        $subcategories = $query->with('category')
                              ->orderBy('created_at', 'desc')
                              ->paginate(10);
        
        // Get all active categories for the dropdown
        $categories = Category::where('status', 'active')->get();
    
        return Inertia::render('captain/CaptainSubcategory', [
            'subcategories' => [
                'data' => SubcategoryResource::collection($subcategories)->resolve(),
                'current_page' => $subcategories->currentPage(),
                'last_page' => $subcategories->lastPage(),
                'per_page' => $subcategories->perPage(),
                'total' => $subcategories->total(),
            ],
            'categories' => CategoryResource::collection($categories),
        ]);
    }

    public function create(SubcategoryRequest $request)
    {
        $validated = $request->validated();
        $subcategory = Subcategory::create($validated);

        $subcategory->budget()->create([
            'proposed_budget' => 0,
            'january' => 0,
            'february' => 0,
            'march' => 0,
            'april' => 0,
            'may' => 0,
            'june' => 0,
            'july' => 0,
            'august' => 0,
            'september' => 0,
            'october' => 0,
            'november' => 0,
            'december' => 0,
            'profit' => 0,
        ]);

        return redirect()->back()->with([
            'success' => 'Subcategory created successfully',
            'subcategory' => new SubcategoryResource($subcategory)
        ]);
    }

    public function update(SubcategoryRequest $request, Subcategory $subcategory)
    {
        $subCategorySetting = FundSettings::where('name', 'sub_categories')->first();
        if ($subCategorySetting && $subCategorySetting->is_locked) {
            return redirect()->back()->withErrors([
                'subcategories' => 'Subcategories are locked and cannot be modified.'
            ]);
        }

        $validated = $request->validated();
        $subcategory->update($validated);

        return redirect()->back()->with([
            'success' => 'Subcategory updated successfully',
            'subcategory' => new SubcategoryResource($subcategory)
        ]);
    }

    public function destroy(Subcategory $subcategory)
    {
        $subCategorySetting = FundSettings::where('name', 'sub_categories')->first();
        if ($subCategorySetting && $subCategorySetting->is_locked) {
            return redirect()->back()->withErrors([
                'subcategories' => 'Subcategories are locked and cannot be deleted.'
            ]);
        }

        $subcategory->delete();

        return redirect()->back()->with([
            'success' => 'Subcategory deleted successfully',
            'subcategory' => new SubcategoryResource($subcategory)
        ]);
    }
}