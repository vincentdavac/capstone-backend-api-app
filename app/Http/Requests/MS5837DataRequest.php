<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MS5837DataRequest extends FormRequest
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
                'buoy_id' => 'required|integer|exists:buoys,id',

                'temperature_celsius'     => 'required|numeric',
                'temperature_fahrenheit'  => 'prohibited', // now server computes this

                'depth_m'  => 'required|numeric|min:0',
                'depth_ft' => 'required|numeric|min:0',

                'water_altitude' => 'required|numeric',
                'water_pressure' => 'required|numeric|min:0',

                'recorded_at' => 'prohibited', // server sets automatically
            ];
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return [
                'buoy_id' => 'sometimes|integer|exists:buoys,id',

                'temperature_celsius'     => 'sometimes|numeric',
                'temperature_fahrenheit'  => 'sometimes|numeric', // optional on updates

                'depth_m'  => 'sometimes|numeric|min:0',
                'depth_ft' => 'sometimes|numeric|min:0',

                'water_altitude' => 'sometimes|numeric',
                'water_pressure' => 'sometimes|numeric|min:0',

                'recorded_at' => 'sometimes|date',
            ];
        }

        return [];
    }

    public function messages(): array
    {
        return [
            'buoy_id.required' => 'Buoy ID is required.',
            'buoy_id.exists'   => 'The selected buoy does not exist.',

            'temperature_celsius.required' => 'Temperature (°C) is required.',
            'temperature_fahrenheit.prohibited' => 'Temperature (°F) is handled by the server.',

            'depth_m.min'  => 'Depth in meters cannot be negative.',
            'depth_ft.min' => 'Depth in feet cannot be negative.',

            'water_pressure.min' => 'Water pressure cannot be negative.',

            'recorded_at.prohibited' => 'Recorded time is handled by the server.',
            'recorded_at.date'       => 'Recorded time must be a valid date.',
        ];
    }
}
