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
            'position' => 'required|integer|min:0'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The category name is required.',
            'name.max' => 'The category name cannot exceed 255 characters.',
            'status.required' => 'Please select a status.',
            'status.in' => 'The status must be either active or inactive.',
            'position.required' => 'Position is required.',
            'position.integer' => 'Position must be a number.',
            'position.min' => 'Position cannot be negative.'
        ];
    }
}