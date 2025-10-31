<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBme280TemperatureReadingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'buoy_id' => 'required|exists:buoys,id',
            'temperature_celsius' => 'required|numeric',
            'temperature_fahrenheit' => 'required|numeric',
        ];
    }
}
