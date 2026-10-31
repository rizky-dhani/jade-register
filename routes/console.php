<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('queue:work --stop-when-empty', [
    '--queue' => 'high,default',
    '--sleep' => 3,
    '--tries' => 3,
    '--max-time' => 3600,
])->everyMinute()->withoutOverlapping();

// Scheduled even when disabled: the command no-ops so the schedule stays
// predictable, and enabling it is a config change rather than a deploy.
Schedule::command('sheets:sync-registrations')
    ->everyTenMinutes()
    ->withoutOverlapping();
