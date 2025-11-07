<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHomepageFooterRequest extends FormRequest
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
            'image' => 'nullable|file|image|mimes:jpg,jpeg,png,webp|max:10240', // max 10MB
            'caption' => 'nullable|string',
            'documentation_link' => 'nullable|url',
            'research_paper_link' => 'nullable|url',
            'email_address' => 'nullable|email',
            'facebook_link' => 'nullable|url',
            'youtube_link' => 'nullable|url',
            'footer_subtitle' => 'nullable|string',
            'is_archived' => 'boolean',
        ];
    }
}
