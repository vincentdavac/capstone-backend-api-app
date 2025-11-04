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
            : config('app.frontend_user_url'); // e.g., http://localhost:5173

        // ✅ Construct reset URL
        $resetUrl = $baseUrl . '/reset-password?token='
            . $this->token . '&email=' . urlencode($notifiable->email);

        // ✅ Use a custom Blade view for the email content
        return (new MailMessage)
            ->subject('Reset Your Password')
            ->view('emails.reset-password', [
                'resetUrl' => $resetUrl,
                'email' => $notifiable->email,
            ]);
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
