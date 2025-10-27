<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRelayStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'buoy_id' => 'required|exists:buoys,id',
            'relay_state' => 'required|boolean',
            'report_status' => 'required|in:On,Off',
            'recorded_at' => 'required|date',
        ];
    }
}
