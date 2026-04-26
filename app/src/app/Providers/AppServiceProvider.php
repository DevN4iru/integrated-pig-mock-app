<?php

namespace App\Providers;

use App\Models\Pig;
use App\Services\NotificationGenerationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        Pig::saving(function (Pig $pig): void {
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
