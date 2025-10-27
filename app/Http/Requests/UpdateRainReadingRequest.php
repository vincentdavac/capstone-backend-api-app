<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRainReadingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_raining' => 'sometimes|boolean',
            'analog_value' => 'sometimes|integer|min:0',
            'percentage' => 'sometimes|numeric|min:0|max:100',
            'report_status' => 'sometimes|string',
            'recorded_at' => 'sometimes|date',
        ];
    }
}
