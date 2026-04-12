<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Welcome email sent to newly registered users.
 */
class WelcomeUser extends Notification
{
    use Queueable;

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to '.config('app.name'))
            ->markdown('mail.welcome', [
                'name' => $notifiable->name,
                'propertiesUrl' => url('/properties'),
                'dashboardUrl' => url('/dashboard'),
            ]);
    }
}
