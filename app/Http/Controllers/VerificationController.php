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
            return redirect($this->getFrontendUrl($user, 'verify-failed'));
        }

        // 🟡 If already verified
        if ($user->hasVerifiedEmail()) {
            return redirect($this->getFrontendUrl($user, 'verify-success'));
        }

        // ✅ Mark verified and fire event
        $user->markEmailAsVerified();
        event(new Verified($user));

        // 🔁 Redirect to correct frontend (based on role)
        return redirect($this->getFrontendUrl($user, 'verify-success'));
    }

    /**
     * ✅ Decide which frontend base URL to use (user vs admin)
     */
private function getFrontendUrl(User $user, string $path)
{
    // Get base URLs from .env
    $userUrl = rtrim(env('FRONTEND_USER_URL', 'http://localhost:5173/coastella'), '/');
    $adminUrl = rtrim(env('FRONTEND_ADMIN_URL', 'http://localhost:5174'), '/');

    // ✅ Detect admin either by is_admin column or role value
    $isAdmin = (isset($user->is_admin) && $user->is_admin == 1) 
            || (isset($user->role) && $user->role === 'admin');

    // ✅ Select correct frontend base
    $baseUrl = $isAdmin ? $adminUrl : $userUrl;

    return "{$baseUrl}/{$path}";
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
