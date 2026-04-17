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
            $categories = collect($this->resource)->where('group_name', $groupName)
                ->sortBy('position')
                ->values();

            $result[] = [
                'group_name' => $groupName,
                'categories' => $categories, // Relies on the controller having eager-loaded subcategories, budgets, and transactions
            ];
        }

        return $result;
    }
}