<?php

use App\Services\EmailAlertDispatchService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('alerts:dispatch-email', function () {
    app(EmailAlertDispatchService::class)->dispatchScheduledAlerts();

    $this->info('Pigstep email alert dispatch pass completed.');
})->purpose('Dispatch scheduled Pigstep email alerts');

Schedule::command('alerts:dispatch-email')->everyMinute();
