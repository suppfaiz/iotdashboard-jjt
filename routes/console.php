<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('energy:log-daily')->dailyAt('23:59');
Schedule::command('energy:log-hourly')->hourly();
Schedule::command('devices:monitor')->everyMinute();
