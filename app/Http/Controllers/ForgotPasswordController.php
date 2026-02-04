<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class ForgotPasswordController extends Controller
{
    // Step 1: Send reset link to email
    public function sendResetLinkEmail(Request $request)
    {
        try {
            $request->validate(['email' => 'required|email']);

            $status = Password::sendResetLink(
                $request->only('email')
            );

            return $status === Password::RESET_LINK_SENT
                ? response()->json(['status' => 'success', 'message' => __($status)], 200)
                : response()->json(['status' => 'error', 'message' => __($status)], 400);
                
        } catch (\Exception $e) {
            Log::error('Send reset link error', [
                'error' => $e->getMessage(),
                'email' => $request->email ?? 'N/A'
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send reset link. Please try again.'
            ], 500);
        }
    }

    // Step 2: Show reset form (For MOBILE users only)
    public function showResetForm(Request $request, $token)
    {
        // Check if this is a mobile user request
        // You might want to add logic here to detect mobile vs web
        
        return view('reset-password', [
            'token' => $token,
            'email' => $request->email,
            'is_mobile' => true // Flag for mobile users
        ]);
    }

    // Step 3: Reset password (Works for ALL users)
    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error', 
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function (User $user, $password) {
                    $user->forceFill([
                        'password' => Hash::make($password),
                    ])->save();
                    
                    Log::info('Password reset successfully', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'user_type' => $user->user_type,
                        'timestamp' => now()
                    ]);
                }
            );

            // ✅ Always return JSON - no redirects
            if ($status === Password::PASSWORD_RESET) {
                return response()->json([
                    'status' => 'success', 
                    'message' => 'Password reset successfully. You can now login.'
                ], 200);
            }

            return response()->json([
                'status' => 'error', 
                'message' => __($status)
            ], 400);
                
        } catch (\Exception $e) {
            Log::error('Password reset failed', [
                'error' => $e->getMessage(),
                'email' => $request->email ?? 'N/A',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Password reset failed. Please try again or contact support.'
            ], 500);
        }
    }
}