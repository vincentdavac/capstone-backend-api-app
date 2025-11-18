<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;

use App\Models\User;
use App\Models\Barangay;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserInformationResource;

use Illuminate\Support\Facades\DB;


class AuthController extends Controller
{
    use HttpResponses;



    // public function getAllBarangayAndAdminUsers()
    // {
    //     $users = User::with('barangay')
    //         ->whereIn('user_type', ['admin', 'barangay'])
    //         ->where('is_active', 1)
    //         ->latest()
    //         ->get();

    //     return $this->success(
    //         UserResource::collection($users),
    //         'All Barangay and Admin users fetched successfully.',
    //         200
    //     );
    // }

    // public function getAllBarangayResidents()
    // {
    //     $authUser = Auth::user();

    //     if (!$authUser || !$authUser->barangay_id) {
    //         return $this->error(null,'Authenticated user has no assigned barangay.', 400);
    //     }

    //     $users = User::with('barangay')
    //         ->where('user_type', 'user')
    //         ->where('is_active', 1)
    //         ->where('barangay_id', $authUser->barangay_id)
    //         ->latest()
    //         ->get();

    //     return $this->success(
    //         UserResource::collection($users),
    //         'All barangay residents fetched successfully.',
    //         200
    //     );
    // }



    // UPDATE USER
    public function updateUser(UpdateUserRequest $request, $user)
    {
        $user = User::find($user);

        if (!$user) {
            return $this->error(null, 'User not found.', 404);
        }

        $validated = $request->validated();

        // 🔹 Handle profile image update
        if ($request->hasFile('image')) {
            $imageFile = $request->file('image');
            $imageName = Str::random(32) . '.' . $imageFile->getClientOriginalExtension();
            $imageFile->move(public_path('profile_images'), $imageName);
            $validated['image'] = $imageName;
        }

        // 🔹 Handle ID document update
        if ($request->hasFile('id_document')) {
            $idDocumentFile = $request->file('id_document');
            $idDocumentName = Str::random(32) . '.' . $idDocumentFile->getClientOriginalExtension();
            $idDocumentFile->move(public_path('id_documents'), $idDocumentName);
            $validated['id_document'] = $idDocumentName;
        }

        // 🔹 Hash password if provided
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // 🔹 Automatically update verification date if registration_status is true
        if (isset($validated['registration_status']) && $validated['registration_status'] == true) {
            $validated['date_verified'] = now();
            $validated['verified_by'] = Auth::id(); // Optional: track verifier if admin is logged in
        }

        // 🔹 Update the user record
        $user->update($validated);

        // ✅ Load relationships for API response
        $user->load(['barangay', 'verifier']);

        return $this->success(
            new UserResource($user),
            'User information updated successfully.',
            200
        );
    }

    // GET AUTHENTICATED USER INFO
    public function me(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return $this->error(null, 'User not authenticated.', 401);
        }

        // ✅ Eager load barangay and its buoys
        $user->load(['barangay.buoys', 'verifier']);

        // Convert resource to array before passing to success()
        $userResource = (new UserInformationResource($user))->toArray($request);

