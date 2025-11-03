<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHomepageAboutCardRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow authorized users to create cards (adjust if needed)
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
            'homepage_about_id' => 'required|exists:homepage_abouts,id',
            'card_title' => 'required|string|max:255',
            'card_description' => 'required|string',
        ];
    }

    /**
     * Custom error messages (optional)
     */
    public function messages(): array
    {
        return [
            'homepage_about_id.required' => 'The about section reference is required.',
            'homepage_about_id.exists' => 'The specified about section does not exist.',
            'card_title.required' => 'The card title is required.',
            'card_title.max' => 'The card title must not exceed 255 characters.',
            'card_description.required' => 'The card description is required.',
        ];
    }
}
