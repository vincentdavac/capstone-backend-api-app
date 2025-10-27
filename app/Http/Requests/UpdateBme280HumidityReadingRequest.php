<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBme280HumidityReadingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'humidity' => 'sometimes|numeric',
            'report_status' => 'sometimes|string',
            'recorded_at' => 'sometimes|date',
        ];
    }
}
