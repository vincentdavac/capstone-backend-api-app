<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
        $userId = $this->route('user');

        return [
            // ✅ Optional profile image (up to 10MB)
            'image' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],

            // ✅ Optional ID document (up to 20MB, supports PDF)
            'id_document' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:20480'],

            'first_name'     => ['nullable', 'string', 'max:255'],
            'last_name'      => ['nullable', 'string', 'max:255'],
            'email'          => ['nullable', 'email', 'max:255', Rule::unique('users')->ignore($userId)],

            // ✅ Contact number: must start with 09 and be exactly 11 digits
            'contact_number' => ['nullable', 'regex:/^(09)\d{9}$/'],

            'house_no'       => ['nullable', 'string', 'max:255'],
            'street'         => ['nullable', 'string', 'max:255'],
            'barangay_id'    => ['nullable', 'exists:barangays,id'],
            'municipality'   => ['nullable', 'string', 'max:255'],

            // ✅ Password: optional but must meet complexity rules if provided
            'password' => [
                'nullable',
                'string',
                'min:8',
                'regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/'
            ],

            // ✅ Status and verification
            'registration_status' => ['nullable', 'boolean'],
            'date_verified'       => ['nullable', 'date'],
            'verified_by'         => ['nullable', 'exists:users,id'],

            // ✅ User type control
            'user_type'           => ['nullable', Rule::in(['admin', 'barangay', 'user'])],

            'is_active'           => ['nullable', 'boolean'],
            'is_admin'            => ['nullable', 'boolean'],
        ];
    }

    /**
     * Customize the error messages for specific fields.
     */
    public function messages(): array
    {
        return [
            'email.email'              => 'Please provide a valid email address.',
            'email.unique'             => 'This email is already taken by another user.',

            'contact_number.regex'     => 'The contact number must start with 09 and be 11 digits long.',

            'password.min'             => 'Password must be at least 8 characters long.',
            'password.regex'           => 'Password must contain at least one uppercase letter, one number, and one special character.',

            'image.file'               => 'The uploaded file must be a valid image.',
            'image.mimes'              => 'Allowed image types are: jpeg, png, jpg, gif, and webp.',
            'image.max'                => 'Image size must not exceed 10MB.',

            'id_document.file'         => 'The ID document must be a valid file.',
            'id_document.mimes'        => 'Allowed document types are: jpeg, png, jpg, and pdf.',
            'id_document.max'          => 'ID document size must not exceed 20MB.',

            'barangay_id.exists'       => 'The selected barangay is invalid.',
            'registration_status.boolean' => 'The registration status must be true or false.',
            'is_active.boolean'        => 'The active status must be true or false.',
            'is_admin.boolean'         => 'The admin status must be true or false.',
            'user_type.in'             => 'User type must be one of: admin, barangay, or user.',
            'verified_by.exists'       => 'The verifying user is invalid.',
        ];
    }
}
