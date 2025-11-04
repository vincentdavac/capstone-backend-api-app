<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHomepageAboutCardRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow authorized users (adjust if you use policies or gates)
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
            'homepage_about_id' => 'sometimes|exists:homepage_abouts,id',
            'card_title' => 'sometimes|string|max:255',
            'card_description' => 'sometimes|string',
        ];
    }

    /**
     * Custom error messages (optional)
     */
    public function messages(): array
    {
        return [
            'homepage_about_id.exists' => 'The provided about section does not exist.',
            'card_title.max' => 'The card title must not exceed 255 characters.',
        ];
    }
}
