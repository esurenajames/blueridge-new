<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoryId = $this->category?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')->ignore($categoryId)
            ],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
            'group_name' => [
                'required', 
                'string',
                Rule::in(['Beginning Cash Balance', 'Receipts', 'Expenditures', 'MOOE'])
            ],
            'position' => [
                'sometimes',
                'integer',
                'min:1',
                Rule::unique('categories')
                    ->where('group_name', $this->group_name)
                    ->ignore($categoryId)
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The category name is required.',
            'name.max' => 'The category name cannot exceed 255 characters.',
            'name.unique' => 'This category name already exists.',
            'status.required' => 'Please select a status.',
            'status.in' => 'The status must be either active or inactive.',
            'group_name.required' => 'Group name is required.',
            'group_name.in' => 'Invalid group name selected.',
            'position.integer' => 'Position must be a number.',
            'position.min' => 'Position must be at least 1.',
            'position.unique' => 'This position is already taken in the selected group.'
        ];
    }
}