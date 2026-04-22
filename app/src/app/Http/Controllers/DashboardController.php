<?php

namespace App\Http\Controllers;

use App\Models\HealthLog;
use App\Models\MortalityLog;
use App\Models\Pig;
use App\Models\ReproductionCycle;
use App\Models\Sale;

class DashboardController extends Controller
{
    public function index()
    {
        $pigs = Pig::with([
            'pen',
            'sales',
            'mortalityLogs',
            'feedLogs',
            'medications',
            'vaccinations',
            'healthLogs',
            'reproductionCyclesAsSow.updates',
        ])->get();

        $buildWeightLogs = function ($pig) {
            return $pig->healthLogs
                ->filter(fn ($log) => $log->purpose === 'weight_update' && $log->weight !== null)
                ->sortByDesc(fn ($log) => sprintf('%s-%010d', (string) ($log->log_date ?? ''), (int) $log->id))
                ->values();
        };

        $resolveFrozenMortalityLoss = function ($pig): float {
            $mortalityLog = $pig->mortalityLogs
                ->sortByDesc(fn ($log) => sprintf(
                    '%s-%010d',
                    $log->death_date?->format('Y-m-d') ?? (string) ($log->death_date ?? ''),
                    (int) $log->id
                ))
                ->first();

            if (!$mortalityLog) {
                return 0.0;
            }

            if ($mortalityLog->loss_value !== null) {
                return (float) $mortalityLog->loss_value;
            }

            return (float) ($pig->asset_value ?? 0);
        };

        $groupedPigs = $pigs
            ->groupBy(fn ($pig) => $pig->lifecycle_state)
            ->map(fn ($group) => $group->values());

        $livePigs = $groupedPigs->get('active', collect());
        $soldPigs = $groupedPigs->get('sold', collect());
        $deadPigs = $groupedPigs->get('dead', collect());

        $totalAssetValue = (float) $livePigs->sum(fn ($pig) => (float) $pig->computed_asset_value);
        $totalRevenue = (float) $soldPigs->flatMap->sales->sum('price');
        $totalLossValue = (float) $deadPigs->sum(fn ($pig) => $resolveFrozenMortalityLoss($pig));

        $totalFeedCost = (float) $pigs->sum(fn ($pig) => (float) $pig->total_feed_cost);
        $totalMedicationCost = (float) $pigs->sum(fn ($pig) => (float) $pig->total_medication_cost);
        $totalVaccinationCost = (float) $pigs->sum(fn ($pig) => (float) $pig->total_vaccination_cost);
        $totalBreedingCost = (float) $pigs->sum(fn ($pig) => (float) $pig->total_breeding_cost);
        $totalCareLiability = (float) $pigs->sum(fn ($pig) => (float) $pig->total_care_liability);
        $totalOperatingCost = (float) $pigs->sum(fn ($pig) => (float) $pig->total_operating_cost);

        $netPosition = $totalAssetValue + $totalRevenue - $totalLossValue - $totalOperatingCost;

        $positiveGainPigs = $pigs->filter(function ($pig) {
            return $pig->total_feed_kg > 0 && $pig->positive_gain_from_start !== null;
        });

        $totalFeedKgForEfficiency = (float) $positiveGainPigs->sum(fn ($pig) => (float) $pig->total_feed_kg);
        $totalGainForEfficiency = (float) $positiveGainPigs->sum(fn ($pig) => (float) ($pig->positive_gain_from_start ?? 0));

        $farmFeedEfficiency = $totalFeedKgForEfficiency > 0 && $totalGainForEfficiency > 0
            ? $totalFeedKgForEfficiency / $totalGainForEfficiency
            : null;

        $recentSales = Sale::with('pig')->latest()->take(5)->get();
        $recentMortality = MortalityLog::with('pig')->latest()->take(5)->get();
        $recentHealthAlerts = HealthLog::with('pig')
            ->whereIn('purpose', ['sick', 'injury', 'recovered'])
            ->latest()
            ->take(5)
            ->get();

        $upcomingFarrowings = ReproductionCycle::query()
            ->withDashboardRelations()
            ->upcomingFarrowingAlerts()
            ->take(5)
            ->get();

        $activeBreedingCycles = ReproductionCycle::query()
            ->withDashboardRelations()
            ->activeDashboardCycles()
            ->take(5)
            ->get();

        $dueSoonCycles = ReproductionCycle::query()
            ->withDashboardRelations()
            ->dueSoonDashboardCycles()
            ->take(5)
            ->get();

        $returnedToHeatCycles = ReproductionCycle::query()
            ->withDashboardRelations()
            ->returnedToHeatDashboardCycles()
            ->take(5)
            ->get();

        $pendingPregnancyChecks = ReproductionCycle::query()
            ->withDashboardRelations()
            ->pendingPregnancyChecksDashboardCycles()
            ->take(5)
            ->get();

        $staleWeightPigs = $pigs->filter(function ($pig) {
            if (!$pig->latest_weight_log_date) {
                return true;
            }

            return now()->diffInDays($pig->latest_weight_log_date) > 7;
        });

        $weightAlertRows = $staleWeightPigs->map(function ($pig) use ($buildWeightLogs) {
            $weightLogs = $buildWeightLogs($pig);
            $latest = $weightLogs->get(0);
            $previous = $weightLogs->get(1);

            $trendSymbol = '—';
            $trendLabel = 'No change baseline';

            if ($latest && $previous) {
                if ((float) $latest->weight > (float) $previous->weight) {
                    $trendSymbol = '↑';
                    $trendLabel = 'Increasing';
                } elseif ((float) $latest->weight < (float) $previous->weight) {
                    $trendSymbol = '↓';
                    $trendLabel = 'Dropping';
                } else {
                    $trendSymbol = '→';
                    $trendLabel = 'Stable';
                }
            } elseif ($latest) {
                $trendSymbol = '→';
                $trendLabel = 'Only one record';
            }

            return [
                'pig' => $pig,
                'latest_weight' => $pig->computed_weight,
                'trend_symbol' => $trendSymbol,
                'trend_label' => $trendLabel,
            ];
        });

        $growthGroups = [
            'good' => collect(),
            'declining' => collect(),
            'stagnant' => collect(),
            'no_data' => collect(),
        ];

        foreach ($pigs as $pig) {
            $growthGroups[$pig->growth_status]->push($pig);
        }

        $growthSummary = [
            'good' => $growthGroups['good']->count(),
            'declining' => $growthGroups['declining']->count(),
            'stagnant' => $growthGroups['stagnant']->count(),
            'no_data' => $growthGroups['no_data']->count(),
        ];

        $bestPerformers = $pigs
            ->filter(fn ($pig) => $pig->cost_per_kg_gain !== null)
            ->sortBy('cost_per_kg_gain')
            ->take(5)
            ->values();

        $riskPigs = $pigs
            ->filter(fn ($pig) => in_array($pig->performance_status, ['inefficient', 'risk'], true))
            ->sortByDesc(function ($pig) {
                return $pig->cost_per_kg_gain ?? ($pig->performance_status === 'risk' ? 999999 : 0);
            })
            ->take(5)
            ->values();

        return view('dashboard', compact(
            'pigs',
            'livePigs',
            'soldPigs',
            'deadPigs',
            'totalAssetValue',
            'totalRevenue',
            'totalLossValue',
            'netPosition',
            'totalFeedCost',
            'totalMedicationCost',
            'totalVaccinationCost',
            'totalBreedingCost',
            'totalCareLiability',
            'totalOperatingCost',
            'farmFeedEfficiency',
            'recentSales',
            'recentMortality',
            'recentHealthAlerts',
            'upcomingFarrowings',
            'activeBreedingCycles',
            'dueSoonCycles',
            'returnedToHeatCycles',
            'pendingPregnancyChecks',
            'weightAlertRows',
            'growthGroups',
            'growthSummary',
            'bestPerformers',
            'riskPigs'
        ));
    }
}
