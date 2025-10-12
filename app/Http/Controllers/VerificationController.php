<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use App\Models\User;

class VerificationController extends Controller
{
    /**
     * ✅ Verify email without requiring login
     */
// app/Http/Controllers/VerificationController.php

public function verify(Request $request, $id, $hash)
{
    $user = User::findOrFail($id);

    if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        return redirect('http://localhost:5173/coastella/verify-failed'); // 👉 mobile app
    }

    if ($user->hasVerifiedEmail()) {
        return redirect('http://localhost:5173/coastella/verify-success'); // 👉 mobile app
    }

    $user->markEmailAsVerified();
    event(new Verified($user));

    return redirect('http://localhost:5173/coastella/verify-success'); // 👉 mobile app
}


    /**
     * ✅ Resend email verification link
     * - Can be called from frontend via API
     * - Requires user_id to identify the user
     */
    public function resend(Request $request)
    {
        $user = User::find($request->input('user_id'));

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified'], 400);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verification link sent!']);
    }
}
