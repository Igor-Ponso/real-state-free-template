<?php

namespace App\Notifications\Newsletter;

use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Double-opt-in confirmation email for newsletter subscriptions.
 *
 * Sent once on subscribe/re-subscribe. The link sets `confirmed_at`
 * and transitions the subscriber into the active list.
 */
class NewsletterConfirm extends Notification
{
    use Queueable;

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        /** @var NewsletterSubscriber $notifiable */
        $confirmUrl = route('newsletter.confirm', ['token' => $notifiable->confirmation_token]);

        return (new MailMessage)
            ->subject('Confirm your subscription — '.config('app.name'))
            ->markdown('mail.newsletter.confirm', [
                'confirmUrl' => $confirmUrl,
                'appName' => config('app.name'),
            ]);
    }
}
