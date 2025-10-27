<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGpsReadingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'latitude' => 'sometimes|numeric|between:-90,90',
            'longitude' => 'sometimes|numeric|between:-180,180',
            'altitude' => 'sometimes|numeric',
            'satellites' => 'sometimes|integer|min:0',
            'report_status' => 'sometimes|string',
            'recorded_at' => 'sometimes|date',
        ];
    }
}
