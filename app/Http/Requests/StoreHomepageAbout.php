<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHomepageAbout extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
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
        return [
            'title' => 'required|string',
            'caption' => 'required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'side_title' => 'nullable|string',
            'side_description' => 'nullable|string',
            'video_link' => 'nullable|url', // added validation for video link
            'is_archived' => 'boolean',
        ];
    }

    /**
     * Custom error messages (optional)
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The about title is required.',
            'caption.required' => 'The about caption is required.',
            'image.image' => 'The uploaded file must be a valid image.',
            'image.mimes' => 'The image must be a file of type: jpg, jpeg, png, webp.',
            'video_link.url' => 'The video link must be a valid URL.', // custom message for video link
        ];
    }
}
