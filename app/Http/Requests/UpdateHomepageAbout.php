<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHomepageAbout extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow all authenticated or authorized users (adjust if needed)
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
            'title' => 'sometimes|string',
            'caption' => 'sometimes|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'side_title' => 'sometimes|nullable|string',
            'side_description' => 'sometimes|nullable|string',
            'video_link' => 'sometimes|nullable|url', // added validation for video link
            'is_archived' => 'sometimes|boolean',
        ];
    }

    /**
     * Custom error messages (optional)
     */
    public function messages(): array
    {
        return [
            'image.image' => 'The uploaded file must be a valid image.',
            'image.mimes' => 'The image must be one of the following types: jpg, jpeg, png, webp.',
            'video_link.url' => 'The video link must be a valid URL.', // custom message for video link
        ];
    }
}
