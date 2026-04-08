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
        ->whereIn('purpose', ['sick', 'injury'])
        ->latest()
        ->take(5)
        ->get();
@endphp

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

<div class="grid overview-panels">
    <div class="panel-card">
        <div class="section-title">
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
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentSales as $sale)
                            <tr>
                                <td>{{ $sale->sold_date }}</td>
                                <td>{{ $sale->pig->ear_tag ?? '—' }}</td>
                                <td>{{ $sale->buyer ?: '—' }}</td>
                                <td>₱ {{ number_format((float) $sale->price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="panel-card">
        <div class="section-title">
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
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentMortality as $mortality)
                            <tr>
                                <td>{{ $mortality->death_date }}</td>
                                <td>{{ $mortality->pig->ear_tag ?? '—' }}</td>
                                <td>{{ $mortality->cause }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<div class="panel-card">
    <div class="section-title">
        <div>
            <h3>Health Alerts</h3>
            <p>Recent sickness and injury-related logs that may need attention.</p>
        </div>
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
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentHealthAlerts as $log)
                        <tr>
                            <td>{{ $log->log_date }}</td>
                            <td>{{ $log->pig->ear_tag ?? '—' }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $log->purpose)) }}</td>
                            <td>{{ $log->condition }}</td>
                            <td>{{ $log->notes ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@endsection
