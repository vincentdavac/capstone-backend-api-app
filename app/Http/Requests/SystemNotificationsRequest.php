<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SystemNotificationsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Admin and Barangay users are allowed.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // CREATE
        if ($this->isMethod('post')) {
            return [
                'sender_id'     => 'nullable|exists:users,id',
                'receiver_id'   => 'nullable|exists:users,id',
                'barangay_id'   => 'nullable|exists:barangays,id',

                'receiver_role' => 'required|in:admin,barangay,user',
                'title'         => 'required|string|max:150',
                'body'          => 'required|string',

                'status'        => 'sometimes|in:read,unread',
                'read_at'       => 'nullable|date',
            ];
        }

        // UPDATE
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return [
                'receiver_id'   => 'sometimes|nullable|exists:users,id',
                'barangay_id'   => 'sometimes|nullable|exists:barangays,id',

                'receiver_role' => 'sometimes|in:admin,barangay,user',
                'title'         => 'sometimes|string|max:150',
                'body'          => 'sometimes|string',

                'status'        => 'sometimes|in:read,unread',
                'read_at'       => 'nullable|date',
            ];
        }

        return [];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'receiver_role.required' => 'The receiver role is required.',
            'receiver_role.in'       => 'The receiver role must be admin, barangay, or user.',

            'receiver_id.exists'     => 'The selected receiver does not exist.',
            'barangay_id.exists'     => 'The selected barangay does not exist.',

            'title.required'         => 'The notification title is required.',
            'title.max'              => 'The notification title must not exceed 150 characters.',

            'body.required'          => 'The notification message is required.',

            'status.in'              => 'The status must be either read or unread.',
            'read_at.date'           => 'The read at value must be a valid date.',
        ];
    }
}
