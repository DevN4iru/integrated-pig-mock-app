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

        $buildMetrics = function ($pig) use ($buildWeightLogs) {
            $weightLogs = $buildWeightLogs($pig);
            $latestLog = $weightLogs->get(0);
            $previousLog = $weightLogs->get(1);

            $baselineWeight = $pig->latest_weight !== null && $pig->latest_weight !== ''
                ? (float) $pig->latest_weight
                : null;

            $computedWeight = $latestLog
                ? (float) $latestLog->weight
                : (float) ($baselineWeight ?? 0);

            $weightGain = null;
            $dailyGain = null;

            if ($latestLog && $previousLog) {
                $weightGain = (float) $latestLog->weight - (float) $previousLog->weight;

                $days = max(
                    1,
                    now()->parse($latestLog->log_date)->diffInDays(now()->parse($previousLog->log_date))
                );

                $dailyGain = $weightGain / $days;
            } elseif ($latestLog && $baselineWeight !== null) {
                $weightGain = (float) $latestLog->weight - $baselineWeight;

                $baselineDate = $pig->date_added ? now()->parse($pig->date_added) : null;
                $days = $baselineDate
                    ? max(1, now()->parse($latestLog->log_date)->diffInDays($baselineDate))
                    : 1;

                $dailyGain = $weightGain / $days;
            }

            $growthStatus = 'no_data';

            if ($weightGain !== null) {
                if ($weightGain > 0) {
                    $growthStatus = 'good';
                } elseif ($weightGain < 0) {
                    $growthStatus = 'declining';
                } else {
                    $growthStatus = 'stagnant';
                }
            }

            return [
                'weight_logs' => $weightLogs,
                'latest_log_date' => $latestLog?->log_date,
                'computed_weight' => $computedWeight,
                'weight_gain' => $weightGain,
                'daily_gain' => $dailyGain,
                'growth_status' => $growthStatus,
            ];
        };

        foreach ($pigs as $pig) {
            $metrics = $buildMetrics($pig);

            $pig->dashboard_weight_logs = $metrics['weight_logs'];
            $pig->dashboard_latest_log_date = $metrics['latest_log_date'];
            $pig->dashboard_computed_weight = $metrics['computed_weight'];
            $pig->dashboard_weight_gain = $metrics['weight_gain'];
            $pig->dashboard_daily_gain = $metrics['daily_gain'];
            $pig->dashboard_growth_status = $metrics['growth_status'];
        }

        $livePigs = $pigs->filter(fn ($pig) => $pig->sales->isEmpty() && $pig->mortalityLogs->isEmpty());
        $soldPigs = $pigs->filter(fn ($pig) => $pig->sales->isNotEmpty());
        $deadPigs = $pigs->filter(fn ($pig) => $pig->mortalityLogs->isNotEmpty());

        $totalAssetValue = (float) $livePigs->sum('asset_value');
        $totalRevenue = (float) $soldPigs->flatMap->sales->sum('price');
        $totalLossValue = (float) $deadPigs->sum('asset_value');

        $totalFeedCost = (float) $pigs->sum(fn ($pig) => (float) $pig->total_feed_cost);
        $totalMedicationCost = (float) $pigs->sum(fn ($pig) => (float) $pig->total_medication_cost);
        $totalVaccinationCost = (float) $pigs->sum(fn ($pig) => (float) $pig->total_vaccination_cost);
        $totalBreedingCost = (float) $pigs->sum(fn ($pig) => (float) $pig->total_breeding_cost);
        $totalCareLiability = (float) $pigs->sum(fn ($pig) => (float) $pig->total_care_liability);
        $totalOperatingCost = (float) $pigs->sum(fn ($pig) => (float) $pig->total_operating_cost);

        $netPosition = $totalAssetValue + $totalRevenue - $totalLossValue - $totalOperatingCost;

        $positiveGainPigs = $pigs->filter(fn ($pig) => $pig->feed_efficiency !== null);

        $totalFeedKgForEfficiency = (float) $positiveGainPigs->sum(fn ($pig) => (float) $pig->total_feed_kg);
        $totalGainForEfficiency = (float) $positiveGainPigs->sum(function ($pig) {
            return max(0, (float) $pig->dashboard_computed_weight - (float) $pig->latest_weight);
        });

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

        $normalizeCycleStatuses = function ($cycles) {
            return $cycles->map(function ($cycle) {
                $cycle->status = $cycle->display_status;
                return $cycle;
            })->values();
        };

        $upcomingFarrowings = ReproductionCycle::with(['sow.pen', 'boar'])
            ->whereIn('status', [
                ReproductionCycle::STATUS_PREGNANT,
                ReproductionCycle::STATUS_DUE_SOON,
            ])
            ->where('pregnancy_result', ReproductionCycle::PREGNANCY_RESULT_PREGNANT)
            ->whereNotNull('expected_farrow_date')
            ->whereBetween('expected_farrow_date', [
                now()->toDateString(),
                now()->copy()->addDays(14)->toDateString(),
            ])
            ->orderBy('expected_farrow_date')
            ->take(5)
            ->get();

        $activeBreedingCycles = ReproductionCycle::with(['sow.pen', 'boar'])
            ->whereIn('status', ReproductionCycle::activeStatuses())
            ->orderByDesc('service_date')
            ->take(5)
            ->get();

        $dueSoonCycles = ReproductionCycle::with(['sow.pen', 'boar'])
            ->whereIn('status', [
                ReproductionCycle::STATUS_PREGNANT,
                ReproductionCycle::STATUS_DUE_SOON,
            ])
            ->where('pregnancy_result', ReproductionCycle::PREGNANCY_RESULT_PREGNANT)
            ->whereNotNull('expected_farrow_date')
            ->whereNull('actual_farrow_date')
            ->orderBy('expected_farrow_date')
            ->get()
            ->filter(fn ($cycle) => $cycle->is_due_soon)
            ->take(5)
            ->values();

        $returnedToHeatCycles = ReproductionCycle::with(['sow.pen', 'boar'])
            ->where('status', ReproductionCycle::STATUS_RETURNED_TO_HEAT)
            ->orderByDesc('pregnancy_check_date')
            ->orderByDesc('service_date')
            ->take(5)
            ->get();

        $pendingPregnancyChecks = ReproductionCycle::with(['sow.pen', 'boar'])
            ->where('status', ReproductionCycle::STATUS_SERVICED)
            ->where('pregnancy_result', ReproductionCycle::PREGNANCY_RESULT_PENDING)
            ->orderByDesc('service_date')
            ->take(5)
            ->get();

        $upcomingFarrowings = $normalizeCycleStatuses($upcomingFarrowings);
        $activeBreedingCycles = $normalizeCycleStatuses($activeBreedingCycles);
        $dueSoonCycles = $normalizeCycleStatuses($dueSoonCycles);
        $returnedToHeatCycles = $normalizeCycleStatuses($returnedToHeatCycles);
        $pendingPregnancyChecks = $normalizeCycleStatuses($pendingPregnancyChecks);

        $staleWeightPigs = $pigs->filter(function ($pig) {
            if (!$pig->dashboard_latest_log_date) {
                return true;
            }

            return now()->diffInDays($pig->dashboard_latest_log_date) > 7;
        });

        $weightAlertRows = $staleWeightPigs->map(function ($pig) {
            $latest = $pig->dashboard_weight_logs->get(0);
            $previous = $pig->dashboard_weight_logs->get(1);

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
                'latest_weight' => $pig->dashboard_computed_weight,
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
            $growthGroups[$pig->dashboard_growth_status]->push($pig);
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
