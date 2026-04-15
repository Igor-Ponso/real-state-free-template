<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
|
| Runs when the system cron triggers `php artisan schedule:run` every minute.
| In production, add the following line to your server's crontab:
|
|     * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
|
*/

// Weekly property digest — Mondays at 9am (subscriber timezone is server timezone;
// upgrade to per-subscriber timezone if internationalising).
Schedule::command('newsletter:weekly')
    ->weeklyOn(1, '09:00')
    ->withoutOverlapping()
    ->onOneServer();

// Clean up unconfirmed subscribers daily.
Schedule::command('newsletter:prune')
    ->daily()
    ->onOneServer();
