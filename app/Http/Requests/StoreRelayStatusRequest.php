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
            'buoy_id'     => 'sometimes|exists:buoys,id',
            'relay_state' => 'required|string|in:on,off',
            'buoy_code'   => 'required|string|exists:buoys,buoy_code',
        ];
    }
}
