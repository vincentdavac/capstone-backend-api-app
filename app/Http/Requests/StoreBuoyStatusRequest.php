<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBuoyStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'buoy_id' => 'required|exists:buoys,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'battery_health' => 'required|numeric|between:0,100',
            'alert' => 'required|in:on,off',
            'recorded_at' => 'required|date',
        ];
    }
}
