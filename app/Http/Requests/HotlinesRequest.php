<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HotlinesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Admin and Barangay users are allowed.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // CREATE
        if ($this->isMethod('post')) {
            return [
                'barangay_id' => 'nullable|exists:barangays,id',
                'number'      => 'required|string|max:30',
                'description' => 'nullable|string|max:255',
                'is_archive'  => 'sometimes|boolean',
                'is_global'    => 'sometimes|boolean',

            ];
        }

        // UPDATE
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return [
                'barangay_id' => 'nullable|exists:barangays,id',
                'number'      => 'sometimes|string|max:30',
                'description' => 'sometimes|string|max:255',
                'is_archive'  => 'sometimes|boolean',
                'is_global'    => 'sometimes|boolean',

            ];
        }

        return [];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'barangay_id.exists' => 'The selected barangay does not exist.',
            'number.required'    => 'The hotline number is required.',
            'number.max'         => 'The hotline number must not exceed 30 characters.',
            'description.max'    => 'The description must not exceed 255 characters.',
        ];
    }
}
