<?php

namespace App\Providers;

use App\Services\NotificationGenerationService;
use Illuminate\Support\Facades\Log;
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
