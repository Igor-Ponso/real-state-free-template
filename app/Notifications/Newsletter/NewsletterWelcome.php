<?php

namespace App\Notifications\Newsletter;

use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Welcome email sent after a subscriber confirms their email.
 */
class NewsletterWelcome extends Notification
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
        return (new MailMessage)
            ->subject('Welcome to the '.config('app.name').' newsletter')
            ->markdown('mail.newsletter.welcome', [
                'propertiesUrl' => url('/properties'),
                'unsubscribeUrl' => route('newsletter.unsubscribe', ['token' => $notifiable->confirmation_token]),
                'appName' => config('app.name'),
            ]);
    }
}
