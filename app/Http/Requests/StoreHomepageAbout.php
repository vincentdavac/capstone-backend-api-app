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
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'image_link' => 'nullable|url',

            'side_title' => 'required|string',
            'side_description' => 'required|string',

            'first_card_title' => 'required|string',
            'first_card_description' => 'required|string',

            'second_card_title' => 'required|string',
            'second_card_description' => 'required|string',

            'third_card_title' => 'required|string',
            'third_card_description' => 'required|string',
        ];
    }
}
