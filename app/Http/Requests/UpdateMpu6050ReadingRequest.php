<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMpu6050ReadingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'accel_x' => 'sometimes|numeric',
            'accel_y' => 'sometimes|numeric',
            'accel_z' => 'sometimes|numeric',
            'gyro_x'  => 'sometimes|numeric',
            'gyro_y'  => 'sometimes|numeric',
            'gyro_z'  => 'sometimes|numeric',
            'report_status' => 'sometimes|string',
            'recorded_at' => 'sometimes|date',
        ];
    }
}
