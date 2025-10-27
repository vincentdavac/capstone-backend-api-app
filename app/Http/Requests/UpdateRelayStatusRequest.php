<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRelayStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'relay_state' => 'sometimes|boolean',
            'report_status' => 'sometimes|in:On,Off',
            'recorded_at' => 'sometimes|date',
        ];
    }
}
