<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Http; // ✅ needed for reCAPTCHA verification
use Illuminate\Support\Str;

class AuthController extends Controller
{
    use HttpResponses;

    public function login(LoginUserRequest $request){
        $request->validated($request->all());
        // ✅ Step 2: Rate limiting
        $email = (string) $request->email;
        $key = Str::lower($email) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'status' => 'error',
                'message' => "Too many login attempts. Try again in {$seconds} seconds.",
                'retry_after' => $seconds // 👈 para pwede mo ma-display sa frontend
            ], 429);
        }

        // ✅ Step 3: Attempt login
        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            RateLimiter::hit($key, 3);
            return $this->error('', 'Credentials do not match', 401);
        }

        $user = User::where('email', $request->email)->first();

        // 🚨 Step 4: Check if email is verified
        if (is_null($user->email_verified_at)) {
            // 🔴 Important: logout agad para walang session/token
            Auth::logout();

            return response()->json([
                'status' => 'error',
                'message' => 'Please verify your email address before logging in.'
            ], 403);
        }

        // ✅ Step 5: Clear attempts after success
        RateLimiter::clear($key);

        return $this->success([
            'user' => $user,
            'token' => $user->createToken('API Token:' . $user->first_name)->plainTextToken,
            'message' => 'User logged in successfully'
        ]);
    }

public function register(StoreUserRequest $request)
{
    // 🔹 Step 1: Validate required fields + captcha
    $request->validate([
        'g-recaptcha-response' => 'required', // 👈 frontend must send this
        'first_name'           => 'required|string|max:255',
        'last_name'            => 'required|string|max:255',
        'email'                => 'required|string|email|max:255|unique:users',
        'contact_number'       => 'nullable|string|max:20',
        'password'             => 'required|string|min:8|confirmed',
    ]);

    // 🔹 Step 2: Verify reCAPTCHA with Google
    $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
        'secret'   => env('RECAPTCHA_SECRET_KEY'),
        'response' => $request->input('g-recaptcha-response'),
        'remoteip' => $request->ip(),
    ]);

    $recaptchaData = $response->json();

    if (!($recaptchaData['success'] ?? false)) {
        \Illuminate\Support\Facades\Log::error('reCAPTCHA failed', $recaptchaData);

        return response()->json([
            'status'  => 'error',
            'message' => 'Captcha verification failed',
            'errors'  => $recaptchaData['error-codes'] ?? [],
        ], 422);
    }

    // 🔹 Step 3: Proceed with registration if captcha OK
    $user = User::create([
        'first_name'     => $request->first_name,
        'last_name'      => $request->last_name,
        'email'          => $request->email,
        'contact_number' => $request->contact_number,
        'image'          => $request->image,
        'image_url'      => $request->image_url,
        'password'       => Hash::make($request->password),
    ]);

    // 🔹 Step 4: Send email verification
    $user->sendEmailVerificationNotification();

    return response()->json([
        'status'  => 'success',
        'message' => 'Account created successfully. Please check your email to verify your account before logging in.'
    ], 201);
}


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->success('', 'User logged out successfully', 200);
    }
}
