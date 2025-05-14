<?php

namespace App\Http\Controllers\Captain;

use App\Http\Controllers\Controller;
use App\Http\Requests\FundRequest;
use App\Http\Resources\FundResource;
use App\Models\Budget;
use App\Models\Category;
use App\Models\FundSettings;
use App\Models\FundTransactionHistory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class CaptainFundOverviewController extends Controller
{
    public function index()
    {
        $currentMonth = Carbon::now()->format('F');
        $currentYear = Carbon::now()->year; // Get current year
        $months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];
        
        $currentMonthIndex = array_search($currentMonth, $months);
        
        $monthFields = [
            'january', 'february', 'march', 'april', 'may', 'june',
            'july', 'august', 'september', 'october', 'november', 'december'
        ];

        $categories = Category::with(['subcategories' => function($q) use ($currentYear) {
                $q->active()->with(['budget' => function($b) use ($currentYear) {
                    $b->where('year', $currentYear);
                }]);
            }])
            ->active()
            ->where('name', 'not like', '%money%')
            ->get()
            ->map(function ($category) use ($currentMonthIndex, $monthFields) {
                $category->subcategories->transform(function ($subcategory) use ($currentMonthIndex, $monthFields) {
                    if ($subcategory->budget) {
                        // Calculate YTD
                        $ytd = 0;
                        for ($i = 0; $i <= $currentMonthIndex; $i++) {
                            $ytd += $subcategory->budget->{$monthFields[$i]} ?? 0;
                        }
                        $subcategory->budget->ytd = $ytd;

                        // Compute balance: (proposed_budget - sum(jan-dec)) + profit
                        $sumMonths = 0;
                        foreach ($monthFields as $month) {
                            $sumMonths += $subcategory->budget->{$month} ?? 0;
                        }
                        $subcategory->budget->balance = 
                            ($subcategory->budget->proposed_budget ?? 0)
                            - $sumMonths
                            + ($subcategory->budget->profit ?? 0);
                    }
                    return $subcategory;
                });
                return $category;
            });

        return Inertia::render('captain/CaptainFundOverview', [
            'budgetGroups' => (new FundResource($categories))->resolve(),
        ]);
    }

    public function addProfit(FundRequest $request, Budget $budget)
    {
        $budget->increment('profit', $request->amount);

        // Always use current time for transaction_date (with seconds)
        $transactionDate = now()->format('Y-m-d H:i:s');

        FundTransactionHistory::create([
            'budget_id' => $budget->id,
            'processed_by' => auth()->id(),
            'transaction_date' => $transactionDate,
            'type' => 'profit',
            'total_amount' => $request->amount,
            'remarks' => $request->remarks,
        ]);

        return redirect()->back()->with([
            'message' => 'Profit added successfully',
            'budget' => $budget->fresh()
        ]);
    }

    public function addProposedBudget(FundRequest $request, Budget $budget)
    {
        $budgetSetting = FundSettings::where('name', 'budget')->first();
        if ($budgetSetting && $budgetSetting->is_locked) {
            return redirect()->back()->withErrors([
                'proposed_budget' => 'Proposed budget is locked and cannot be modified.'
            ]);
        }

        $validated = $request->validated();

        $budget->increment('proposed_budget', $validated['amount']);

        // Always use current time for transaction_date (with seconds)
        $transactionDate = now()->format('Y-m-d H:i:s');

        FundTransactionHistory::create([
            'budget_id' => $budget->id,
            'processed_by' => auth()->id(),
            'transaction_date' => $transactionDate,
            'type' => 'proposed budget',
            'total_amount' => $validated['amount'],
            'remarks' => $validated['remarks'] ?? null,
        ]);

        return redirect()->back()->with([
            'message' => 'Proposed budget added successfully',
            'budget' => $budget->fresh()
        ]);
    }
}