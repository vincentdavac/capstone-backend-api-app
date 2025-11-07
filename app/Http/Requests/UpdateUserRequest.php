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
            // ✅ Allow uploading an image file up to 10MB
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],

            // ✅ Optional image URL
            'image_url' => ['nullable', 'string', 'max:255'],

            'first_name'     => ['nullable', 'string', 'max:255'],
            'last_name'      => ['nullable', 'string', 'max:255'],
            'email'          => ['nullable', 'email', 'max:255', Rule::unique('users')->ignore($userId)],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'house_no'       => ['nullable', 'string', 'max:255'],
            'street'         => ['nullable', 'string', 'max:255'],
            'barangay_id'    => ['nullable', 'exists:barangays,id'], // ✅ foreign key validation
            'municipality'   => ['nullable', 'string', 'max:255'],
            'password'       => ['nullable', 'string', 'min:8'],
            'is_active'      => ['nullable', 'boolean'],
            'is_admin'       => ['nullable', 'boolean'],
        ];
    }

    /**
     * Customize the error messages for specific fields.
     */
    public function messages(): array
    {
        return [
            'email.email'            => 'Please provide a valid email address.',
            'email.unique'           => 'This email is already taken by another user.',
            'image.image'            => 'The uploaded file must be an image.',
            'image.mimes'            => 'Allowed image types are: jpeg, png, jpg, gif, and webp.',
            'image.max'              => 'Image size must not exceed 10MB.',
            'barangay_id.exists'     => 'The selected barangay is invalid.',
            'password.min'           => 'Password must be at least 8 characters long.',
            'is_active.boolean'      => 'The active status must be true or false.',
            'is_admin.boolean'       => 'The admin status must be true or false.',
        ];
    }
}
