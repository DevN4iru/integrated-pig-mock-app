<?php

namespace App\Services;

use App\Models\Pig;
use App\Models\ReproductionCycle;
use Carbon\Carbon;

class ProtocolEligibilityService
{
    public function qualifiesForAnyClientProtocol(Pig $pig): bool
    {
        return $this->qualifiesForRegisteredPigletProtocol($pig)
            || $this->qualifiesForLactatingSowProtocol($pig);
    }

    public function qualifiesForRegisteredPigletProtocol(Pig $pig): bool
    {
        return strtolower((string) $pig->pig_source) === 'birthed'
            && $pig->reproduction_cycle_id !== null
            && !$this->hasSowReproductionHistory($pig)
            && $this->birthCycleHasActualFarrowing($pig);
    }

    public function qualifiesForLactatingSowProtocol(Pig $pig): bool
    {
        return strtolower((string) $pig->sex) === 'female'
            && $this->latestActualFarrowingCycle($pig) !== null;
    }

    public function latestActualFarrowingCycle(Pig $pig): ?ReproductionCycle
    {
        if ($pig->relationLoaded('reproductionCyclesAsSow')) {
            return $pig->reproductionCyclesAsSow
                ->filter(fn ($cycle) => $cycle->actual_farrow_date !== null)
                ->sortByDesc(function ($cycle): string {
                    $date = $cycle->actual_farrow_date
                        ? Carbon::parse($cycle->actual_farrow_date)->format('Y-m-d')
                        : '';

                    return sprintf('%s-%010d', $date, (int) $cycle->id);
                })
                ->first();
        }

        return $pig->reproductionCyclesAsSow()
            ->whereNotNull('actual_farrow_date')
            ->orderByDesc('actual_farrow_date')
            ->orderByDesc('id')
            ->first();
    }

    public function registeredPigletAnchorDate(Pig $pig): ?Carbon
    {
        if (!$this->qualifiesForRegisteredPigletProtocol($pig)) {
            return null;
        }

        if ($pig->relationLoaded('birthCycle') && $pig->birthCycle?->actual_farrow_date) {
            return Carbon::parse($pig->birthCycle->actual_farrow_date)->startOfDay();
        }

        $birthCycle = $pig->birthCycle()
            ->whereNotNull('actual_farrow_date')
            ->first(['id', 'actual_farrow_date']);

        return $birthCycle?->actual_farrow_date
            ? Carbon::parse($birthCycle->actual_farrow_date)->startOfDay()
            : null;
    }

    public function lactatingSowAnchorDate(Pig $pig): ?Carbon
    {
        $cycle = $this->latestActualFarrowingCycle($pig);

        return $cycle?->actual_farrow_date
            ? Carbon::parse($cycle->actual_farrow_date)->startOfDay()
            : null;
    }

    protected function hasSowReproductionHistory(Pig $pig): bool
    {
        if ($pig->relationLoaded('reproductionCyclesAsSow')) {
            return $pig->reproductionCyclesAsSow->isNotEmpty();
        }

        return $pig->reproductionCyclesAsSow()->exists();
    }

    protected function birthCycleHasActualFarrowing(Pig $pig): bool
    {
        if ($pig->relationLoaded('birthCycle')) {
            return $pig->birthCycle !== null
                && $pig->birthCycle->actual_farrow_date !== null;
        }

        return $pig->birthCycle()
            ->whereNotNull('actual_farrow_date')
            ->exists();
    }
}
