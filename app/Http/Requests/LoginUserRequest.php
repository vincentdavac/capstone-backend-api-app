<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\RecaptchaRule; // ✅ import the custom rule

class LoginUserRequest extends FormRequest
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
        'email' => 'required|string|email|max:255',
        'password' => 'required|string|min:8',
        // 🚫 remove captcha rule for now
        // 'g-recaptcha-response' => ['required', 'string', new \App\Rules\RecaptchaRule],
    ];
}



    /**
     * Custom messages (optional)
     */
    public function messages(): array
    {
        return [
            'g-recaptcha-response.required' => 'Please complete the captcha to continue.',
        ];
    }
}
