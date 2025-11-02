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
            'contact_number'  => 'required|string|max:20',
            'house_no'        => 'required|string|max:255',
            'street'          => 'required|string|max:255',
            'barangay'        => 'required|string|max:255',
            'municipality'    => 'nullable|string|max:255',
            'password'        => 'required|string|min:8|confirmed',
            'image'           => 'required|file|mimes:jpg,jpeg,png,webp|max:10240', // ✅ 10MB max
            'image_url'       => 'nullable|url',
            'is_admin'        => 'nullable|boolean',
        ];
    }
}
