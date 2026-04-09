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
    $pigs = \App\Models\Pig::with(['sales', 'mortalityLogs'])->get();

    $livePigs = $pigs->filter(fn ($pig) => $pig->sales->isEmpty() && $pig->mortalityLogs->isEmpty());
    $soldPigs = $pigs->filter(fn ($pig) => $pig->sales->isNotEmpty());
    $deadPigs = $pigs->filter(fn ($pig) => $pig->mortalityLogs->isNotEmpty());

    $totalAssetValue = (float) $livePigs->sum('asset_value');
    $totalRevenue = (float) $soldPigs->flatMap->sales->sum('price');
    $totalLossValue = (float) $deadPigs->sum('asset_value');
    $netPosition = $totalAssetValue + $totalRevenue - $totalLossValue;

    $recentSales = \App\Models\Sale::with('pig')->latest()->take(5)->get();
    $recentMortality = \App\Models\MortalityLog::with('pig')->latest()->take(5)->get();

    $recentHealthAlerts = \App\Models\HealthLog::with('pig')
        ->whereIn('purpose', ['sick', 'injury', 'recovered'])
        ->latest()
        ->take(5)
        ->get();

    $staleWeightPigs = \App\Models\Pig::all()->filter(function ($pig) {
        $latestWeightLog = $pig->healthLogs()
            ->where('purpose', 'weight_update')
            ->orderByDesc('log_date')
            ->orderByDesc('id')
            ->first();

        if (!$latestWeightLog) {
            return true;
        }

        return now()->diffInDays($latestWeightLog->log_date) > 7;
    });

    $weightAlertRows = $staleWeightPigs->map(function ($pig) {
        $weightLogs = $pig->healthLogs()
            ->where('purpose', 'weight_update')
            ->whereNotNull('weight')
            ->orderByDesc('log_date')
            ->orderByDesc('id')
            ->take(2)
            ->get()
            ->values();

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
@endphp

<style>
.section-header-green { border-left: 5px solid #22c55e; padding-left: 10px; }
.section-header-blue { border-left: 5px solid #3b82f6; padding-left: 10px; }
.section-header-red { border-left: 5px solid #ef4444; padding-left: 10px; }
.section-header-orange { border-left: 5px solid #f97316; padding-left: 10px; }
.trend-up { font-weight: 700; color: #16a34a; }
.trend-down { font-weight: 700; color: #dc2626; }
.trend-flat { font-weight: 700; color: #6b7280; }
.panel-actions-inline { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
</style>

<div class="grid stats">
    <div class="stat-card">
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

<div class="grid stats" style="margin-top:20px;">
    <div class="stat-card">
        <div class="stat-top">
            <span class="label">Growing Well</span>
            <span class="badge green">Good</span>
        </div>
        <div class="stat-value">{{ $growthSummary['good'] }}</div>
        <div class="stat-sub">Pigs gaining weight.</div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <span class="label">Declining</span>
            <span class="badge red">Alert</span>
        </div>
        <div class="stat-value">{{ $growthSummary['declining'] }}</div>
        <div class="stat-sub">Pigs losing weight.</div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <span class="label">Stagnant</span>
            <span class="badge orange">Monitor</span>
        </div>
        <div class="stat-value">{{ $growthSummary['stagnant'] }}</div>
        <div class="stat-sub">No weight change.</div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <span class="label">No Data</span>
            <span class="badge blue">Unknown</span>
        </div>
        <div class="stat-value">{{ $growthSummary['no_data'] }}</div>
        <div class="stat-sub">No weight logs yet.</div>
    </div>
</div>

<div class="grid overview-panels">
    <div class="panel-card">
        <div class="section-title section-header-green">
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
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($growthGroups['good'] as $pig)
                            <tr>
                                <td>{{ $pig->ear_tag }}</td>
                                <td>{{ number_format((float) $pig->computed_weight, 2) }} kg</td>
                                <td>{{ $pig->weight_gain !== null ? number_format((float) $pig->weight_gain, 2) . ' kg' : '—' }}</td>
                                <td>{{ $pig->daily_gain !== null ? number_format((float) $pig->daily_gain, 2) . ' kg/day' : '—' }}</td>
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
        <div class="section-title section-header-red">
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
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($growthGroups['declining'] as $pig)
                            <tr>
                                <td>{{ $pig->ear_tag }}</td>
                                <td>{{ number_format((float) $pig->computed_weight, 2) }} kg</td>
                                <td>{{ $pig->weight_gain !== null ? number_format((float) $pig->weight_gain, 2) . ' kg' : '—' }}</td>
                                <td>{{ $pig->daily_gain !== null ? number_format((float) $pig->daily_gain, 2) . ' kg/day' : '—' }}</td>
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

<div class="grid overview-panels" style="margin-top:20px;">
    <div class="panel-card">
        <div class="section-title section-header-orange">
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
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($growthGroups['stagnant'] as $pig)
                            <tr>
                                <td>{{ $pig->ear_tag }}</td>
                                <td>{{ number_format((float) $pig->computed_weight, 2) }} kg</td>
                                <td>{{ $pig->weight_gain !== null ? number_format((float) $pig->weight_gain, 2) . ' kg' : '—' }}</td>
                                <td>{{ $pig->daily_gain !== null ? number_format((float) $pig->daily_gain, 2) . ' kg/day' : '—' }}</td>
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
        <div class="section-title section-header-blue">
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
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($growthGroups['no_data'] as $pig)
                            <tr>
                                <td>{{ $pig->ear_tag }}</td>
                                <td>{{ number_format((float) $pig->computed_weight, 2) }} kg</td>
                                <td><span class="badge blue">No Data</span></td>
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

<div class="grid overview-panels" style="margin-top:20px;">
    <div class="panel-card">
        <div class="section-title section-header-blue">
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
        <div class="section-title section-header-red">
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
    <div class="section-title section-header-orange">
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

<div class="panel-card" style="margin-top:20px;">
    <div class="section-title section-header-green">
        <div>
            <h3>Weight Monitoring Alerts</h3>
            <p>Pigs with no recent weight updates (7+ days).</p>
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

@endsection
