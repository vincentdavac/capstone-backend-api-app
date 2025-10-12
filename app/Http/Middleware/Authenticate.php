<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo($request): ?string
    {
        // ✅ For API requests, return JSON 401 instead of redirect
        if ($request->expectsJson()) {
            return null; // Laravel will automatically throw 401
        }

        // ✅ Or, if you want email link clicks to go to React login page:
        // return 'http://localhost:5173/login';

        return null;
    }
}
