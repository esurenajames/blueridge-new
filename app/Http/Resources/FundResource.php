<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FundResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $groups = ['Beginning Cash Balance', 'Receipts', 'Expenditures', 'MOOE'];
        $result = [];

        foreach ($groups as $groupName) {
            $categories = $this->resource->where('group_name', $groupName)
                ->sortBy('position')
                ->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'position' => $category->position,
                        'subcategories' => $category->subcategories->map(function ($subcategory) {
                            $budget = $subcategory->budget;
                            $transactions = $budget ? $budget->transactions()
                            ->whereYear('transaction_date', $budget->year) 
                            ->latest('transaction_date')
                            ->get()
                            ->map(function($transaction) {
                                return [
                                    'id' => $transaction->id,
                                    'date' => $transaction->transaction_date->format('Y-m-d'),
                                    'type' => $transaction->type,
                                    'amount' => number_format($transaction->total_amount, 2, '.', ''),
                                    'remarks' => $transaction->remarks,
                                    'processed_by' => $transaction->processedBy->name,
                                ];
                            }) : [];
                            return [
                                'id' => $subcategory->id,
                                'budget_id' => $budget->id ?? null,
                                'name' => $subcategory->name,
                                'year' => $budget->year ?? null,
                                'proposedBudget' => $budget->proposed_budget ?? 0,
                                'janJun' => $budget->january + $budget->february + $budget->march + 
                                          $budget->april + $budget->may + $budget->june,
                                'julDec' => $budget->july + $budget->august + $budget->september + 
                                          $budget->october + $budget->november + $budget->december,
                                'january' => $budget->january ?? 0,
                                'february' => $budget->february ?? 0,
                                'march' => $budget->march ?? 0,
                                'april' => $budget->april ?? 0,
                                'may' => $budget->may ?? 0,
                                'june' => $budget->june ?? 0,
                                'july' => $budget->july ?? 0,
                                'august' => $budget->august ?? 0,
                                'september' => $budget->september ?? 0,
                                'october' => $budget->october ?? 0,
                                'november' => $budget->november ?? 0,
                                'december' => $budget->december ?? 0,
                                'ytd' => $budget->ytd ?? 0,
                                'profit' => $budget->profit ?? 0,
                                'balance' => $budget->balance ?? 0,
                                'transactions' => $transactions,
                            ];
                        })->values(),
                    ];
                })->values();

            $result[] = [
                'group_name' => $groupName,
                'categories' => $categories,
            ];
        }

        return $result;
    }
}