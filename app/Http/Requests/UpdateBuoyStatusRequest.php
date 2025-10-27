<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBuoyStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'latitude' => 'sometimes|numeric|between:-90,90',
            'longitude' => 'sometimes|numeric|between:-180,180',
            'battery_health' => 'sometimes|numeric|between:0,100',
            'alert' => 'sometimes|in:on,off',
            'recorded_at' => 'sometimes|date',
        ];
    }
}
