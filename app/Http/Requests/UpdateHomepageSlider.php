<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHomepageSlider extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // allow authorized users to update
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240', // optional 10MB image
            'title' => 'sometimes|required|string|max:255',                // only validate if provided
            'description' => 'nullable|string',
            'is_archive' => 'sometimes|required|boolean',                  // optional but validated if present
        ];
    }
}
