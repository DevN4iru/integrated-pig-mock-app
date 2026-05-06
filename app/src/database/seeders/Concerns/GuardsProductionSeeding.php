<?php

namespace Database\Seeders\Concerns;

trait GuardsProductionSeeding
{
    protected function guardAgainstProductionSeeding(string $seederClass): void
    {
        $allowProductionSeeding = filter_var(
            env('PIGSTEP_ALLOW_PRODUCTION_SEEDING', false),
            FILTER_VALIDATE_BOOLEAN
        );

        if (app()->environment('production') && !$allowProductionSeeding) {
            throw new \RuntimeException(
                $seederClass . ' blocked. Production seeding is disabled to protect real farm/client data. ' .
                'Set PIGSTEP_ALLOW_PRODUCTION_SEEDING=true only during supervised maintenance, then set it back to false.'
            );
        }
    }
}
