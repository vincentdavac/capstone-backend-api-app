<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
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
        if ($this->isMethod('post')) {
            // Validation rules for creating a message
            return [
                'chat_id'    => 'sometimes|exists:chats,id',
                'sender_id'  => 'sometimes|exists:users,id',
                'receiver_id'  => 'sometimes|exists:users,id',
                'message'    => 'nullable|string',
                'attachment' => 'nullable|file|max:10240', // 10MB
                'is_read'    => 'sometimes|boolean',
            ];
        }

        if ($this->isMethod('patch') || $this->isMethod('put')) {
            // Validation rules for updating a message
            return [
                'chat_id'    => 'sometimes|exists:chats,id',
                'sender_id'  => 'sometimes|exists:users,id',
                'receiver_id'  => 'sometimes|exists:users,id',
                'message'    => 'sometimes|string',
                'attachment' => 'sometimes|file|max:10240', // optional file
                'is_read'    => 'sometimes|boolean',
            ];
        }

        // Default fallback (no rules)
        return [];
    }

    public function messages(): array
    {
        return [
            'attachment.max' => 'The attachment must not exceed 10 MB.',
        ];
    }
}
