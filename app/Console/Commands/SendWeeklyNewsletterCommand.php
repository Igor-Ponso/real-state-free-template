<?php

namespace App\Console\Commands;

use App\Models\NewsletterSubscriber;
use App\Models\Property;
use App\Notifications\Newsletter\NewsletterWeeklyDigest;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

#[Signature('newsletter:weekly {--dry : Run without dispatching emails}')]
#[Description('Send the weekly property digest to all confirmed newsletter subscribers.')]
class SendWeeklyNewsletterCommand extends Command
{
    public function handle(): int
    {
        $subscribers = NewsletterSubscriber::active()->get();

        if ($subscribers->isEmpty()) {
            $this->info('No active subscribers — nothing to send.');

            return self::SUCCESS;
        }

        $featured = Property::published()
            ->featured()
            ->with(['city', 'propertyType', 'media'])
            ->latest('published_at')
            ->take(6)
            ->get();

        if ($featured->isEmpty()) {
            $this->warn('No featured published properties this week — skipping digest.');

            return self::SUCCESS;
        }

        if ($this->option('dry')) {
            $this->info("[dry-run] Would send digest to {$subscribers->count()} subscriber(s) with {$featured->count()} featured properties.");

            return self::SUCCESS;
        }

        $subscribers->each(
            fn (NewsletterSubscriber $sub) => Notification::send($sub, new NewsletterWeeklyDigest($featured))
        );

        $this->info("Dispatched digest to {$subscribers->count()} subscriber(s).");

        return self::SUCCESS;
    }
}
