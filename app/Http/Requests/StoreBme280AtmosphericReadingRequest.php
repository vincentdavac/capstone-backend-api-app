<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBme280AtmosphericReadingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'buoy_id' => 'required|exists:buoys,id',
            'pressure_mbar' => 'required|numeric',
            'pressure_hpa' => 'required|numeric',
            'altitude' => 'required|numeric',
            'report_status' => 'required|string',
        ];
    }
}
