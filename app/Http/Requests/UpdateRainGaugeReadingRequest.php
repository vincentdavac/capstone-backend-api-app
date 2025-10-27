<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRainGaugeReadingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rainfall_mm' => 'sometimes|numeric|min:0',
            'tip_count' => 'sometimes|integer|min:0',
            'report_status' => 'sometimes|string',
            'recorded_at' => 'sometimes|date',
        ];
    }
}
