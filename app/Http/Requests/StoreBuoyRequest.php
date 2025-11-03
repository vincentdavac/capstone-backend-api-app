<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBuoyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'buoy_code' => 'nullable|string|max:255|unique:buoys,buoy_code',
            'river_name' => 'required|string|max:255',
            'wall_height' => 'required|numeric|min:0',
            'river_hectare' => 'required|numeric|min:0',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'barangay_id' => 'required|integer|min:1',
            'attachment' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:10240',
            'status' => 'nullable|in:active,inactive,maintenance',
            'maintenance_at' => 'nullable|date',
        ];
    }
}
