<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Prune position history older than HISTORY_RETENTION_DAYS (default 7).
// Runs daily at 02:00 server time; chunked deletes keep locks short.
Schedule::command('positions:prune-history')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->onOneServer();
