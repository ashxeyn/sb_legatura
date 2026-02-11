<?php

use App\Jobs\SendDeadlineNotifications;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ------------------------------------------------------------------
// Notification scheduler: run deadline checks every hour
// ------------------------------------------------------------------
Schedule::job(new SendDeadlineNotifications)->hourly();

// Cleanup old read notifications (> 90 days) once a day at 2 AM
Schedule::call(function () {
    \App\Models\both\notificationClass::cleanupOld(90);
})->dailyAt('02:00');
