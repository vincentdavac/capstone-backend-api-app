<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BatteryHealthRequest extends FormRequest
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
                'voltage'    => 'required|numeric|min:0',

                // Server-handled field: ESP32 should not send
                'recorded_at' => 'prohibited',
            ];
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return [
                'buoy_id'    => 'sometimes|integer|exists:buoys,id',
                'percentage' => 'sometimes|numeric|between:0,100',
                'voltage'    => 'sometimes|numeric|min:0',

                // Optional for updates
                'recorded_at' => 'sometimes|date',
            ];
        }

        // For GET / fetch requests
        if ($this->isMethod('get')) {
            return [
                'from'     => 'nullable|date_format:Y-m-d\TH:i',
                'to'       => 'nullable|date_format:Y-m-d\TH:i|after_or_equal:from',
                'buoy_id'  => 'required|integer|exists:buoys,id',
            ];
        }
        // Default fallback
        return [];
    }

    public function messages(): array
    {
        return [
            'buoy_id.required'    => 'Buoy ID is required.',
            'buoy_id.exists'      => 'The selected buoy does not exist.',

            'percentage.required' => 'Battery percentage is required.',
            'percentage.between'  => 'Battery percentage must be between 0 and 100.',

            'voltage.required'    => 'Voltage is required.',
            'voltage.min'         => 'Voltage must be at least 0.',

            'recorded_at.date'    => 'Recorded time must be a valid date.',


            // Messages for GET filters
            'from.date' => 'The "from" field must be a valid date.',
            'to.date'   => 'The "to" field must be a valid date.',
            'to.after_or_equal' => 'The "to" date must be after or equal to the "from" date.',
        ];
    }
}
