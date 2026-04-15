<?php

namespace App\Notifications\Security;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Security alert — sent when a user enables 2FA on their account.
 *
 * Functions as confirmation plus intrusion detection: if the recipient
 * ever sees this email without having enabled 2FA themselves, they know
 * the account may be compromised.
 */
class TwoFactorEnabled extends Notification
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
            ->subject('Two-factor authentication enabled — '.config('app.name'))
            ->markdown('mail.security.two-factor-enabled', [
                'name' => $notifiable->name,
                'appName' => config('app.name'),
                'securityUrl' => url('/settings/security'),
            ]);
    }
}
