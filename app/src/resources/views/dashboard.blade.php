@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')
@section('page_subtitle', 'Farm financials and operational overview.')

@section('top_actions')
    <a href="{{ route('settings.farm.edit') }}" class="btn">Farm Settings</a>
    <a href="{{ route('pigs.index') }}" class="btn">View Pigs</a>
    <a href="{{ route('pigs.create') }}" class="btn primary">+ Add Pig</a>
@endsection

@section('content')

@php
    $pigs = \App\Models\Pig::with([
        'sales',
        'mortalityLogs',
        'feedLogs',
        'medications',
        'vaccinations',
        'healthLogs',
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
                \Carbon\Carbon::parse($latestLog->log_date)
                    ->diffInDays(\Carbon\Carbon::parse($previousLog->log_date))
            );

            $dailyGain = $weightGain / $days;
        } elseif ($latestLog && $baselineWeight !== null) {
            $weightGain = (float) $latestLog->weight - $baselineWeight;

            $baselineDate = $pig->date_added ? \Carbon\Carbon::parse($pig->date_added) : null;
            $days = $baselineDate
                ? max(1, \Carbon\Carbon::parse($latestLog->log_date)->diffInDays($baselineDate))
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
    $netPosition = $totalAssetValue + $totalRevenue - $totalLossValue;

    $totalFeedCost = (float) $pigs->sum(fn ($pig) => (float) $pig->total_feed_cost);
    $totalMedicationCost = (float) $pigs->sum(fn ($pig) => (float) $pig->total_medication_cost);
    $totalVaccinationCost = (float) $pigs->sum(fn ($pig) => (float) $pig->total_vaccination_cost);
    $totalCareLiability = (float) $pigs->sum(fn ($pig) => (float) $pig->total_care_liability);
    $totalOperatingCost = (float) $pigs->sum(fn ($pig) => (float) $pig->total_operating_cost);

    $positiveGainPigs = $pigs->filter(fn ($pig) => $pig->feed_efficiency !== null);

    $totalFeedKgForEfficiency = (float) $positiveGainPigs->sum(fn ($pig) => (float) $pig->total_feed_kg);
    $totalGainForEfficiency = (float) $positiveGainPigs->sum(function ($pig) {
        return max(0, (float) $pig->dashboard_computed_weight - (float) $pig->latest_weight);
    });

    $farmFeedEfficiency = $totalFeedKgForEfficiency > 0 && $totalGainForEfficiency > 0
        ? $totalFeedKgForEfficiency / $totalGainForEfficiency
        : null;

    $recentSales = \App\Models\Sale::with('pig')->latest()->take(5)->get();
    $recentMortality = \App\Models\MortalityLog::with('pig')->latest()->take(5)->get();
    $recentHealthAlerts = \App\Models\HealthLog::with('pig')
        ->whereIn('purpose', ['sick', 'injury', 'recovered'])
        ->latest()
        ->take(5)
        ->get();

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
@endphp

@section('styles')
.dashboard-stack {
    display: grid;
    gap: 20px;
}

.dashboard-section {
    display: grid;
    gap: 16px;
}

.dashboard-section-title {
    font-size: 18px;
    font-weight: 800;
    letter-spacing: -0.02em;
    margin-bottom: 2px;
}

.dashboard-section-sub {
    color: var(--muted);
    font-size: 13px;
}

.dashboard-mini-grid {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 18px;
}

.dashboard-compact-card {
    background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    box-shadow: var(--shadow-sm);
    padding: 18px;
}

.dashboard-compact-card .stat-value {
    margin-bottom: 0;
}

.section-accent-green,
.section-accent-blue,
.section-accent-red,
.section-accent-orange {
    position: relative;
    padding-left: 14px;
}

.section-accent-green::before,
.section-accent-blue::before,
.section-accent-red::before,
.section-accent-orange::before {
    content: "";
    position: absolute;
    left: 0;
    top: 2px;
    bottom: 2px;
    width: 4px;
    border-radius: 999px;
}

.section-accent-green::before { background: #22c55e; }
.section-accent-blue::before { background: #3b82f6; }
.section-accent-red::before { background: #ef4444; }
.section-accent-orange::before { background: #f97316; }

.metric-card-highlight {
    background: linear-gradient(135deg, rgba(37,99,235,0.05), rgba(255,255,255,0.95));
}

.trend-up { font-weight: 700; color: #16a34a; }
.trend-down { font-weight: 700; color: #dc2626; }
.trend-flat { font-weight: 700; color: #64748b; }

@media (max-width: 1200px) {
    .dashboard-mini-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 640px) {
    .dashboard-mini-grid {
        grid-template-columns: 1fr;
    }
}
@endsection

<div class="dashboard-stack">

    <div class="dashboard-section">
        <div>
            <div class="dashboard-section-title">Financial Overview</div>
            <div class="dashboard-section-sub">High-level farm position using current active value, recorded revenue, and mortality-linked losses.</div>
        </div>

        <div class="grid stats">
            <div class="stat-card metric-card-highlight">
                <div class="stat-top">
                    <span class="label">Live Asset Value</span>
                    <span class="badge green">Active</span>
                </div>
                <div class="stat-value">₱ {{ number_format($totalAssetValue, 2) }}</div>
                <div class="stat-sub">Current value of pigs that are still active.</div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <span class="label">Total Revenue</span>
                    <span class="badge blue">Sales</span>
                </div>
                <div class="stat-value">₱ {{ number_format($totalRevenue, 2) }}</div>
                <div class="stat-sub">Accumulated income from recorded pig sales.</div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <span class="label">Loss Value</span>
                    <span class="badge red">Mortality</span>
                </div>
                <div class="stat-value">₱ {{ number_format($totalLossValue, 2) }}</div>
                <div class="stat-sub">Estimated value lost from pigs with mortality records.</div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <span class="label">Net Position</span>
                    <span class="badge orange">Summary</span>
                </div>
                <div class="stat-value">₱ {{ number_format($netPosition, 2) }}</div>
                <div class="stat-sub">Live assets + revenue − mortality loss.</div>
            </div>
        </div>
    </div>

    <div class="dashboard-section">
        <div>
            <div class="dashboard-section-title">Cost & Liability</div>
            <div class="dashboard-section-sub">Consolidated operating costs and care-related exposure across the herd.</div>
        </div>

        <div class="dashboard-mini-grid">
            <div class="dashboard-compact-card">
                <div class="stat-top">
                    <span class="label">Feed Cost</span>
                    <span class="badge orange">Cost</span>
                </div>
                <div class="stat-value">₱ {{ number_format($totalFeedCost, 2) }}</div>
            </div>

            <div class="dashboard-compact-card">
                <div class="stat-top">
                    <span class="label">Medication Cost</span>
                    <span class="badge red">Care</span>
                </div>
                <div class="stat-value">₱ {{ number_format($totalMedicationCost, 2) }}</div>
            </div>

            <div class="dashboard-compact-card">
                <div class="stat-top">
                    <span class="label">Vaccination Cost</span>
                    <span class="badge blue">Care</span>
                </div>
                <div class="stat-value">₱ {{ number_format($totalVaccinationCost, 2) }}</div>
            </div>

            <div class="dashboard-compact-card">
                <div class="stat-top">
                    <span class="label">Care Liability</span>
                    <span class="badge orange">Liability</span>
                </div>
                <div class="stat-value">₱ {{ number_format($totalCareLiability, 2) }}</div>
            </div>

            <div class="dashboard-compact-card">
                <div class="stat-top">
                    <span class="label">Operating Cost</span>
                    <span class="badge red">Total</span>
                </div>
                <div class="stat-value">₱ {{ number_format($totalOperatingCost, 2) }}</div>
            </div>
        </div>
    </div>

    <div class="dashboard-section">
        <div>
            <div class="dashboard-section-title">Herd Status</div>
            <div class="dashboard-section-sub">Quick view of total records and current herd distribution.</div>
        </div>

        <div class="grid stats">
            <div class="stat-card">
                <div class="stat-top">
                    <span class="label">Total Pigs</span>
                    <span class="badge blue">All</span>
                </div>
                <div class="stat-value">{{ $pigs->count() }}</div>
                <div class="stat-sub">All pig records in the system.</div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <span class="label">Active Pigs</span>
                    <span class="badge green">Live</span>
                </div>
                <div class="stat-value">{{ $livePigs->count() }}</div>
                <div class="stat-sub">Pigs without sale or mortality records.</div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <span class="label">Sold Pigs</span>
                    <span class="badge orange">Closed</span>
                </div>
                <div class="stat-value">{{ $soldPigs->count() }}</div>
                <div class="stat-sub">Pigs with at least one sale record.</div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <span class="label">Dead Pigs</span>
                    <span class="badge red">Loss</span>
                </div>
                <div class="stat-value">{{ $deadPigs->count() }}</div>
                <div class="stat-sub">Pigs with recorded mortality.</div>
            </div>
        </div>
    </div>

    <div class="dashboard-section">
        <div>
            <div class="dashboard-section-title">Growth Monitoring</div>
            <div class="dashboard-section-sub">Snapshot of herd growth behavior based on weight-update health logs.</div>
        </div>

        <div class="dashboard-mini-grid">
            <div class="dashboard-compact-card">
                <div class="stat-top">
                    <span class="label">Growing Well</span>
                    <span class="badge green">Good</span>
                </div>
                <div class="stat-value">{{ $growthSummary['good'] }}</div>
            </div>

            <div class="dashboard-compact-card">
                <div class="stat-top">
                    <span class="label">Declining</span>
                    <span class="badge red">Alert</span>
                </div>
                <div class="stat-value">{{ $growthSummary['declining'] }}</div>
            </div>

            <div class="dashboard-compact-card">
                <div class="stat-top">
                    <span class="label">Stagnant</span>
                    <span class="badge orange">Monitor</span>
                </div>
                <div class="stat-value">{{ $growthSummary['stagnant'] }}</div>
            </div>

            <div class="dashboard-compact-card">
                <div class="stat-top">
                    <span class="label">No Data</span>
                    <span class="badge blue">Unknown</span>
                </div>
                <div class="stat-value">{{ $growthSummary['no_data'] }}</div>
            </div>

            <div class="dashboard-compact-card">
                <div class="stat-top">
                    <span class="label">Farm Feed Efficiency</span>
                    <span class="badge green">Ratio</span>
                </div>
                <div class="stat-value">{{ $farmFeedEfficiency !== null ? number_format($farmFeedEfficiency, 2) : '—' }}</div>
            </div>
        </div>
    </div>

    <div class="grid overview-panels">
        <div class="panel-card">
            <div class="section-title section-accent-green">
                <div>
                    <h3>Best Cost Performers</h3>
                    <p>Pigs with positive gain and the lowest cost per kg gain.</p>
                </div>
                <a href="{{ route('pigs.index') }}" class="btn">View Pigs</a>
            </div>

            @if($bestPerformers->isEmpty())
                <div class="empty-state">No pigs have enough cost-and-gain data yet.</div>
            @else
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Ear Tag</th>
                                <th>Latest Weight</th>
                                <th>Operating Cost</th>
                                <th>Cost / kg Gain</th>
                                <th>Performance</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bestPerformers as $pig)
                                <tr>
                                    <td>{{ $pig->ear_tag }}</td>
                                    <td>{{ number_format((float) $pig->dashboard_computed_weight, 2) }} kg</td>
                                    <td>₱ {{ number_format((float) $pig->total_operating_cost, 2) }}</td>
                                    <td>₱ {{ number_format((float) $pig->cost_per_kg_gain, 2) }}</td>
                                    <td>
                                        <span class="badge {{ match($pig->performance_status) {
                                            'good' => 'green',
                                            'inefficient' => 'orange',
                                            'risk' => 'red',
                                            'monitor' => 'orange',
                                            default => 'blue',
                                        } }}">
                                            {{ match($pig->performance_status) {
                                                'good' => 'Efficient',
                                                'inefficient' => 'Inefficient',
                                                'risk' => 'Risk',
                                                'monitor' => 'Monitor',
                                                default => 'No Data',
                                            } }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('pigs.show', $pig->id) }}" class="btn">Go to Pig</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="panel-card">
            <div class="section-title section-accent-red">
                <div>
                    <h3>High Cost / Low Gain Risks</h3>
                    <p>Pigs that are inefficient or currently weight-negative.</p>
                </div>
                <a href="{{ route('pigs.index') }}" class="btn">View Pigs</a>
            </div>

            @if($riskPigs->isEmpty())
                <div class="empty-state">No pigs are currently flagged as high-cost or risk.</div>
            @else
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Ear Tag</th>
                                <th>Latest Weight</th>
                                <th>Operating Cost</th>
                                <th>Cost / kg Gain</th>
                                <th>Performance</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($riskPigs as $pig)
                                <tr>
                                    <td>{{ $pig->ear_tag }}</td>
                                    <td>{{ number_format((float) $pig->dashboard_computed_weight, 2) }} kg</td>
                                    <td>₱ {{ number_format((float) $pig->total_operating_cost, 2) }}</td>
                                    <td>{{ $pig->cost_per_kg_gain !== null ? '₱ ' . number_format((float) $pig->cost_per_kg_gain, 2) : '—' }}</td>
                                    <td>
                                        <span class="badge {{ $pig->performance_status === 'risk' ? 'red' : 'orange' }}">
                                            {{ $pig->performance_status === 'risk' ? 'Risk' : 'Inefficient' }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('pigs.show', $pig->id) }}" class="btn">Go to Pig</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="grid overview-panels">
        <div class="panel-card">
            <div class="section-title section-accent-green">
                <div>
                    <h3>Growing Well Pigs</h3>
                    <p>Pigs currently showing positive growth.</p>
                </div>
                <a href="{{ route('pigs.index') }}" class="btn">View Pigs</a>
            </div>

            @if($growthGroups['good']->isEmpty())
                <div class="empty-state">No pigs currently marked as growing well.</div>
            @else
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Ear Tag</th>
                                <th>Latest Weight</th>
                                <th>Weight Gain</th>
                                <th>Daily Gain</th>
                                <th>Feed Cost</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($growthGroups['good'] as $pig)
                                <tr>
                                    <td>{{ $pig->ear_tag }}</td>
                                    <td>{{ number_format((float) $pig->dashboard_computed_weight, 2) }} kg</td>
                                    <td>{{ $pig->dashboard_weight_gain !== null ? number_format((float) $pig->dashboard_weight_gain, 2) . ' kg' : '—' }}</td>
                                    <td>{{ $pig->dashboard_daily_gain !== null ? number_format((float) $pig->dashboard_daily_gain, 2) . ' kg/day' : '—' }}</td>
                                    <td>₱ {{ number_format((float) $pig->total_feed_cost, 2) }}</td>
                                    <td>
                                        <a href="{{ route('pigs.show', $pig->id) }}" class="btn">Go to Pig</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="panel-card">
            <div class="section-title section-accent-red">
                <div>
                    <h3>Declining Pigs</h3>
                    <p>Pigs currently showing weight loss.</p>
                </div>
                <a href="{{ route('pigs.index') }}" class="btn">View Pigs</a>
            </div>

            @if($growthGroups['declining']->isEmpty())
                <div class="empty-state">No pigs currently marked as declining.</div>
            @else
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Ear Tag</th>
                                <th>Latest Weight</th>
                                <th>Weight Gain</th>
                                <th>Daily Gain</th>
                                <th>Feed Cost</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($growthGroups['declining'] as $pig)
                                <tr>
                                    <td>{{ $pig->ear_tag }}</td>
                                    <td>{{ number_format((float) $pig->dashboard_computed_weight, 2) }} kg</td>
                                    <td>{{ $pig->dashboard_weight_gain !== null ? number_format((float) $pig->dashboard_weight_gain, 2) . ' kg' : '—' }}</td>
                                    <td>{{ $pig->dashboard_daily_gain !== null ? number_format((float) $pig->dashboard_daily_gain, 2) . ' kg/day' : '—' }}</td>
                                    <td>₱ {{ number_format((float) $pig->total_feed_cost, 2) }}</td>
                                    <td>
                                        <a href="{{ route('pigs.show', $pig->id) }}" class="btn">Go to Pig</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="grid overview-panels">
        <div class="panel-card">
            <div class="section-title section-accent-orange">
                <div>
                    <h3>Stagnant Pigs</h3>
                    <p>Pigs with no recent weight change.</p>
                </div>
                <a href="{{ route('pigs.index') }}" class="btn">View Pigs</a>
            </div>

            @if($growthGroups['stagnant']->isEmpty())
                <div class="empty-state">No pigs currently marked as stagnant.</div>
            @else
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Ear Tag</th>
                                <th>Latest Weight</th>
                                <th>Weight Gain</th>
                                <th>Daily Gain</th>
                                <th>Feed Cost</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($growthGroups['stagnant'] as $pig)
                                <tr>
                                    <td>{{ $pig->ear_tag }}</td>
                                    <td>{{ number_format((float) $pig->dashboard_computed_weight, 2) }} kg</td>
                                    <td>{{ $pig->dashboard_weight_gain !== null ? number_format((float) $pig->dashboard_weight_gain, 2) . ' kg' : '—' }}</td>
                                    <td>{{ $pig->dashboard_daily_gain !== null ? number_format((float) $pig->dashboard_daily_gain, 2) . ' kg/day' : '—' }}</td>
                                    <td>₱ {{ number_format((float) $pig->total_feed_cost, 2) }}</td>
                                    <td>
                                        <a href="{{ route('pigs.show', $pig->id) }}" class="btn">Go to Pig</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="panel-card">
            <div class="section-title section-accent-blue">
                <div>
                    <h3>No Data Pigs</h3>
                    <p>Pigs with insufficient weight logs for growth analysis.</p>
                </div>
                <a href="{{ route('pigs.index') }}" class="btn">View Pigs</a>
            </div>

            @if($growthGroups['no_data']->isEmpty())
                <div class="empty-state">All pigs already have enough weight data.</div>
            @else
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Ear Tag</th>
                                <th>Latest Weight</th>
                                <th>Growth Status</th>
                                <th>Feed Cost</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($growthGroups['no_data'] as $pig)
                                <tr>
                                    <td>{{ $pig->ear_tag }}</td>
                                    <td>{{ number_format((float) $pig->dashboard_computed_weight, 2) }} kg</td>
                                    <td><span class="badge blue">No Data</span></td>
                                    <td>₱ {{ number_format((float) $pig->total_feed_cost, 2) }}</td>
                                    <td>
                                        <a href="{{ route('pigs.show', $pig->id) }}" class="btn">Go to Pig</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="grid overview-panels">
        <div class="panel-card">
            <div class="section-title section-accent-blue">
                <div>
                    <h3>Recent Sales</h3>
                    <p>Latest sale activity in the farm.</p>
                </div>
            </div>

            @if($recentSales->isEmpty())
                <div class="empty-state">No sales recorded yet.</div>
            @else
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Ear Tag</th>
                                <th>Buyer</th>
                                <th>Price</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentSales as $sale)
                                <tr>
                                    <td>{{ $sale->sold_date }}</td>
                                    <td>{{ $sale->pig->ear_tag ?? '—' }}</td>
                                    <td>{{ $sale->buyer ?: '—' }}</td>
                                    <td>₱ {{ number_format((float) $sale->price, 2) }}</td>
                                    <td>
                                        @if($sale->pig)
                                            <a href="{{ route('pigs.show', $sale->pig->id) }}" class="btn">Go to Pig</a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="panel-card">
            <div class="section-title section-accent-red">
                <div>
                    <h3>Recent Mortality</h3>
                    <p>Latest loss events recorded.</p>
                </div>
            </div>

            @if($recentMortality->isEmpty())
                <div class="empty-state">No mortality records yet.</div>
            @else
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Ear Tag</th>
                                <th>Cause</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentMortality as $mortality)
                                <tr>
                                    <td>{{ $mortality->death_date }}</td>
                                    <td>{{ $mortality->pig->ear_tag ?? '—' }}</td>
                                    <td>{{ $mortality->cause }}</td>
                                    <td>
                                        @if($mortality->pig)
                                            <a href="{{ route('pigs.show', $mortality->pig->id) }}" class="btn">Go to Pig</a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="panel-card">
        <div class="section-title section-accent-orange">
            <div>
                <h3>Health Alerts</h3>
                <p>Recent sickness, injury, and recovery-related logs that may need attention.</p>
            </div>
            <a href="{{ route('pigs.index') }}" class="btn">View Pigs</a>
        </div>

        @if($recentHealthAlerts->isEmpty())
            <div class="empty-state">No recent health alerts.</div>
        @else
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Ear Tag</th>
                            <th>Purpose</th>
                            <th>Condition</th>
                            <th>Notes</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentHealthAlerts as $log)
                            @php
                                $healthBadgeClass = match($log->purpose) {
                                    'sick' => 'red',
                                    'recovered' => 'green',
                                    'injury' => 'orange',
                                    default => 'blue',
                                };
                            @endphp
                            <tr>
                                <td>{{ $log->log_date }}</td>
                                <td>{{ $log->pig->ear_tag ?? '—' }}</td>
                                <td>
                                    <span class="badge {{ $healthBadgeClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $log->purpose)) }}
                                    </span>
                                </td>
                                <td>{{ $log->condition }}</td>
                                <td>{{ $log->notes ?: '—' }}</td>
                                <td>
                                    @if($log->pig)
                                        <a href="{{ route('pigs.show', $log->pig->id) }}" class="btn">Go to Pig</a>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="panel-card">
        <div class="section-title section-accent-green">
            <div>
                <h3>Weight Monitoring Alerts</h3>
                <p>Pigs with no recent weight updates in the last 7 or more days.</p>
            </div>
            <a href="{{ route('pigs.index') }}" class="btn">View Pigs</a>
        </div>

        @if($weightAlertRows->isEmpty())
            <div class="empty-state">All pigs have recent weight updates.</div>
        @else
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Ear Tag</th>
                            <th>Last Weight</th>
                            <th>Trend</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($weightAlertRows as $row)
                            <tr>
                                <td>{{ $row['pig']->ear_tag }}</td>
                                <td>{{ number_format((float) $row['latest_weight'], 2) }} kg</td>
                                <td>
                                    @if($row['trend_symbol'] === '↑')
                                        <span class="trend-up">↑ Increasing</span>
                                    @elseif($row['trend_symbol'] === '↓')
                                        <span class="trend-down">↓ Dropping</span>
                                    @else
                                        <span class="trend-flat">{{ $row['trend_symbol'] }} {{ $row['trend_label'] }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('pigs.show', $row['pig']->id) }}" class="btn">Go to Pig</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection
