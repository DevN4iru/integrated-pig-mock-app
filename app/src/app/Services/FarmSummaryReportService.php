<?php

namespace App\Services;

use App\Models\Pen;
use App\Models\Pig;
use App\Models\ReproductionCycle;
use Illuminate\Support\Carbon;

class FarmSummaryReportService
{
    public function summary(): array
    {
        $generatedAt = Carbon::now();

        $pigs = Pig::with([
            'pen',
            'sales',
            'mortalityLogs',
            'feedLogs',
            'medications',
            'vaccinations',
            'healthLogs',
            'reproductionCyclesAsSow.updates',
            'protocolExecutions.medication',
            'protocolExecutions.vaccination',
        ])->get();

        $groupedPigs = $pigs
            ->groupBy(fn (Pig $pig) => $pig->lifecycle_state)
            ->map(fn ($group) => $group->values());

        $livePigs = $groupedPigs->get('active', collect());
        $soldPigs = $groupedPigs->get('sold', collect());
        $deadPigs = $groupedPigs->get('dead', collect());

        $totalAssetValue = (float) $livePigs->sum(fn (Pig $pig) => (float) $pig->active_live_value);
        $totalRevenue = (float) $soldPigs->flatMap->sales->sum('price');
        $mortalityLoss = (float) $deadPigs->sum(fn (Pig $pig) => (float) $pig->frozen_mortality_value);

        $totalFeedCost = (float) $pigs->sum(fn (Pig $pig) => (float) $pig->total_feed_cost);
        $totalMedicationCost = (float) $pigs->sum(fn (Pig $pig) => (float) $pig->total_medication_cost);
        $totalVaccinationCost = (float) $pigs->sum(fn (Pig $pig) => (float) $pig->total_vaccination_cost);
        $totalBreedingCost = (float) $pigs->sum(fn (Pig $pig) => (float) $pig->total_breeding_cost);
        $totalCareLiability = (float) $pigs->sum(fn (Pig $pig) => (float) $pig->total_care_liability);
        $totalOperatingCost = (float) $pigs->sum(fn (Pig $pig) => (float) $pig->total_operating_cost);

        $netPosition = $totalAssetValue + $totalRevenue - $mortalityLoss - $totalOperatingCost;

        $protocolDueToday = 0;
        $protocolOverdue = 0;

        foreach ($livePigs as $pig) {
            $protocolSummary = $pig->protocol_summary;

            if (! $protocolSummary) {
                continue;
            }

            $protocolDueToday += count($protocolSummary['due_today'] ?? []);
            $protocolOverdue += count($protocolSummary['overdue'] ?? []);
        }

        return [
            'generated_at' => $generatedAt,
            'rows' => [
                ['metric' => 'generated_at', 'value' => $generatedAt->format('Y-m-d H:i:s')],
                ['metric' => 'total_pigs', 'value' => $pigs->count()],
                ['metric' => 'active_pigs', 'value' => $livePigs->count()],
                ['metric' => 'sold_pigs', 'value' => $soldPigs->count()],
                ['metric' => 'dead_pigs', 'value' => $deadPigs->count()],
                ['metric' => 'archived_pigs', 'value' => Pig::onlyTrashed()->count()],
                ['metric' => 'pen_count', 'value' => Pen::query()->count()],
                ['metric' => 'active_breeding_records', 'value' => ReproductionCycle::query()->activeDashboardCycles()->count()],
                ['metric' => 'farrowing_due_soon', 'value' => ReproductionCycle::query()->upcomingFarrowingAlerts()->count()],
                ['metric' => 'pending_pregnancy_checks', 'value' => ReproductionCycle::query()->pendingPregnancyChecksDashboardCycles()->count()],
                ['metric' => 'stale_weight_pigs', 'value' => $pigs->filter(fn (Pig $pig) => $pig->has_stale_weight)->count()],
                ['metric' => 'protocol_due_today', 'value' => $protocolDueToday],
                ['metric' => 'protocol_overdue', 'value' => $protocolOverdue],
                ['metric' => 'total_asset_value', 'value' => $this->money($totalAssetValue)],
                ['metric' => 'total_revenue', 'value' => $this->money($totalRevenue)],
                ['metric' => 'mortality_loss', 'value' => $this->money($mortalityLoss)],
                ['metric' => 'total_feed_cost', 'value' => $this->money($totalFeedCost)],
                ['metric' => 'total_medication_cost', 'value' => $this->money($totalMedicationCost)],
                ['metric' => 'total_vaccination_cost', 'value' => $this->money($totalVaccinationCost)],
                ['metric' => 'total_breeding_cost', 'value' => $this->money($totalBreedingCost)],
                ['metric' => 'total_care_liability', 'value' => $this->money($totalCareLiability)],
                ['metric' => 'total_operating_cost', 'value' => $this->money($totalOperatingCost)],
                ['metric' => 'net_position', 'value' => $this->money($netPosition)],
            ],
        ];
    }

    protected function money(float $value): string
    {
        return number_format($value, 2, '.', '');
    }
}
