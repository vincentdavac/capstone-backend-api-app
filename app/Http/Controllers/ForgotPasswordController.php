<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class ForgotPasswordController extends Controller
{
    //  Step 1: Send reset link to email
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['status' => 'success', 'message' => __($status)], 200)
            : response()->json(['status' => 'error', 'message' => __($status)], 400);
    }

    //  Step 2: Show reset password form (for mobile)
    public function showResetForm(Request $request, $token)
    {
        return view('reset-password', [
            'token' => $token,
            'email' => $request->email
        ]);
    }

    //  Step 3: Reset password
    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            //  For web form submission, redirect back with errors
            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        //  For web form, redirect to success page
        if (!$request->expectsJson()) {
            return $status === Password::PASSWORD_RESET
                ? redirect()->route('password.reset.success')
                : back()->withErrors(['email' => __($status)])->withInput();
        }

        // ✅ For API, return JSON
        return $status === Password::PASSWORD_RESET
            ? response()->json(['status' => 'success', 'message' => __($status)], 200)
            : response()->json(['status' => 'error', 'message' => __($status)], 400);
    }
}
