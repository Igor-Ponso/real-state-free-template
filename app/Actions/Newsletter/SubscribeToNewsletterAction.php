<?php

namespace App\Actions\Newsletter;

use App\Models\NewsletterSubscriber;
use App\Notifications\Newsletter\NewsletterConfirm;
use Illuminate\Support\Str;

/**
 * Register or re-subscribe an email address to the newsletter.
 *
 * Idempotent: if the email already exists (looked up via CipherSweet blind index),
 * the existing row is updated rather than duplicated. If the subscriber had
 * previously unsubscribed, they're reactivated (unsubscribed_at cleared) and
 * must confirm again via email.
 *
 * Always sends a confirmation email — double-opt-in is mandatory to prevent
 * list poisoning and satisfy CAN-SPAM / CASL / GDPR.
 */
class SubscribeToNewsletterAction
{
    public function execute(string $email, ?string $ipAddress = null): NewsletterSubscriber
    {
        $subscriber = NewsletterSubscriber::whereBlind('email', 'newsletter_email_index', $email)->first();

        if ($subscriber === null) {
            $subscriber = NewsletterSubscriber::create([
                'email' => $email,
                'confirmation_token' => Str::random(64),
                'ip_address' => $ipAddress,
            ]);
        } elseif ($subscriber->unsubscribed_at !== null || $subscriber->confirmed_at === null) {
            // Re-subscribe or refresh unconfirmed — issue a new token either way.
            $subscriber->update([
                'confirmation_token' => Str::random(64),
                'confirmed_at' => null,
                'unsubscribed_at' => null,
                'ip_address' => $ipAddress ?? $subscriber->ip_address,
            ]);
        }

        // Always send confirmation email — even for re-subscribes — so the user
        // explicitly confirms intent. Skip only if already actively subscribed.
        if ($subscriber->confirmed_at === null) {
            $subscriber->notify(new NewsletterConfirm);
        }

        return $subscriber;
    }
}
