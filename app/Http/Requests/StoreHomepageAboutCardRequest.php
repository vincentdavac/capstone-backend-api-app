<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHomepageAboutCardRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'homepage_about_id' => 'sometimes|exists:homepage_abouts,id',
            'card_title' => 'required|string|max:255',
            'card_description' => 'required|string',
            'is_archive' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'homepage_about_id.required' => 'The about section reference is required.',
            'homepage_about_id.exists' => 'The specified about section does not exist.',
            'card_title.required' => 'The card title is required.',
            'card_title.max' => 'The card title must not exceed 255 characters.',
            'card_description.required' => 'The card description is required.',
            'is_archive.boolean' => 'The archive value must be true or false.',
        ];
    }
}
