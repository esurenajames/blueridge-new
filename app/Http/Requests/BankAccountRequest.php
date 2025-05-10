<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BankAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'bank' => ['required', 'string', 'max:255'],
            'account_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('bank_accounts')->ignore($this->route('bankAccount')),
            ],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The account name is required.',
            'bank.required' => 'The bank name is required.',
            'account_number.required' => 'The account number is required.',
            'account_number.unique' => 'This account number already exists.',
            'status.required' => 'The status is required.',
            'status.in' => 'The status must be either active or inactive.',
        ];
    }
}