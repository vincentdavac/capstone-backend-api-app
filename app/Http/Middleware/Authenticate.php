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
        // For API requests, return JSON 401
        if ($request->expectsJson()) {
            return null; // Laravel will return 401 automatically
        }

        // For non-JSON requests, return JSON manually
        return response()->json([
            'message' => 'Unauthenticated.'
        ], 401);
    }
}
