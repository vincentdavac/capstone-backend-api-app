<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

class RecaptchaRule implements ValidationRule
{
    /**
     * Validate the attribute.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // ✅ Get secret key from config/services.php
        $secret = config('services.recaptcha.secret');

        // ✅ Verify the response with Google
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret'   => $secret,
            'response' => $value,
        ]);

        if (! $response->json('success')) {
            $fail('reCAPTCHA verification failed, please try again.');
        }
    }
}
