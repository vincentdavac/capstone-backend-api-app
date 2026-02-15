<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RelayStatusRequest extends FormRequest
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
                'buoy_id'     => 'required|integer|exists:buoys,id',
                'relay_state' => 'required|string|in:on,off',

                // Server-handled fields (ESP32 or client must NOT send these)
                'triggered_by' => 'prohibited',
                'recorded_at'  => 'prohibited',
            ];
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return [
                // Optional updates
                'buoy_id'     => 'sometimes|integer|exists:buoys,id',
                'relay_state' => 'sometimes|string|in:on,off',
                'buoy_code'   => 'sometimes|string|exists:buoys,buoy_code',

                // Optional server-handled field
                'triggered_by' => 'prohibited',
                'recorded_at'  => 'sometimes|date',
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

        // Fallback
        return [];
    }

    public function messages(): array
    {
        return [
            'buoy_id.required'    => 'Buoy ID is required.',
            'buoy_id.exists'      => 'The selected buoy does not exist.',

            'relay_state.required' => 'Relay state is required.',
            'relay_state.in'       => 'Relay state must be either "on" or "off".',

            'buoy_code.required'   => 'Buoy code is required.',
            'buoy_code.exists'     => 'The provided buoy code does not exist.',

            'triggered_by.prohibited' => 'Triggered_by is set automatically by the server.',
            'recorded_at.prohibited'  => 'Recorded_at is set automatically by the server.',
            'recorded_at.date'        => 'Recorded_at must be a valid date.',

            // Messages for GET filters
            'from.date' => 'The "from" field must be a valid date.',
            'to.date'   => 'The "to" field must be a valid date.',
            'to.after_or_equal' => 'The "to" date must be after or equal to the "from" date.',
        ];
    }
}
