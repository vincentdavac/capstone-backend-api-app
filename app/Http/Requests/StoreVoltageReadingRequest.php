<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVoltageReadingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'buoy_id' => 'required|exists:buoys,id',
            'voltage_percentage' => 'required|numeric|between:0,100',
            'report_status' => 'required|string',
            'recorded_at' => 'required|date',
        ];
    }
}
