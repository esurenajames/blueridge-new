<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255' . $this->category?->id,
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'group_name' => 'required|in:Beginning Cash Balance,Receipts,Expenditures,MOOE'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The category name is required.',
            'name.max' => 'The category name cannot exceed 255 characters.',
            'status.required' => 'Please select a status.',
            'status.in' => 'The status must be either active or inactive.',
            'group_name.required' => 'Group name is required.',
            'group_name.in' => 'Invalid group name selected.'
        ];
    }
}