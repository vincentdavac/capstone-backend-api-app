<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHomepagePrototype extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow authorized users to update; set to true for now
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
            'title' => 'sometimes|required|string',
            'description' => 'sometimes|nullable|string',
            'image' => 'sometimes|nullable|file|mimes:jpg,jpeg,png,webp|max:10240', // max:10240 = 10MB
            'position' => 'sometimes|required|in:left,right', // Only accepts "left" or "right"
            'is_archived' => 'sometimes|boolean',
        ];
    }
}
