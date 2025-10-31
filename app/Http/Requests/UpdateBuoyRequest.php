<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBuoyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'buoy_code' => 'nullable|string|max:255|unique:buoys,buoy_code',
            'river_name' => 'sometimes|string|max:255',
            'wall_height' => 'sometimes|numeric|min:0',
            'river_hectare' => 'sometimes|numeric|min:0',
            'latitude' => 'sometimes|numeric|between:-90,90',
            'longitude' => 'sometimes|numeric|between:-180,180',
            'barangay' => 'sometimes|integer|min:1',
            'attachment' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:2048', // max size 2MB
            'status' => 'nullable|in:active,inactive,maintenance',
            'maintenance_at' => 'nullable|date',
        ];
    }
}
