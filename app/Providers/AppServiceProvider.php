<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 🔗 Customize reset password URL for React frontend
        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            return config('app.frontend_url') . "/reset-password/{$token}?email=" . urlencode($notifiable->getEmailForPasswordReset());
        });
    }
}
