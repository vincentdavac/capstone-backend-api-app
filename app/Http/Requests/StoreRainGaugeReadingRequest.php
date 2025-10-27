<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRainGaugeReadingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'buoy_id' => 'required|exists:buoys,id',
            'rainfall_mm' => 'required|numeric|min:0',
            'tip_count' => 'required|integer|min:0',
            'report_status' => 'required|string',
            'recorded_at' => 'required|date',
        ];
    }
}
