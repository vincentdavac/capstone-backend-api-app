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
use Illuminate\Support\Facades\Log; // ✅ ADD THIS

class AuthController extends Controller
{
    use HttpResponses;

public function login(LoginUserRequest $request)
{
    $request->validated($request->all());

    // ✅ Step 2: Rate limiting
    $email = (string) $request->email;
    $key = Str::lower($email) . '|' . $request->ip();

    if (RateLimiter::tooManyAttempts($key, 3)) {
        $seconds = RateLimiter::availableIn($key);
        return response()->json([
            'status' => 'error',
            'message' => "Too many login attempts. Try again in {$seconds} seconds.",
            'retry_after' => $seconds
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
        Auth::logout();
        return response()->json([
            'status' => 'error',
            'message' => 'Please verify your email address before logging in.'
        ], 403);
    }

    // 🚫 Step 5: Block cross-login based on Origin
    $origin = $request->headers->get('origin');

    if ($user->is_admin && str_contains($origin, '5173')) {
        // Admin trying to log in from user site
        Auth::logout();
        return response()->json([
            'status' => 'error',
            'message' => 'Admins cannot log in from the user site.'
        ], 403);
    }

    if (!$user->is_admin && str_contains($origin, '5174')) {
        // User trying to log in from admin site
        Auth::logout();
        return response()->json([
            'status' => 'error',
            'message' => 'Users cannot log in from the admin site.'
        ], 403);
    }

    // ✅ Step 6: Clear rate limit and issue token
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
        'g-recaptcha-response' => 'required',
        'first_name'           => 'required|string|max:255',
        'last_name'            => 'required|string|max:255',
        'email'                => 'required|string|email|max:255|unique:users',
        'contact_number'       => 'nullable|string|max:20',
        'password'             => 'required|string|min:8|confirmed',
    ]);

    // 🔹 Step 2: Verify reCAPTCHA
    $response = \Illuminate\Support\Facades\Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
        'secret'   => env('RECAPTCHA_SECRET_KEY'),
        'response' => $request->input('g-recaptcha-response'),
        'remoteip' => $request->ip(),
    ]);

    $recaptchaData = $response->json();

    if (!($recaptchaData['success'] ?? false)) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Captcha verification failed',
            'errors'  => $recaptchaData['error-codes'] ?? [],
        ], 422);
    }

    // ✅ Step 3: Detect admin request or force admin creation
    // If your frontend explicitly sends is_admin: 1, trust that value.
    // If not, you can also check origin as fallback.
    $origin = $request->headers->get('origin');
    $isAdmin = $request->boolean('is_admin'); // ✅ handles "1", 1, "true", etc.

    if (!$isAdmin && $origin && (str_contains($origin, '5174') || str_contains($origin, '127.0.0.1:5174'))) {
        $isAdmin = true;
    }

    //     // 🟡 ADD THIS DEBUG LOG HERE (before creating user)
    // \Log::info('REGISTER DEBUG', [
    //     'origin' => $request->headers->get('origin'),
    //     'is_admin_raw' => $request->is_admin,
    //     'parsed_boolean' => $request->boolean('is_admin'),
    //     'final_value' => $isAdmin,
    // ]);
    // 🔹 Step 4: Create user
    $user = User::create([
        'first_name'     => $request->first_name,
        'last_name'      => $request->last_name,
        'email'          => $request->email,
        'contact_number' => $request->contact_number,
        'image'          => $request->image,
        'image_url'      => $request->image_url,
        'password'       => \Illuminate\Support\Facades\Hash::make($request->password),
        'is_admin'       => $isAdmin ? 1 : 0, // ✅ This now respects the frontend
    ]);

    // 🔹 Step 5: Send verification email
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
