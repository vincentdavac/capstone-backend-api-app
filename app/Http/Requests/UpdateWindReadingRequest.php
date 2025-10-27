<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWindReadingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'wind_speed_m_s' => 'sometimes|numeric',
            'wind_speed_k_h' => 'sometimes|numeric',
            'report_status' => 'sometimes|string',
            'recorded_at' => 'sometimes|date',
        ];
    }
}
