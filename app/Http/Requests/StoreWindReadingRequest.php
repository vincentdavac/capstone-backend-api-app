<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWindReadingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'buoy_id' => 'required|exists:buoys,id',
            'wind_speed_m_s' => 'required|numeric',
            'wind_speed_k_h' => 'required|numeric',
            'report_status' => 'required|string',
            'recorded_at' => 'required|date',
        ];
    }
}
