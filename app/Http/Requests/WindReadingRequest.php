<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WindReadingRequest extends FormRequest
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
                'buoy_id'        => 'required|integer|exists:buoys,id',
                'wind_speed_m_s' => 'required|numeric|min:0',
                'wind_speed_k_h' => 'required|numeric|min:0',

                // Server-handled, not sent by ESP32
                'recorded_at'    => 'prohibited',
            ];
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return [
                'buoy_id'        => 'sometimes|integer|exists:buoys,id',
                'wind_speed_m_s' => 'sometimes|numeric|min:0',
                'wind_speed_k_h' => 'sometimes|numeric|min:0',
                'recorded_at'    => 'sometimes|date',
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

        return [];
    }

    public function messages(): array
    {
        return [
            'buoy_id.required' => 'Buoy ID is required.',
            'buoy_id.exists'   => 'The selected buoy does not exist.',

            'wind_speed_m_s.required' => 'Wind speed (m/s) is required.',
            'wind_speed_m_s.numeric'  => 'Wind speed (m/s) must be numeric.',
            'wind_speed_m_s.min'      => 'Wind speed (m/s) cannot be negative.',

            'wind_speed_k_h.required' => 'Wind speed (km/h) is required.',
            'wind_speed_k_h.numeric'  => 'Wind speed (km/h) must be numeric.',
            'wind_speed_k_h.min'      => 'Wind speed (km/h) cannot be negative.',

            'recorded_at.prohibited' => 'Recorded time is handled by the server.',
            'recorded_at.date'       => 'Recorded time must be a valid date.',

            // Messages for GET filters
            'from.date' => 'The "from" field must be a valid date.',
            'to.date'   => 'The "to" field must be a valid date.',
            'to.after_or_equal' => 'The "to" date must be after or equal to the "from" date.',
        ];
    }
}
