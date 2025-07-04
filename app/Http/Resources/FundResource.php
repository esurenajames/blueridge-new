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
                                    'files' => $transaction->files->map(function ($file) {
                                        return [
                                            'id' => $file->id,
                                            'name' => $file->name,
                                            'path' => $file->path,
                                            'size' => $file->size,
                                            'file_type' => $file->file_type,
                                            'url' => $file->path ? asset('storage/' . $file->path) : null,
                                        ];
                                    }),
                                ];
                            }) : [];
                            return [
                                'id' => $subcategory->id,
                                'budget_id' => $budget->id ?? null,
                                'name' => $subcategory->name,
                                'year' => $budget->year ?? null,
                                'proposedBudget' => $budget->proposed_budget ?? 0,
                                'janJun' => ($budget->january ?? 0) + ($budget->february ?? 0) + ($budget->march ?? 0) +
                                        ($budget->april ?? 0) + ($budget->may ?? 0) + ($budget->june ?? 0),
                                'julDec' => ($budget->july ?? 0) + ($budget->august ?? 0) + ($budget->september ?? 0) +
                                        ($budget->october ?? 0) + ($budget->november ?? 0) + ($budget->december ?? 0),
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
                                'income' => $budget->income ?? 0,
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