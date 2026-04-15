<?php

namespace App\Notifications\Security;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Security alert — sent when a user's password is changed or reset.
 *
 * Functions as confirmation plus intrusion detection: if the recipient
 * ever sees this email without having changed their password, they can
 * initiate recovery immediately.
 */
class PasswordChanged extends Notification
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
            ->subject('Your password was changed — '.config('app.name'))
            ->markdown('mail.security.password-changed', [
                'name' => $notifiable->name,
                'appName' => config('app.name'),
                'securityUrl' => url('/settings/security'),
                'supportEmail' => config('mail.from.address'),
            ]);
    }
}
