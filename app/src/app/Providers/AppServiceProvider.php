<?php

namespace App\Providers;

use App\Models\Pen;
use App\Models\Pig;
use App\Services\NotificationGenerationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        /*
         * Pigstep server clock safety:
         * If the machine boots with a bad CMOS/system date, block automated alert/reminder mail.
         * This protects breeding windows, protocol due dates, age-based reminders, and daily alerts.
         */
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Mail\Events\MessageSending::class, function (\Illuminate\Mail\Events\MessageSending $event) {
            $subject = method_exists($event->message, 'getSubject')
                ? strtolower((string) $event->message->getSubject())
                : '';

            $blockAllMail = filter_var(env('PIGSTEP_CLOCK_BLOCK_ALL_MAIL', false), FILTER_VALIDATE_BOOLEAN);
            $looksLikePigstepAlert = str_contains($subject, 'pigstep')
                || str_contains($subject, 'alert')
                || str_contains($subject, 'reminder')
                || str_contains($subject, 'protocol')
                || str_contains($subject, 'farrow')
                || str_contains($subject, 'due');

            if (!$blockAllMail && !$looksLikePigstepAlert) {
                return null;
            }

            $clock = app(\App\Services\ClockSafetyService::class);

            if ($clock->shouldBlockAlertMail()) {
                \Illuminate\Support\Facades\Log::warning('Pigstep alert mail blocked because server clock is unsafe.', $clock->status());

                return false;
            }

            return null;
        });

        Pig::saving(function (Pig $pig): void {
            $pen = null;

            if ($pig->pen_id) {
                $pen = $pig->relationLoaded('pen')
                    ? $pig->pen
                    : Pen::query()->find($pig->pen_id);
            }

            if ($pen?->type === Pen::TYPE_BOAR && strtolower((string) $pig->sex) !== 'male') {
                throw ValidationException::withMessages([
                    'sex' => 'Only male pigs can be assigned to a Boar pen.',
                ]);
            }

            if (!Schema::hasTable('pigs') || !Schema::hasColumn('pigs', 'exclude_from_value_computation')) {
                return;
            }

            if (!request()->has('exclude_from_value_computation')) {
                return;
            }

            $excluded = request()->boolean('exclude_from_value_computation');

            $pig->forceFill([
                'exclude_from_value_computation' => $excluded,
                'asset_value' => $excluded
                    ? 0
                    : Pig::preservedAssetValueSeedFromWeight((float) ($pig->latest_weight ?? 0)),
            ]);
        });

        View::composer('layouts.app', function ($view): void {
            try {
                app(NotificationGenerationService::class)->generateFirstWave();
            } catch (\Throwable $throwable) {
                Log::warning('First-wave notification generation failed during layout composition.', [
                    'exception' => get_class($throwable),
                    'message' => $throwable->getMessage(),
                ]);
            }
        });
    }
}
