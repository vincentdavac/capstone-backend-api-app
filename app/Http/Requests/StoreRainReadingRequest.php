<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRainReadingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'buoy_id' => 'required|exists:buoys,id',
            'is_raining' => 'required|boolean',
            'analog_value' => 'required|integer|min:0',
            'percentage' => 'required|numeric|min:0|max:100',
            'report_status' => 'required|string',
            'recorded_at' => 'required|date',
        ];
    }
}
