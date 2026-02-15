<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RainSensorReadingRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Allow API / ESP32 / external clients
        return true;
    }

    public function rules(): array
    {
        if ($this->isMethod('post')) {
            return [
                // Required inputs for POST
                'buoy_id'    => 'required|integer|exists:buoys,id',
                'percentage' => 'required|numeric|between:0,100',

                // Server-handled field (ESP32 must NOT send this)
                'recorded_at' => 'prohibited',
            ];
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return [
                'buoy_id'    => 'sometimes|integer|exists:buoys,id',
                'percentage' => 'sometimes|numeric|between:0,100',

                // Optional for updates
                'recorded_at' => 'sometimes|date',
            ];
        }

        // Fallback
        return [];
    }

    public function messages(): array
    {
        return [
            'buoy_id.required'   => 'Buoy ID is required.',
            'buoy_id.exists'     => 'The selected buoy does not exist.',

            'percentage.required' => 'Rain percentage is required.',
            'percentage.between' => 'Rain percentage must be between 0 and 100.',

            'recorded_at.date'   => 'Recorded time must be a valid date.',
        ];
    }
}
