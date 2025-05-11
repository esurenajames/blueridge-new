<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubcategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255' . $this->subcategory?->id,
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive'
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Please select a parent category.',
            'category_id.exists' => 'The selected category does not exist.',
            'name.required' => 'The subcategory name is required.',
            'name.max' => 'The subcategory name cannot exceed 255 characters.',
            'status.required' => 'Please select a status.',
            'status.in' => 'The status must be either active or inactive.'
        ];
    }
}