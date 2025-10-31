<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBatteryHealthRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'buoy_id'    => 'required|exists:buoys,id',
            'percentage' => 'required|numeric|between:0,100',
            'voltage'    => 'required|numeric|min:0',
        ];
    }
}
