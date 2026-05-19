<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('queue:work', [
    '--queue' => 'high,default',
    '--sleep' => 3,
    '--tries' => 3,
    '--max-time' => 3600,
    '--stop-when-empty' => true,
])->everyMinute()->withoutOverlapping();
