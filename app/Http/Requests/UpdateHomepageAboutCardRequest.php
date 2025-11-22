<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHomepageAboutCardRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'homepage_about_id' => 'sometimes|exists:homepage_abouts,id',
            'card_title' => 'sometimes|string|max:255',
            'card_description' => 'sometimes|string',
            'is_archive' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'homepage_about_id.exists' => 'The provided about section does not exist.',
            'card_title.max' => 'The card title must not exceed 255 characters.',
            'is_archive.boolean' => 'The archive value must be true or false.',
        ];
    }
}
