<?php

namespace App\Notifications\Newsletter;

use App\Models\NewsletterSubscriber;
use App\Models\Property;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Weekly digest of featured published properties.
 *
 * Queued so the scheduler doesn't block on a large subscriber list.
 * Template renders property title, price, and city with a link to the listing.
 */
class NewsletterWeeklyDigest extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  Collection<int, Property>  $properties
     */
    public function __construct(public Collection $properties) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        /** @var NewsletterSubscriber $notifiable */
        return (new MailMessage)
            ->subject('This week at '.config('app.name'))
            ->markdown('mail.newsletter.digest', [
                'properties' => $this->properties,
                'unsubscribeUrl' => route('newsletter.unsubscribe', ['token' => $notifiable->confirmation_token]),
                'appName' => config('app.name'),
            ]);
    }
}
