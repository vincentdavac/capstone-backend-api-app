<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHomepageFeedback extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
return [
    'name' => 'required|string|max:255',
    'role' => 'required|string|max:255',
    'image' => 'nullable|string|max:255',
    'image_url' => 'nullable|url',
    'rate' => 'required|numeric|min:1|max:5',
    'feedback' => 'required|string',
    'is_active' => 'boolean',
];
    }
}