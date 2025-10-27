<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBme280HumidityReadingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'buoy_id' => 'required|exists:buoys,id',
            'humidity' => 'required|numeric',
            'report_status' => 'required|string',
            'recorded_at' => 'required|date',
        ];
    }
}
