<?php

namespace App\Console\Commands;

use App\Models\NewsletterSubscriber;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('newsletter:prune')]
#[Description('Delete newsletter subscribers who never confirmed within 7 days.')]
class PruneNewsletterSubscribersCommand extends Command
{
    public function handle(): int
    {
        $deleted = NewsletterSubscriber::whereNull('confirmed_at')
            ->where('created_at', '<', now()->subDays(7))
            ->delete();

        $this->info("Pruned {$deleted} unconfirmed subscriber(s).");

        return self::SUCCESS;
    }
}
