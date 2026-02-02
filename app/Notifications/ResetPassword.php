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
        | Both use: http://localhost:5173/reset-password
        */
        if (in_array($userType, ['admin', 'barangay'])) {
            // Common frontend URL for both admin and barangay
            $baseUrl = 'http://localhost:5173';
            
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
        $baseUrl = 'http://10.0.2.2:8000'; // For Android emulator
        
        // For physical device testing, you might use your local IP
        // $baseUrl = 'http://192.168.1.x:8000';

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