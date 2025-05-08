<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequestFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // You can add authorization logic here if needed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules()
    {
        return [
            'name' => 'required|string|min:3',
            'category' => 'required|string',
            'description' => 'required|string|min:10',
            'collaborators' => 'nullable|array',
            'collaborators.*.id' => 'required|exists:users,id',
            'collaborators.*.permission' => 'required|in:view,edit',
            'files' => 'nullable|array',
            'files.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240' // 10MB max size
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The request name is required',
            'name.min' => 'The request name must be at least 3 characters',
            'category.required' => 'Please select a category',
            'category.in' => 'Please select a valid category',
            'description.required' => 'The description is required',
            'description.min' => 'The description must be at least 10 characters',
            'collaborators.*.id.required' => 'Collaborator ID is required',
            'collaborators.*.id.exists' => 'One or more selected collaborators are invalid',
            'collaborators.*.permission.required' => 'Permission is required for each collaborator',
            'collaborators.*.permission.in' => 'Invalid permission type selected',
            'files.*.max' => 'Files must not exceed 10MB in size',
            'files.*.file' => 'The uploaded file is invalid'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    public function prepareForValidation()
    {
        if ($this->has('collaborators') && is_array($this->collaborators)) {
            $collaborators = collect($this->collaborators)->map(function ($collaborator) {
                if (is_array($collaborator) && isset($collaborator['id'])) {
                    return [
                        'id' => $collaborator['id'],
                        'permission' => $collaborator['permission'] ?? 'view'
                    ];
                }
                return null;
            })->filter(); // Remove any null values

            $this->merge([
                'collaborators' => $collaborators->toArray()
            ]);
        }
    }
}
