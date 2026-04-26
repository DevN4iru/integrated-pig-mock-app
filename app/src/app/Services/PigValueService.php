<?php

namespace App\Services;

use App\Models\Pig;

class PigValueService
{
    public function isExcludedFromFarmValue(Pig $pig): bool
    {
        return (bool) ($pig->exclude_from_value_computation ?? false);
    }

    public function activeLiveValue(Pig $pig): float
    {
        if ($this->isExcludedFromFarmValue($pig)) {
            return 0.0;
        }

        if (!$pig->is_active_lifecycle) {
            return 0.0;
        }

        return (float) $pig->active_live_value;
    }

    public function profileValue(Pig $pig): float
    {
        if ($this->isExcludedFromFarmValue($pig)) {
            return 0.0;
        }

        return (float) ($pig->computed_asset_value ?? $pig->asset_value ?? 0);
    }

    public function recalculatedSeedValue(Pig $pig): float
    {
        return Pig::preservedAssetValueSeedFromWeight(
            (float) ($pig->computed_weight ?? $pig->latest_weight ?? 0)
        );
    }

    public function valueStatusLabel(Pig $pig): string
    {
        return $this->isExcludedFromFarmValue($pig)
            ? 'Excluded from totals'
            : 'Included in totals';
    }

    public function valueDisplay(Pig $pig): string
    {
        return $this->isExcludedFromFarmValue($pig)
            ? 'Not counted'
            : '₱ ' . number_format($this->profileValue($pig), 2);
    }
}
