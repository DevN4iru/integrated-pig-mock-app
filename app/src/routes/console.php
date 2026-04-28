<?php

use App\Models\EmailAlertDelivery;
use App\Models\User;
use App\Services\EmailAlertDispatchService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Validator;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('auth:create-owner {email} {--name=Owner} {--password=}', function (string $email) {
    $name = trim((string) $this->option('name'));
    $password = (string) $this->option('password');

    if ($name === '') {
        $name = 'Owner';
    }

    if ($password === '') {
        $password = (string) $this->secret('Owner password');
    }

    $validated = Validator::make(
        [
            'email' => $email,
            'name' => $name,
            'password' => $password,
        ],
        [
            'email' => ['required', 'email:rfc', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
        ]
    )->validate();

    $user = User::query()->updateOrCreate(
        ['email' => $validated['email']],
        [
            'name' => $validated['name'],
            'password' => $validated['password'],
        ]
    );

    $this->info('Owner account ready: '.$user->email);
})->purpose('Create or update the Pigstep owner login account');

Artisan::command('alerts:test-email {--to=}', function () {
    $recipient = trim((string) $this->option('to'));

    $delivery = app(EmailAlertDispatchService::class)->dispatchTestEmail(
        $recipient !== '' ? $recipient : null
    );

    if ($delivery->status === EmailAlertDelivery::STATUS_SENT) {
        $this->info('Pigstep real alert test email sent to ' . $delivery->recipient . '.');
        return 0;
    }

    $this->error('Pigstep real alert test email failed: ' . ($delivery->error_message ?: 'Unknown error.'));
    return 1;
})->purpose('Send one real Pigstep alert email through the production alert mail path');

Artisan::command('alerts:dispatch-email', function () {
    app(EmailAlertDispatchService::class)->dispatchScheduledAlerts();

    $this->info('Pigstep email alert dispatch pass completed.');
})->purpose('Dispatch scheduled Pigstep email alerts');

Schedule::command('alerts:dispatch-email')->everyMinute();
