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
    // public function toMail(object $notifiable): MailMessage
    // {
    //     //  Detect if the account is an admin
    //     $isAdmin = isset($notifiable->is_admin) && $notifiable->is_admin == 1;

    //     if ($isAdmin) {
    //         //  ADMIN: Use web frontend URL (unchanged)
    //         $baseUrl = config('app.frontend_admin_url'); // http://127.0.0.1:5173
    //         $resetUrl = $baseUrl . '/reset-password?token='
    //             . $this->token . '&email=' . urlencode($notifiable->email);

    //         //  Use custom Blade view for admin
    //         return (new MailMessage)
    //             ->subject('Reset Your Password - X-STREAM Admin')
    //             ->view('emails.reset-password', [
    //                 'resetUrl' => $resetUrl,
    //                 'email' => $notifiable->email,
    //             ]);
    //     } else {
    //         //  MOBILE USER: Use Laravel backend route with 10.0.2.2
    //         $baseUrl = 'http://10.0.2.2:8000';
    //         $resetUrl = $baseUrl . '/password/reset/' . $this->token . '?email=' . urlencode($notifiable->email);

    //         //  Use default Laravel email template for mobile
    //         return (new MailMessage)
    //             ->subject('Reset Password - X-STREAM')
    //             ->greeting('Hello!')
    //             ->line('You are receiving this email because we received a password reset request for your account.')
    //             ->action('Reset Password', $resetUrl)
    //             ->line('This password reset link will expire in 60 minutes.')
    //             ->line('If you did not request a password reset, no further action is required.')
    //             ->salutation('Regards, The X-STREAM Team');
    //     }
    // }

    public function toMail(object $notifiable): MailMessage
    {
        // Detect user type safely
        $userType = $notifiable->user_type ?? null;

        /*
    |--------------------------------------------------------------------------
    | ADMIN
    |--------------------------------------------------------------------------
    | Uses frontend admin web app
    */
        if ($userType === 'admin') {

            $baseUrl = config('app.frontend_admin_url'); // http://127.0.0.1:5173

            $resetUrl = $baseUrl . '/reset-password?token='
                . $this->token . '&email=' . urlencode($notifiable->email);

            return (new MailMessage)
                ->subject('Reset Your Password - X-STREAM Admin')
                ->view('emails.reset-password', [
                    'resetUrl' => $resetUrl,
                    'email'    => $notifiable->email,
                ]);
        }

        /*
    |--------------------------------------------------------------------------
    | BARANGAY
    |--------------------------------------------------------------------------
    | Uses Laravel backend on localhost (127.0.0.1)
    */
        if ($userType === 'barangay') {

            $baseUrl = 'http://127.0.0.1:8000';

            $resetUrl = $baseUrl . '/password/reset/'
                . $this->token . '?email=' . urlencode($notifiable->email);

            return (new MailMessage)
                ->subject('Reset Password - X-STREAM Barangay')
                ->greeting('Hello!')
                ->line('You are receiving this email because we received a password reset request for your barangay account.')
                ->action('Reset Password', $resetUrl)
                ->line('This password reset link will expire in 60 minutes.')
                ->line('If you did not request a password reset, no further action is required.')
                ->salutation('Regards, The X-STREAM Team');
        }

        /*
    |--------------------------------------------------------------------------
    | USER / MOBILE (DEFAULT)
    |--------------------------------------------------------------------------
    | Android Emulator uses 10.0.2.2
    */
        $baseUrl = 'http://10.0.2.2:8000';

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
