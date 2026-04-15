<?php

namespace App\Http\Controllers;

use App\Actions\Newsletter\SubscribeToNewsletterAction;
use App\Http\Requests\SubscribeNewsletterRequest;
use App\Models\NewsletterSubscriber;
use App\Notifications\Newsletter\NewsletterWelcome;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Public newsletter subscription flow with double opt-in.
 *
 * Flow: store (submit) → confirm (click link) → unsubscribe (click link later).
 * All three endpoints are public (no auth). Rate-limited via `public` limiter.
 */
class NewsletterController extends Controller
{
    public function store(
        SubscribeNewsletterRequest $request,
        SubscribeToNewsletterAction $subscribe,
    ): RedirectResponse {
        $subscribe->execute($request->validated('email'), $request->ip());

        return back()->with('success', 'Check your inbox to confirm your subscription.');
    }

    public function confirm(string $token): Response
    {
        $subscriber = NewsletterSubscriber::where('confirmation_token', $token)->first();

        if ($subscriber === null) {
            return Inertia::render('Newsletter/Status', [
                'status' => 'invalid',
                'message' => "That confirmation link isn't valid. You may have already confirmed, or the link has expired.",
            ]);
        }

        if ($subscriber->confirmed_at === null) {
            $subscriber->update([
                'confirmed_at' => now(),
                'unsubscribed_at' => null,
            ]);
            $subscriber->notify(new NewsletterWelcome);
        }

        return Inertia::render('Newsletter/Status', [
            'status' => 'confirmed',
            'message' => "You're in. Expect a weekly digest of standout listings.",
        ]);
    }

    public function unsubscribe(string $token): Response
    {
        $subscriber = NewsletterSubscriber::where('confirmation_token', $token)->first();

        if ($subscriber === null) {
            return Inertia::render('Newsletter/Status', [
                'status' => 'invalid',
                'message' => "That unsubscribe link isn't valid.",
            ]);
        }

        $subscriber->update(['unsubscribed_at' => now()]);

        return Inertia::render('Newsletter/Status', [
            'status' => 'unsubscribed',
            'message' => "You've been unsubscribed. Resubscribe anytime from our site.",
        ]);
    }
}
