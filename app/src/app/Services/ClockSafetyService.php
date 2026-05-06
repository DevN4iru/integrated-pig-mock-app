<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\File;

class ClockSafetyService
{
    public function status(): array
    {
        $now = CarbonImmutable::now();
        $minDate = CarbonImmutable::parse((string) env('PIGSTEP_CLOCK_MIN_DATE', '2026-01-01'))->startOfDay();
        $backwardToleranceMinutes = max(0, (int) env('PIGSTEP_CLOCK_BACKWARD_TOLERANCE_MINUTES', 10));
        $forwardToleranceDays = max(1, (int) env('PIGSTEP_CLOCK_FORWARD_TOLERANCE_DAYS', 730));

        $anchorPath = $this->anchorPath();
        $anchor = $this->readAnchor();

        $reasons = [];

        if ($now->lessThan($minDate)) {
            $reasons[] = 'Current server date is earlier than minimum safe date ' . $minDate->toDateString() . '.';
        }

        if ($anchor && isset($anchor['last_safe_at'])) {
            try {
                $lastSafeAt = CarbonImmutable::parse((string) $anchor['last_safe_at']);

                if ($now->lessThan($lastSafeAt->subMinutes($backwardToleranceMinutes))) {
                    $reasons[] = 'Current server time is earlier than the last known safe time.';
                }

                if ($now->greaterThan($lastSafeAt->addDays($forwardToleranceDays))) {
                    $reasons[] = 'Current server time jumped too far beyond the last known safe time.';
                }
            } catch (\Throwable $exception) {
                $reasons[] = 'Clock anchor exists but could not be parsed.';
            }
        }

        $safe = empty($reasons);

        return [
            'safe' => $safe,
            'now' => $now->toIso8601String(),
            'timezone' => config('app.timezone'),
            'min_date' => $minDate->toDateString(),
            'anchor_path' => $anchorPath,
            'anchor_exists' => $anchor !== null,
            'anchor' => $anchor,
            'reasons' => $reasons,
            'alerts_blocked_when_unsafe' => filter_var(env('PIGSTEP_CLOCK_BLOCK_ALERT_MAIL', true), FILTER_VALIDATE_BOOLEAN),
        ];
    }

    public function isSafe(): bool
    {
        return (bool) $this->status()['safe'];
    }

    public function shouldBlockAlertMail(): bool
    {
        $enabled = filter_var(env('PIGSTEP_REQUIRE_SAFE_CLOCK', true), FILTER_VALIDATE_BOOLEAN);
        $blockAlertMail = filter_var(env('PIGSTEP_CLOCK_BLOCK_ALERT_MAIL', true), FILTER_VALIDATE_BOOLEAN);

        return $enabled && $blockAlertMail && !$this->isSafe();
    }

    public function recordSafeAnchor(): void
    {
        $status = $this->status();

        if (!$status['safe']) {
            return;
        }

        $path = $this->anchorPath();
        File::ensureDirectoryExists(dirname($path));

        File::put($path, json_encode([
            'last_safe_at' => CarbonImmutable::now()->toIso8601String(),
            'timezone' => config('app.timezone'),
            'source' => 'pigstep:clock-check',
        ], JSON_PRETTY_PRINT));
    }

    public function anchorPath(): string
    {
        return storage_path('app/pigstep-clock-safe-anchor.json');
    }

    protected function readAnchor(): ?array
    {
        $path = $this->anchorPath();

        if (!File::exists($path)) {
            return null;
        }

        $decoded = json_decode((string) File::get($path), true);

        return is_array($decoded) ? $decoded : null;
    }
}