        return $this->success(
            $userResource,
            'User information retrieved successfully.',
            200
        );
    }


    // USER LOGIN
    public function login(LoginUserRequest $request)
    {
        $request->validated($request->all());

        // ✅ Step 1: Rate limiting
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

        // ✅ Step 2: Attempt login
        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            RateLimiter::hit($key, 3);
            return $this->error('', 'Credentials do not match', 401);
        }

        $user = User::where('email', $request->email)->first();

        // 🚨 Step 3: Check if email is verified
        if (is_null($user->email_verified_at)) {
            Auth::logout();
            return $this->error('', 'Please verify your email address before logging in.', 403);
        }

        // 🚨 Step 4: Check if account is active
        if (!$user->is_active) {
            Auth::logout();
            return $this->error('', 'Your account has been archived or deactivated. Please contact your barangay for assistance.', 403);
        }

        // 🚨 Step 4: Check is_admin
        if ($user->is_admin) {
            Auth::logout();
            return $this->error('', 'Admin accounts are not allowed to log in here.', 403);
        }

        // 🚨 Step 5: Check registration status
        if (!$user->registration_status) {
            Auth::logout();
            return $this->error('', 'Your account is not activated. Please contact your barangay for activation.', 403);
        }

        // 🚨 Step 6: Check user_type
        if ($user->user_type !== 'user') {
            Auth::logout();
            return $this->error('', 'Only user accounts are allowed to log in here.', 403);
        }

        // ✅ Step 7: Clear rate limit and issue token
        RateLimiter::clear($key);
        $token = $user->createToken('auth_token')->plainTextToken;

        // ✅ Load relationships for API response
        $user->load(['barangay', 'verifier']);

        return $this->success([
            'user' => new UserResource($user),
            'token' => $token,
            'message' => 'User logged in successfully'
        ]);
    }

    // USER REGISTRATION
    public function register(StoreUserRequest $request)
    {

        // 🔹 Step 4: Check if contact number already exists
        $existingContact = User::where('contact_number', $request->contact_number)->first();

        if ($existingContact) {
            return $this->error(
                'The contact number is already registered.',
                ['contact_number' => ['The contact number has already been taken.']],
                422
            );
        }

        // 🔹 Step 1: Handle image upload
        $imageName = null;
        if ($request->hasFile('image')) {
            $imageFile = $request->file('image');
            $imageName = Str::random(32) . '.' . $imageFile->getClientOriginalExtension();
            $imageFile->move(public_path('profile_images'), $imageName);
        }

        // 🔹 Step 2: Handle ID document upload
        $idDocumentName = null;
        if ($request->hasFile('id_document')) {
            $idDocumentFile = $request->file('id_document');
            $idDocumentName = Str::random(32) . '.' . $idDocumentFile->getClientOriginalExtension();
            $idDocumentFile->move(public_path('id_documents'), $idDocumentName);
        }

        // 🔹 Step 3: Verify Google reCAPTCHA (skip for local/testing)
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


        // 🔹 Step 5: Create new user
        $user = User::create([
            'first_name'         => $request->first_name,
            'last_name'          => $request->last_name,
            'email'              => $request->email,
            'contact_number'     => $request->contact_number,
            'house_no'           => $request->house_no,
            'street'             => $request->street,
            'barangay_id'        => $request->barangay_id,
            'municipality'       => $request->municipality,
            'image'              => $imageName,
            'id_document'        => $idDocumentName,
            'password'           => Hash::make($request->password),
            'is_admin'           => 0,
            'registration_status' => $request->input('registration_status', false),
            'user_type'          => 'user',
            'date_verified'      => $request->date_verified ?? null,
            'verified_by'        => $request->verified_by ?? null,
        ]);

        // ✅ Generate Sanctum token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Attach the token to the resource
        $user->token = $token;

        // 🔹 Step 6: Send email verification
        $user->sendEmailVerificationNotification();

        // ✅ Load barangay and verifier relationships for UserResource
        $user->load(['barangay', 'verifier']);

        // 🔹 Step 7: Return success response using HttpResponses
        return $this->success([
            'user' => new UserResource($user),
            'token' => $token,
            'message' => 'Account created successfully. Please check your email to verify your account before logging in.',
        ]);
    }

    // ADMIN LOGIN
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
            return $this->error('', 'Please verify your email address before logging in.', 403);
        }



        // 🚨 Ensure admin account
        if (!$user->is_admin || $user->user_type !== 'admin') {
            Auth::logout();
            return $this->error('', 'Access denied. Only admin accounts can log in here.', 403);
        }

        // 🚨 Check registration status
        if (!$user->registration_status) {
            Auth::logout();
            return $this->error('', 'Your admin account is not activated. Please contact the system administrator.', 403);
        }

        // ✅ Clear rate limit and issue token
        RateLimiter::clear($key);
        $token = $user->createToken('admin_auth_token')->plainTextToken;

        // ✅ Load relationships for API response
        $user->load(['barangay', 'verifier']);

        return $this->success([
            'user' => new UserResource($user),
            'token' => $token,
            'message' => 'Admin logged in successfully'
        ]);
    }

    // ADMIN REGISTRATION
    public function registerAdmin(StoreUserRequest $request)
    {

        // 🔹 Step 4: Check if contact number already exists
        $existingContact = User::where('contact_number', $request->contact_number)->first();

        if ($existingContact) {
            return $this->error(
                'The contact number is already registered.',
                ['contact_number' => ['The contact number has already been taken.']],
                422
            );
        }

        // 🔹 Step 1: Handle profile image upload
        $imageName = null;
        if ($request->hasFile('image')) {
            $imageFile = $request->file('image');
            $imageName = Str::random(32) . '.' . $imageFile->getClientOriginalExtension();
            $imageFile->move(public_path('profile_images'), $imageName);
        }

        // 🔹 Step 2: Handle ID document upload
        $idDocumentName = null;
        if ($request->hasFile('id_document')) {
            $idDocumentFile = $request->file('id_document');
            $idDocumentName = Str::random(32) . '.' . $idDocumentFile->getClientOriginalExtension();
            $idDocumentFile->move(public_path('id_documents'), $idDocumentName);
        }

        // 🔹 Step 3: Verify Google reCAPTCHA (skip for local/testing)
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



        // 🔹 Step 4: Create new admin user
        $user = User::create([
            'first_name'         => $request->first_name,
            'last_name'          => $request->last_name,
            'email'              => $request->email,
            'contact_number'     => $request->contact_number,
            'house_no'           => $request->house_no,
            'street'             => $request->street,
            'barangay_id'        => $request->barangay_id,
            'municipality'       => $request->municipality,
            'image'              => $imageName,
            'id_document'        => $idDocumentName,
            'password'           => Hash::make($request->password),
            'is_admin'           => 1,              // Force admin
            'user_type'          => 'admin',        // Force admin type
            'registration_status' => $request->input('registration_status', true),
            'date_verified'      => $request->date_verified ?? null,
            'verified_by'        => $request->verified_by ?? null,
        ]);

        // ✅ Generate Sanctum token
        $token = $user->createToken('admin_auth_token')->plainTextToken;
        $user->token = $token;

        // 🔹 Step 5: Send email verification
        $user->sendEmailVerificationNotification();

        // ✅ Load relationships for UserResource
        $user->load(['barangay', 'verifier']);

        // 🔹 Step 6: Return success response
        return $this->success([
            'user' => new UserResource($user),
            'token' => $token,
            'message' => 'Admin account created successfully. Please check your email to verify your account before logging in.',
        ]);
    }

    // BARANGAY LOGIN
    public function loginBarangay(LoginUserRequest $request)
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
            return $this->error('', 'Please verify your email address before logging in.', 403);
        }

        // 🚨 Ensure barangay account
        if ($user->is_admin || $user->user_type !== 'barangay') {
            Auth::logout();
            return $this->error('', 'Access denied. Only barangay accounts can log in here.', 403);
        }

        // 🚨 Step 4: Check if account is active
        if (!$user->is_active) {
            Auth::logout();
            return $this->error('', 'Your account has been archived or deactivated. Please contact your administrator for assistance.', 403);
        }

        // 🚨 Check registration status
        if (!$user->registration_status) {
            Auth::logout();
            return $this->error('', 'Your barangay account is not activated. Please contact the system administrator.', 403);
        }

        // ✅ Clear rate limit and issue token
        RateLimiter::clear($key);
        $token = $user->createToken('barangay_auth_token')->plainTextToken;

        // ✅ Load relationships for API response
        $user->load(['barangay', 'verifier']);

        return $this->success([
            'user' => new UserResource($user),
            'token' => $token,
            'message' => 'Barangay logged in successfully'
        ]);
    }

    // BARANGAY REGISTRATION
    public function registerBarangay(StoreUserRequest $request)
    {

        // 🔹 Step 4: Check if contact number already exists
        $existingContact = User::where('contact_number', $request->contact_number)->first();

        if ($existingContact) {
            return $this->error(
                'The contact number is already registered.',
                ['contact_number' => ['The contact number has already been taken.']],
                422
            );
        }

        // 🔹 Step 1: Handle profile image upload
        $imageName = null;
        if ($request->hasFile('image')) {
            $imageFile = $request->file('image');
            $imageName = Str::random(32) . '.' . $imageFile->getClientOriginalExtension();
            $imageFile->move(public_path('profile_images'), $imageName);
        }

        // 🔹 Step 2: Handle ID document upload
        $idDocumentName = null;
        if ($request->hasFile('id_document')) {
            $idDocumentFile = $request->file('id_document');
            $idDocumentName = Str::random(32) . '.' . $idDocumentFile->getClientOriginalExtension();
            $idDocumentFile->move(public_path('id_documents'), $idDocumentName);
        }

        // 🔹 Step 3: Verify Google reCAPTCHA (skip for local/testing)
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

        // 🔹 Step 4: Create new barangay user
        $user = User::create([
            'first_name'         => $request->first_name,
            'last_name'          => $request->last_name,
            'email'              => $request->email,
            'contact_number'     => $request->contact_number,
            'house_no'           => $request->house_no,
            'street'             => $request->street,
            'barangay_id'        => $request->barangay_id,
            'municipality'       => $request->municipality,
            'image'              => $imageName,
            'id_document'        => $idDocumentName,
            'password'           => Hash::make($request->password),
            'is_admin'           => 0,                 // Not admin
            'user_type'          => 'barangay',        // Force barangay type
            'registration_status' => $request->input('registration_status', false),
            'date_verified'      => $request->date_verified ?? null,
            'verified_by'        => $request->verified_by ?? null,
        ]);

        // ✅ Generate Sanctum token
        $token = $user->createToken('barangay_auth_token')->plainTextToken;
        $user->token = $token;

        // 🔹 Step 5: Send email verification
        $user->sendEmailVerificationNotification();

        // ✅ Load relationships for API response
        $user->load(['barangay', 'verifier']);

        // 🔹 Step 6: Return success response
        return $this->success([
            'user' => new UserResource($user),
            'token' => $token,
            'message' => 'Barangay account created successfully. Please check your email to verify your account before logging in.',
        ]);
    }

    // ARCHIVE USER
    public function archiveUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->error(null, 'User not found.', 404);
        }

        $authUser = Auth::user();

        // 🚫 Prevent barangay users from archiving admins or other barangay accounts
        if (in_array($user->user_type, ['admin', 'barangay'])) {
            return $this->error(null, 'Admin and Barangay accounts cannot be archived.', 403);
        }

        // ✅ Allow barangay users to archive only users under their jurisdiction (or all, if global)
        if ($authUser->user_type === 'barangay') {
            // Optional: If you track barangay_id in users table, enforce same barangay
            if ($authUser->barangay_id !== $user->barangay_id) {
                return $this->error(null, 'You are not authorized to archive users from another barangay.', 403);
            }
        } else {
            // 🚫 Non-admin/non-barangay users cannot archive anyone
            return $this->error(null, 'You are not authorized to perform this action.', 403);
        }

        // ✅ Archive the user (deactivate account)
        $user->update(['is_active' => false]);

        // 🧹 Remove personal access tokens (logout everywhere)
        DB::table('personal_access_tokens')->where('tokenable_id', $user->id)->delete();

        return $this->success(
            null,
            'User account archived successfully. All active sessions have been logged out.',
            200
        );
    }

    // RESTORE USER
    public function restoreUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->error(null, 'User not found.', 404);
        }

        $authUser = Auth::user();

        // ✅ Only the same user or an admin can restore the account
        if ($authUser->id !== $user->id && !in_array($authUser->user_type, ['admin', 'barangay'])) {
            return $this->error(null, 'You are not authorized to restore this user.', 403);
        }


        // 🚫 Prevent restoring admin or barangay accounts using this function
        if (in_array($user->user_type, ['admin', 'barangay'])) {
            return $this->error(null, 'Admin and Barangay accounts cannot be restored using this function.', 403);
        }

        // ✅ Restore user account
        $user->update(['is_active' => true]);

        return $this->success(
            new UserResource($user),
            'User account restored successfully. The account is now active.',
            200
        );
    }

    // ARCHIVE BARANGAY ACCOUNT BY ADMIN
    public function archiveBarangay($id)
    {
        $authUser = Auth::user();
        $user = User::find($id);

        // 🔸 Check if the user exists
        if (!$user) {
            return $this->error(null, 'Barangay account not found.', 404);
        }

        // 🔸 Only admins can archive barangay accounts
        if ($authUser->user_type !== 'admin') {
            return $this->error(null, 'Only administrators can archive barangay accounts.', 403);
        }

        // 🔸 Prevent admin from archiving themselves
        if ($authUser->id === $user->id) {
            return $this->error(null, 'You cannot archive your own account while logged in.', 403);
        }

        // 🔸 Prevent archiving if target is not a barangay account
        if ($user->user_type !== 'barangay') {
            return $this->error(null, 'Only barangay accounts can be archived in this action.', 403);
        }

        // 🔸 Prevent archiving any admin account (even if another admin tries)
        if ($user->user_type === 'admin' || $user->is_admin) {
            return $this->error(null, 'Admin accounts cannot be archived.', 403);
        }

        // ✅ Archive barangay account
        $user->update(['is_active' => false]);

        // 🧹 Remove all active tokens of the barangay account
        DB::table('personal_access_tokens')
            ->where('tokenable_id', $user->id)
            ->delete();

        return $this->success(
            new UserResource($user),
            'Barangay account archived and logged out successfully.',
            200
        );
    }

    // RESTORE BARANGAY ACCOUNT BY ADMIN
    public function restoreBarangay($id)
    {
        $authUser = Auth::user();
        $user = User::find($id);

        // 🔸 Check if the user exists
        if (!$user) {
            return $this->error(null, 'Barangay account not found.', 404);
        }

        // 🔸 Only admins can restore barangay accounts
        if ($authUser->user_type !== 'admin') {
            return $this->error(null, 'Only administrators can restore barangay accounts.', 403);
        }

        // 🔸 Prevent admin from restoring themselves
        if ($authUser->id === $user->id) {
            return $this->error(null, 'You cannot restore your own account while logged in.', 403);
        }

        // 🔸 Ensure target is a barangay account
        if ($user->user_type !== 'barangay') {
            return $this->error(null, 'Only barangay accounts can be restored in this action.', 403);
        }

        // 🔸 Prevent restoring admin accounts in this function
        if ($user->user_type === 'admin' || $user->is_admin) {
            return $this->error(null, 'Admin accounts cannot be restored using this function.', 403);
        }

        // ✅ Restore barangay account
        $user->update(['is_active' => true]);

        return $this->success(
            new UserResource($user),
            'Barangay account restored successfully and is now active.',
            200
        );
    }

    // GET ACTIVE USERS BASED ON AUTHENTICATED USER TYPE
    public function activeUsers()
    {
        $authUser = Auth::user();

        if ($authUser->user_type === 'admin') {
            // Admin sees all active barangay accounts
            $users = User::whereIn('user_type', ['admin', 'barangay'])
                ->where('is_active', true)
                ->orderBy('created_at', 'desc') // newest first
                ->get();
        } elseif ($authUser->user_type === 'barangay') {
            // Barangay sees all active regular users in their barangay
            $users = User::whereIn('user_type', ['user', 'barangay'])
                ->where('is_active', true)
                ->where('barangay_id', $authUser->barangay_id)
                ->orderBy('created_at', 'desc') // newest first
                ->get();
        } else {
            return $this->error(null, 'Unauthorized', 403);
        }

        // ✅ Eager load barangay and its buoys
        $users->load(['barangay.buoys', 'verifier']);

        return $this->success(
            UserInformationResource::collection($users),
            'Active users retrieved successfully',
            200
        );
    }

    // GET ARCHIVED USERS BASED ON AUTHENTICATED USER TYPE
    public function archivedUsers()
    {
        $authUser = Auth::user();

        if ($authUser->user_type === 'admin') {
            // Admin sees all archived barangay accounts
            $users = User::whereIn('user_type', ['admin', 'barangay'])
                ->where('is_active', false)
                ->orderBy('created_at', 'desc') // newest first
                ->get();
        } elseif ($authUser->user_type === 'barangay') {
            // Barangay sees all archived regular users in their barangay
            $users = User::whereIn('user_type', ['user', 'barangay'])
                ->where('is_active', false)
                ->where('barangay_id', $authUser->barangay_id)
                ->orderBy('created_at', 'desc') // newest first
                ->get();
        } else {
            return $this->error(null, 'Unauthorized', 403);
        }

        // ✅ Eager load barangay and its buoys
        $users->load(['barangay.buoys', 'verifier']);

        return $this->success(
            UserInformationResource::collection($users),
            'Archived users retrieved successfully',
            200
        );
    }

    // LOGOUT
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->success('', 'Logged out successfully', 200);
    }
}
