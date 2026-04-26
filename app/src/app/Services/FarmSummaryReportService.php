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
        $protocolEligibility = new ProtocolEligibilityService();
        $pigValueService = new PigValueService();

        $pigs = Pig::with([
            'pen',
            'sales',
            'mortalityLogs',
            'feedLogs',
            'medications',
            'vaccinations',
            'healthLogs',
            'birthCycle:id,actual_farrow_date',
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

        $totalAssetValue = (float) $livePigs->sum(fn (Pig $pig): float => $pigValueService->activeLiveValue($pig));
        $totalRevenue = (float) $soldPigs->flatMap->sales->sum('price');
        $mortalityLoss = (float) $deadPigs->sum(fn (Pig $pig) => (float) $pig->frozen_mortality_value);

        // Legacy detailed cost buckets stay calculated for future reactivation,
        // but the current client-facing report only exposes breeding cost.
        $totalFeedCost = (float) $pigs->sum(fn (Pig $pig) => (float) $pig->total_feed_cost);
        $totalMedicationCost = (float) $pigs->sum(fn (Pig $pig) => (float) $pig->total_medication_cost);
        $totalVaccinationCost = (float) $pigs->sum(fn (Pig $pig) => (float) $pig->total_vaccination_cost);
        $totalBreedingCost = (float) $pigs->sum(fn (Pig $pig) => (float) $pig->total_breeding_cost);
        $totalCareLiability = (float) $pigs->sum(fn (Pig $pig) => (float) $pig->total_care_liability);
        $totalOperatingCost = (float) $pigs->sum(fn (Pig $pig) => (float) $pig->total_operating_cost);

        $totalRecordedClientCost = $totalBreedingCost;
        $netPosition = $totalAssetValue + $totalRevenue - $mortalityLoss - $totalRecordedClientCost;

        $protocolDueTodayRows = [];
        $protocolOverdueRows = [];

        foreach ($livePigs as $pig) {
            if (! $protocolEligibility->qualifiesForAnyClientProtocol($pig)) {
                continue;
            }

            $protocolSummary = $pig->protocol_summary;

            if (! $protocolSummary) {
                continue;
            }

            foreach (($protocolSummary['due_today'] ?? []) as $row) {
                $protocolDueTodayRows[] = $this->protocolRow($pig, $row);
            }

            foreach (($protocolSummary['overdue'] ?? []) as $row) {
                $protocolOverdueRows[] = $this->protocolRow($pig, $row);
            }
        }

        $activeBreedingRecords = ReproductionCycle::query()->activeDashboardCycles()->count();
        $farrowingDueSoon = ReproductionCycle::query()->upcomingFarrowingAlerts()->count();
        $pendingPregnancyChecks = ReproductionCycle::query()->pendingPregnancyChecksDashboardCycles()->count();
        $staleWeightPigs = $pigs->filter(fn (Pig $pig) => $pig->has_stale_weight)->count();

        $metrics = [
            'total_pigs' => $pigs->count(),
            'active_pigs' => $livePigs->count(),
            'sold_pigs' => $soldPigs->count(),
            'dead_pigs' => $deadPigs->count(),
            'archived_pigs' => Pig::onlyTrashed()->count(),
            'pen_count' => Pen::query()->count(),
            'active_breeding_records' => $activeBreedingRecords,
            'farrowing_due_soon' => $farrowingDueSoon,
            'pending_pregnancy_checks' => $pendingPregnancyChecks,
            'stale_weight_pigs' => $staleWeightPigs,
            'protocol_due_today' => count($protocolDueTodayRows),
            'protocol_overdue' => count($protocolOverdueRows),
            'total_asset_value' => $totalAssetValue,
            'total_revenue' => $totalRevenue,
            'mortality_loss' => $mortalityLoss,
            'total_feed_cost' => $totalFeedCost,
            'total_medication_cost' => $totalMedicationCost,
            'total_vaccination_cost' => $totalVaccinationCost,
            'total_breeding_cost' => $totalBreedingCost,
            'total_care_liability' => $totalCareLiability,
            'total_recorded_client_cost' => $totalRecordedClientCost,
            'total_operating_cost' => $totalRecordedClientCost,
            'net_position' => $netPosition,
        ];

        return [
            'generated_at' => $generatedAt,
            'report_type' => 'Manual',
            'prepared_for' => 'Timothy Maglente',
            'product_by' => 'Kirjane Labs',
            'metrics' => $metrics,
            'rows' => $this->csvRows($generatedAt, $metrics),
            'pen_occupancy' => $this->penOccupancy(),
            'protocol_due_today_rows' => $protocolDueTodayRows,
            'protocol_overdue_rows' => $protocolOverdueRows,
            'action_checklist' => $this->actionChecklist($metrics),
        ];
    }

    protected function csvRows(Carbon $generatedAt, array $metrics): array
    {
        return [
            ['metric' => 'generated_at', 'value' => $generatedAt->format('Y-m-d H:i:s')],
            ['metric' => 'total_pigs', 'value' => $metrics['total_pigs']],
            ['metric' => 'active_pigs', 'value' => $metrics['active_pigs']],
            ['metric' => 'sold_pigs', 'value' => $metrics['sold_pigs']],
            ['metric' => 'dead_pigs', 'value' => $metrics['dead_pigs']],
            ['metric' => 'archived_pigs', 'value' => $metrics['archived_pigs']],
            ['metric' => 'pen_count', 'value' => $metrics['pen_count']],
            ['metric' => 'active_breeding_records', 'value' => $metrics['active_breeding_records']],
            ['metric' => 'farrowing_due_soon', 'value' => $metrics['farrowing_due_soon']],
            ['metric' => 'pending_pregnancy_checks', 'value' => $metrics['pending_pregnancy_checks']],
            ['metric' => 'stale_weight_pigs', 'value' => $metrics['stale_weight_pigs']],
            ['metric' => 'protocol_due_today', 'value' => $metrics['protocol_due_today']],
            ['metric' => 'protocol_overdue', 'value' => $metrics['protocol_overdue']],
            ['metric' => 'live_asset_value', 'value' => $this->money($metrics['total_asset_value'])],
            ['metric' => 'sale_revenue', 'value' => $this->money($metrics['total_revenue'])],
            ['metric' => 'mortality_loss', 'value' => $this->money($metrics['mortality_loss'])],
            ['metric' => 'breeding_cost', 'value' => $this->money($metrics['total_breeding_cost'])],
            ['metric' => 'net_position', 'value' => $this->money($metrics['net_position'])],
        ];
    }

    protected function penOccupancy(): array
    {
        return Pen::query()
            ->withCount(['activePigs as active_pigs_count'])
            ->get()
            ->sortBy(fn (Pen $pen) => $pen->sortKey())
            ->values()
            ->map(function (Pen $pen): array {
                $capacity = (int) $pen->capacity;
                $activePigCount = (int) ($pen->active_pigs_count ?? 0);

                return [
                    'name' => $pen->name,
                    'type' => $pen->type,
                    'capacity' => $capacity,
                    'active_pig_count' => $activePigCount,
                    'available_slots' => max($capacity - $activePigCount, 0),
                ];
            })
            ->all();
    }

    protected function protocolRow(Pig $pig, array $row): array
    {
        return [
            'pig_id' => $pig->id,
            'pig_ear_tag' => $pig->ear_tag,
            'action' => $row['action'] ?? '—',
            'type' => $row['type'] ?? '—',
            'requirement' => $row['requirement'] ?? '—',
            'scheduled_date' => $row['due_start'] ?? null,
            'due_end' => $row['due_end'] ?? null,
        ];
    }

    protected function actionChecklist(array $metrics): array
    {
        return [
            [
                'item' => 'Review overdue medication program items',
                'count' => $metrics['protocol_overdue'],
                'status' => $metrics['protocol_overdue'] > 0 ? 'Needs action' : 'Clear',
            ],
            [
                'item' => 'Handle medication program items due today',
                'count' => $metrics['protocol_due_today'],
                'status' => $metrics['protocol_due_today'] > 0 ? 'Due today' : 'Clear',
            ],
            [
                'item' => 'Update stale pig weights',
                'count' => $metrics['stale_weight_pigs'],
                'status' => $metrics['stale_weight_pigs'] > 0 ? 'Needs update' : 'Clear',
            ],
            [
                'item' => 'Prepare for farrowing due soon',
                'count' => $metrics['farrowing_due_soon'],
                'status' => $metrics['farrowing_due_soon'] > 0 ? 'Monitor closely' : 'Clear',
            ],
            [
                'item' => 'Complete pending pregnancy checks',
                'count' => $metrics['pending_pregnancy_checks'],
                'status' => $metrics['pending_pregnancy_checks'] > 0 ? 'Pending' : 'Clear',
            ],
        ];
    }

    protected function money(float $value): string
    {
        return number_format($value, 2, '.', '');
    }
}
