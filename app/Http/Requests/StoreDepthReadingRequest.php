<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepthReadingRequest extends FormRequest
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
            'depth_m' => 'required|numeric',
            'depth_ft' => 'required|numeric',
            'water_altitude' => 'nullable|numeric',
        ];
    }
}
