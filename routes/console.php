<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;
Schedule::command('itsm:check-tat')->everyFifteenMinutes();
// Dialer: pull Smartping recordings onto our own disk in case they expire.
// No-op until there are external recording URLs, so safe to leave scheduled.
Schedule::command('dialer:backup-recordings')->dailyAt('02:30');
