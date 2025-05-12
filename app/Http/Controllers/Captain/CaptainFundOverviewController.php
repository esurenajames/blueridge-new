<?php

namespace App\Http\Controllers\Captain;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CaptainFundOverviewController extends Controller
{
    public function index()
    {
        $groups = Category::with(['subcategories' => function($q) {
                $q->active();
            }])
            ->active()
            ->where('name', 'not like', '%money%') // Exclude money-related categories
            ->get()
            ->groupBy('group_name')
            ->map(function($categories, $groupName) {
                return [
                    'group_name' => $groupName,
                    'categories' => $categories->map(function($category) {
                        return [
                            'id' => $category->id,
                            'name' => $category->name,
                            'subcategories' => $category->subcategories->map(function($sub) {
                                return [
                                    'id' => $sub->id,
                                    'name' => $sub->name,
                                    // Add your budget details here, e.g.:
                                    // 'proposedBudget' => $sub->proposed_budget,
                                    // ...other fields...
                                ];
                            }),
                        ];
                    })->values(),
                ];
            })->values();

        return Inertia::render('captain/CaptainFundOverview', [
            'budgetGroups' => $groups,
        ]);
    }
}