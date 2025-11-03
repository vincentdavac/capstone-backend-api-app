<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBarangayRequest extends FormRequest
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
            'barangay_code' => 'nullable|string|unique:barangays,barangay_code',
            'name' => 'required|string',
            'number' => 'required|integer',
            'river_wall_height' => 'nullable|numeric',
            'square_meter' => 'nullable|numeric',
            'hectare' => 'nullable|numeric',
            'white_level_alert' => 'nullable|numeric',
            'blue_level_alert' => 'nullable|numeric',
            'red_level_alert' => 'nullable|string',
            'description' => 'nullable|string',
            'attachment' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10240', // 10MB max
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The barangay name is required.',
            'number.required' => 'The barangay number is required.',
            'attachment.image' => 'The attachment must be an image file.',
            'attachment.max' => 'The image must not exceed 10MB.',
        ];
    }
}
