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
            'buoy_code' => 'required|string|max:255',
            'location_name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive,maintenance',
            'is_active' => 'required|boolean',
            'installed_at' => 'nullable|date',
            'maintenance_at' => 'nullable|date',
        ];
    }
}
