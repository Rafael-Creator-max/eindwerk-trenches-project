<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;

class ResetPasswordNotification extends Notification
{
    

    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $resetUrl = URL::temporarySignedRoute(
            'password.reset',
            Carbon::now()->addMinutes(60),
            [
                'token' => $this->token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]
        );

        // For frontend URL
        $frontendUrl = rtrim(config('app.frontend_url', 'http://localhost:3000'), '/');
        $resetUrl = "{$frontendUrl}/reset-password?" . http_build_query([
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset()
        ]);

        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('Reset Password Notification')
            ->view('emails.password-reset', [
                'resetUrl' => $resetUrl,
                'user' => $notifiable
            ]);
    }
}
