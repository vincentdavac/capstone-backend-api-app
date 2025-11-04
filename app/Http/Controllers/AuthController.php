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
use App\Http\Resources\UserResource;

class AuthController extends Controller
{
    use HttpResponses;

    public function me(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return $this->error(null, 'User not authenticated.', 401);
        }

        // Convert resource to array before passing to success()
        $userResource = (new UserResource($user))->toArray($request);

        return $this->success(
            $userResource,
            'User information retrieved successfully.',
            200
        );
    }



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

        // ✅ Step 6: Clear rate limit and issue token
        RateLimiter::clear($key);
        $token = $user->createToken('admin_auth_token')->plainTextToken;

        return $this->success([
            'user' => new UserResource($user),
            'token' => $token,
            'message' => 'User logged in successfully'
        ]);
    }


    public function register(StoreUserRequest $request)
    {
        // 🔹 Step 1: Handle image upload
        $imageName = null;
        $imageUrl = null;

        if ($request->hasFile('image')) {
            $imageFile = $request->file('image');
            $imageName = Str::random(32) . '.' . $imageFile->getClientOriginalExtension();
            $imageFile->move(public_path('profile_images'), $imageName);
            $imageUrl = url('profile_images/' . $imageName);
        }

        // 🔹 Step 2: Verify Google reCAPTCHA (skip for local/testing)
        if (!app()->environment(['local', 'testing'])) {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret'   => env('RECAPTCHA_SECRET_KEY'),
                'response' => $request->input('g-recaptcha-response'),
                'remoteip' => $request->ip(),
            ]);

            $recaptchaData = $response->json();

            if (!($recaptchaData['success'] ?? false)) {
                return $this->error(
                    'Captcha verification failed',
                    $recaptchaData['error-codes'] ?? [],
                    422
                );
            }
        }

        // 🔹 Step 3: Determine if user should be admin
        $origin = $request->headers->get('origin');
        $isAdmin = $request->boolean('is_admin');

        // Allow admin creation from dev origins
        if (!$isAdmin && $origin && (str_contains($origin, '5173') || str_contains($origin, '127.0.0.1:5173'))) {
            $isAdmin = true;
        }

        // 🔹 Step 4: Create new user
        $user = User::create([
            'first_name'     => $request->first_name,
            'last_name'      => $request->last_name,
            'email'          => $request->email,
            'contact_number' => $request->contact_number,
            'house_no'       => $request->house_no,
            'street'         => $request->street,
            'barangay'       => $request->barangay,
            'municipality'   => $request->municipality,
            'image'          => $imageName,
            'image_url'      => $imageUrl,
            'password'       => Hash::make($request->password),
            'is_admin'       => $isAdmin ? 1 : 0,
        ]);

        // ✅ Generate Sanctum token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Attach the token to the resource
        $user->token = $token;

        // 🔹 Step 5: Send email verification
        $user->sendEmailVerificationNotification();

        // 🔹 Step 6: Return success response using HttpResponses
        return $this->success([
            'user' => new UserResource($user),
            'token' => $token,
            'message' => 'Account created successfully. Please check your email to verify your account before logging in.',
        ]);
    }


    public function loginAdmin(LoginUserRequest $request)
    {
        $request->validated($request->all());

        $email = (string) $request->email;
        $key = Str::lower($email) . '|' . $request->ip();

        // ✅ Rate limit: max 3 attempts
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'status' => 'error',
                'message' => "Too many login attempts. Try again in {$seconds} seconds.",
                'retry_after' => $seconds
            ], 429);
        }

        // ✅ Attempt login
        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            RateLimiter::hit($key, 3);
            return $this->error('', 'Credentials do not match', 401);
        }

        $user = User::where('email', $request->email)->first();

        // 🚨 Check if email is verified
        if (is_null($user->email_verified_at)) {
            Auth::logout();
            return response()->json([
                'status' => 'error',
                'message' => 'Please verify your email address before logging in.'
            ], 403);
        }

        // 🚨 Ensure admin
        if (!$user->is_admin) {
            Auth::logout();
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied. Only admin accounts can log in here.'
            ], 403);
        }

        // ✅ Clear rate limit and issue token
        RateLimiter::clear($key);
        $token = $user->createToken('admin_auth_token')->plainTextToken;

        return $this->success([
            'user' => new UserResource($user),
            'token' => $token,
            'message' => 'Admin logged in successfully'
        ]);
    }

    public function registerAdmin(StoreUserRequest $request)
    {
        // 🔹 Step 1: Handle image upload
        $imageName = null;
        $imageUrl = null;

        if ($request->hasFile('image')) {
            $imageFile = $request->file('image');
            $imageName = Str::random(32) . '.' . $imageFile->getClientOriginalExtension();
            $imageFile->move(public_path('profile_images'), $imageName);
            $imageUrl = url('profile_images/' . $imageName);
        }

        // 🔹 Step 2: Verify Google reCAPTCHA (skip for local/testing)
        if (!app()->environment(['local', 'testing'])) {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret'   => env('RECAPTCHA_SECRET_KEY'),
                'response' => $request->input('g-recaptcha-response'),
                'remoteip' => $request->ip(),
            ]);

            $recaptchaData = $response->json();

            if (!($recaptchaData['success'] ?? false)) {
                return $this->error(
                    'Captcha verification failed',
                    $recaptchaData['error-codes'] ?? [],
                    422
                );
            }
        }

        // 🔹 Step 3: Create new admin user
        $user = User::create([
            'first_name'     => $request->first_name,
            'last_name'      => $request->last_name,
            'email'          => $request->email,
            'contact_number' => $request->contact_number,
            'house_no'       => $request->house_no,
            'street'         => $request->street,
            'barangay'       => $request->barangay,
            'municipality'   => $request->municipality,
            'image'          => $imageName,
            'image_url'      => $imageUrl,
            'password'       => Hash::make($request->password),
            'is_admin'       => 1, // ✅ Force admin
        ]);

        // ✅ Generate Sanctum token
        $token = $user->createToken('admin_auth_token')->plainTextToken;
        $user->token = $token;

        // 🔹 Step 4: Send email verification
        $user->sendEmailVerificationNotification();


        // 🔹 Step 5: Return success response using HttpResponses
        return $this->success([
            'user' => new UserResource($user),
            'token' => $token,
            'message' => 'Admin account created successfully. Please check your email to verify your account before logging in.',

        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->success('', 'Logged out successfully', 200);
    }
}
