<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHomepageFeedbackRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Allow authorized users to update
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id'   => 'sometimes|exists:users,id',
            'rate'      => 'sometimes|integer|min:1|max:5',
            'feedback'  => 'sometimes|string',
            'is_archived' => 'sometimes|boolean',
        ];
    }
}
