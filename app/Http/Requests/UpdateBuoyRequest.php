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
            'buoy_code' => 'sometimes|string|max:255',
            'location_name' => 'sometimes|string|max:255',
            'status' => 'sometimes|in:active,inactive,maintenance',
            'is_active' => 'sometimes|boolean',
            'installed_at' => 'nullable|date',
            'maintenance_at' => 'nullable|date',
        ];
    }
}
