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
    public function verify(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        // 🔍 Validate hash
        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect($this->getFrontendUrl($request, $user, 'verify-failed'));
        }

        // 🟡 If already verified
        if ($user->hasVerifiedEmail()) {
            return redirect($this->getFrontendUrl($request, $user, 'verify-success'));
        }

        // ✅ Mark verified and fire event
        $user->markEmailAsVerified();
        event(new Verified($user));

        // 🔁 Redirect to success page
        return redirect($this->getFrontendUrl($request, $user, 'verify-success'));
    }

    /**
     * ✅ Get the appropriate URL for verification result pages
     */
    private function getFrontendUrl(Request $request, User $user, string $path)
    {
        // ✅ Use Laravel routes for verification result pages
        if ($path === 'verify-success') {
            return route('verify.success');
        }
        
        if ($path === 'verify-failed') {
            return route('verify.failed');
        }
        
        return route('verify.success'); // fallback
    }

    /**
     * ✅ Resend email verification link
     */
    public function resend(Request $request)
    {
        $user = User::find($request->input('user_id'));

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified'], 400);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verification link sent!']);
    }
}