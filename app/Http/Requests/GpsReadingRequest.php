<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GpsReadingRequest extends FormRequest
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
                // Required inputs for creating readings
                'buoy_id'   => 'required|integer|exists:buoys,id',
                'latitude'  => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',

                // Optional fields
                'altitude'   => 'nullable|numeric',
                'satellites' => 'nullable|integer|min:0',

                // Prohibited: calculated/server-side fields
                'recorded_at' => 'prohibited',
            ];
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return [
                'buoy_id'   => 'sometimes|integer|exists:buoys,id',
                'latitude'  => 'sometimes|numeric|between:-90,90',
                'longitude' => 'sometimes|numeric|between:-180,180',

                'altitude'   => 'sometimes|numeric',
                'satellites' => 'sometimes|integer|min:0',

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
            'buoy_id.required' => 'Buoy ID is required.',
            'buoy_id.exists'   => 'The selected buoy does not exist.',

            'latitude.required' => 'Latitude is required.',
            'latitude.between'  => 'Latitude must be between -90 and 90.',

            'longitude.required' => 'Longitude is required.',
            'longitude.between'  => 'Longitude must be between -180 and 180.',

            'altitude.numeric'   => 'Altitude must be a numeric value.',
            'satellites.integer' => 'Satellites must be an integer.',
            'satellites.min'     => 'Satellites cannot be negative.',

            'recorded_at.date' => 'Recorded time must be a valid date.',

            // Messages for GET filters
            'from.date' => 'The "from" field must be a valid date.',
            'to.date'   => 'The "to" field must be a valid date.',
            'to.after_or_equal' => 'The "to" date must be after or equal to the "from" date.',
        ];
    }
}
