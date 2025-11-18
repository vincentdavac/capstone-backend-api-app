<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
        return [
            'g-recaptcha-response' => 'sometimes',

            'first_name'      => 'required|string|max:255',
            'last_name'       => 'required|string|max:255',
            'email'           => 'required|string|email|max:255|unique:users',

            // ✅ Contact number: must start with 09 and be 11 digits
            'contact_number'  => [
                'required',
                'regex:/^(09)\d{9}$/'
            ],

            'house_no'        => 'required|string|max:255',
            'street'          => 'required|string|max:255',
            'barangay_id'     => 'required|exists:barangays,id',
            'municipality'    => 'nullable|string|max:255',

            // ✅ Password: min 8, must have uppercase, number, and special char
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/'
            ],

            // ✅ File uploads
            'image'           => 'required|file|mimes:jpg,jpeg,png,webp|max:10240', // ✅ Changed to nullable
            'id_document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:20480',  // 20MB max

            // ✅ Verification and user type
            'registration_status' => 'nullable|boolean',
            'date_verified'       => 'nullable|date',
            'verified_by'         => 'nullable|exists:users,id',
            'user_type'           => 'sometimes|in:admin,barangay,user',

            'is_admin' => 'nullable|boolean',
        ];
    }

    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            'contact_number.regex' => 'The contact number must start with 09 and be 11 digits long.',
            'password.regex' => 'The password must contain at least one uppercase letter, one number, and one special character.',
        ];
    }
}
