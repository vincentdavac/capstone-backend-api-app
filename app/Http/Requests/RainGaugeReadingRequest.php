<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RainGaugeReadingRequest extends FormRequest
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
                'buoy_id'     => 'required|integer|exists:buoys,id',
                'rainfall_mm' => 'required|numeric|min:0',
                'tip_count'   => 'required|integer|min:0',
                'recorded_at' => 'prohibited',
            ];
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return [
                'buoy_id'     => 'sometimes|integer|exists:buoys,id',
                'rainfall_mm' => 'sometimes|numeric|min:0',
                'tip_count'   => 'sometimes|integer|min:0',
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

        return [];
    }

    public function messages(): array
    {
        return [
            'buoy_id.required' => 'Buoy ID is required.',
            'buoy_id.exists'   => 'The selected buoy does not exist.',

            'rainfall_mm.required' => 'Rainfall (mm) is required.',
            'rainfall_mm.numeric'  => 'Rainfall must be a valid number.',
            'rainfall_mm.min'      => 'Rainfall cannot be negative.',

            'tip_count.required' => 'Tip count is required.',
            'tip_count.integer'  => 'Tip count must be an integer.',
            'tip_count.min'      => 'Tip count cannot be negative.',

            'recorded_at.prohibited' => 'Recorded time is handled by the server.',
            'recorded_at.date'       => 'Recorded time must be a valid date.',

            // Messages for GET filters
            'from.date' => 'The "from" field must be a valid date.',
            'to.date'   => 'The "to" field must be a valid date.',
            'to.after_or_equal' => 'The "to" date must be after or equal to the "from" date.',
        ];
    }
}
