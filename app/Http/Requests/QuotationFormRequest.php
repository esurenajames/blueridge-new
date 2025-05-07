<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuotationFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'companies' => 'required|array|size:3',
            'companies.*.companyName' => 'required|string|max:255',
            'companies.*.contactPerson' => 'required|string|max:255',
            'companies.*.address' => 'required|string|max:255',
            'companies.*.contactNumber' => 'required|string|max:20',
            'companies.*.email' => 'required|email|max:255',
            'companies.*.items' => 'required|array|min:1',
            'companies.*.items.*.name' => 'required|string|max:255',
            'companies.*.items.*.description' => 'nullable|string',
            'companies.*.items.*.price' => 'required|numeric|min:0',
            'companies.*.items.*.quantity' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'companies.size' => 'Exactly 3 company quotations are required.',
            'companies.*.items.min' => 'Each company must have at least one item.',
            'companies.*.companyName.required' => 'Company name is required for all companies.',
            'companies.*.contactPerson.required' => 'Contact person is required for all companies.',
            'companies.*.address.required' => 'Address is required for all companies.',
            'companies.*.contactNumber.required' => 'Contact number is required for all companies.',
            'companies.*.email.required' => 'Email is required for all companies.',
            'companies.*.email.email' => 'Please provide a valid email address for all companies.',
            'companies.*.items.*.name.required' => 'Item name is required for all items.',
            'companies.*.items.*.price.required' => 'Price is required for all items.',
            'companies.*.items.*.price.min' => 'Price must be greater than or equal to 0.',
            'companies.*.items.*.quantity.required' => 'Quantity is required for all items.',
            'companies.*.items.*.quantity.min' => 'Quantity must be at least 1.',
        ];
    }
}