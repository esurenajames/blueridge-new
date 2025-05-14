<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FundSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Update this as needed for your auth logic
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'settings' => 'required|array',
            'settings.*.id' => 'required|integer|exists:budget_settings,id',
            'settings.*.is_locked' => 'required|boolean',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'settings.required' => 'Settings data is required.',
            'settings.array' => 'Settings must be an array.',
            'settings.*.id.required' => 'Each setting must have an ID.',
            'settings.*.id.integer' => 'The setting ID must be an integer.',
            'settings.*.id.exists' => 'The selected setting does not exist.',
            'settings.*.is_locked.required' => 'Each setting must specify if it is locked.',
            'settings.*.is_locked.boolean' => 'The is_locked value must be true or false.',
        ];
    }
}