<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BME280DataRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Set to true so API / ESP32 / external clients can send data
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if ($this->isMethod('post')) {
            return [
                // Allowed inputs (ONLY these)
                'buoy_id' => 'required|integer|exists:buoys,id',
                'temperature_celsius' => 'required|numeric',
                'humidity' => 'required|numeric|min:0|max:100',
                'pressure_hpa' => 'required|numeric',
                'altitude' => 'required|numeric',

                //  Explicitly forbidden inputs
                'temperature_fahrenheit' => 'prohibited',
                'pressure_mbar' => 'prohibited',
                'recorded_at' => 'prohibited',
            ];
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            // Validation rules for updating BME280 data
            return [
                'buoy_id' => 'sometimes|integer|exists:buoys,id',

                'temperature_celsius' => 'sometimes|numeric',
                'temperature_fahrenheit' => 'sometimes|numeric',

                'humidity' => 'sometimes|numeric|min:0|max:100',

                'pressure_mbar' => 'sometimes|numeric',
                'pressure_hpa' => 'sometimes|numeric',

                'altitude' => 'sometimes|numeric',

                'recorded_at' => 'sometimes|date',
            ];
        }

        // Fallback
        return [];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return [
            'buoy_id.required' => 'Buoy ID is required.',
            'buoy_id.exists' => 'The selected buoy does not exist.',

            'humidity.max' => 'Humidity must not exceed 100%.',
            'humidity.min' => 'Humidity cannot be negative.',

            'recorded_at.required' => 'Recorded time is required.',
            'recorded_at.date' => 'Recorded time must be a valid date.',
        ];
    }
}
