<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHomepageTeam extends FormRequest
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
            'name' => 'required|string',
            'role' => 'required|string',
            'image' => 'required|string',
            'image_url' => 'nullable|url',
            'facebook_link' => 'nullable|string',
            'twitter_link' => 'nullable|string',
            'linkedin_link' => 'nullable|string',
            'instagram_link' => 'nullable|string',
            'is_active' => 'required|boolean',
        ];
    }
}
