<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPassword extends Notification
{
    use Queueable;

    public $token;

    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Detect user type safely
        $userType = $notifiable->user_type ?? 'user';
        
        /*
        |--------------------------------------------------------------------------
        | ADMIN & BARANGAY (Same Frontend URL)
        |--------------------------------------------------------------------------
        | Both use frontend URL
        */
        if (in_array($userType, ['admin', 'barangay'])) {
            // Use config for frontend URL, fallback based on environment
            if (app()->environment('production')) {
                $baseUrl = rtrim(config('app.frontend_url', 'https://x-stream.ucc.bsit4c.com'), '/');
            } else {
                $baseUrl = rtrim(config('app.frontend_url', 'http://localhost:5173'), '/');
            }
            
            // Build reset URL
            $resetUrl = $baseUrl . '/reset-password?token=' 
                . $this->token 
                . '&email=' . urlencode($notifiable->email)
                . '&user_type=' . $userType;

            // Customize based on user type
            $subject = $userType === 'admin' 
                ? 'Reset Your Password - X-STREAM Admin'
                : 'Reset Password - X-STREAM Barangay';

            $line1 = $userType === 'admin'
                ? 'You are receiving this email because we received a password reset request for your admin account.'
                : 'You are receiving this email because we received a password reset request for your barangay account.';

            return (new MailMessage)
                ->subject($subject)
                ->greeting('Hello!')
                ->line($line1)
                ->action('Reset Password', $resetUrl)
                ->line('This password reset link will expire in 60 minutes.')
                ->line('If you did not request a password reset, no further action is required.')
                ->salutation('Regards, The X-STREAM Team');
        }

/*
|--------------------------------------------------------------------------
| USER / MOBILE (DEFAULT) - Different Approach
|--------------------------------------------------------------------------
| Uses Laravel backend route
*/
// ALWAYS USE PRODUCTION URL FOR MOBILE APP USERS
$baseUrl = rtrim(config('app.url', 'https://backend-x-stream.ucc.bsit4c.com'), '/');

$resetUrl = $baseUrl . '/password/reset/'
    . $this->token . '?email=' . urlencode($notifiable->email);

return (new MailMessage)
    ->subject('Reset Password - X-STREAM')
    ->greeting('Hello!')
    ->line('You are receiving this email because we received a password reset request for your account.')
    ->action('Reset Password', $resetUrl)
    ->line('This password reset link will expire in 60 minutes.')
    ->line('If you did not request a password reset, no further action is required.')
    ->salutation('Regards, The X-STREAM Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}