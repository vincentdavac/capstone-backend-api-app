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
    // ✅ Detect if the account is an admin (based on is_admin column)
    $isAdmin = isset($notifiable->is_admin) && $notifiable->is_admin == 1;

    // ✅ Pick the correct frontend URL from .env
    $baseUrl = $isAdmin
        ? config('app.frontend_admin_url') // e.g., http://localhost:5174
        : config('app.frontend_user_url');      // e.g., http://localhost:5173

    // ✅ Choose correct reset path
    $path = '/reset-password'; // same path for both, unless your user path is different

    // ✅ Construct full reset URL
    $resetUrl = $baseUrl . $path . '?token='
        . $this->token . '&email=' . urlencode($notifiable->email);

    return (new MailMessage)
        ->subject('Reset Your Password')
        ->line('You are receiving this email because we received a password reset request for your account.')
        ->action('Reset Password', $resetUrl)
        ->line('This password reset link will expire in 60 minutes.')
        ->line('If you did not request a password reset, no further action is required.');
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
