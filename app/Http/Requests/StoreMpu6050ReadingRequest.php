<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMpu6050ReadingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'buoy_id' => 'required|exists:buoys,id',
            'accel_x' => 'required|numeric',
            'accel_y' => 'required|numeric',
            'accel_z' => 'required|numeric',
            'gyro_x'  => 'required|numeric',
            'gyro_y'  => 'required|numeric',
            'gyro_z'  => 'required|numeric',
            'report_status' => 'required|string',
            'recorded_at' => 'required|date',
        ];
    }
}
