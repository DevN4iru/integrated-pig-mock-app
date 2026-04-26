@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')
@section('page_subtitle', 'Simple farm overview for daily handling.')

@section('top_actions')
    <a href="{{ route('pigs.index') }}" class="btn">View Pigs</a>
    <a href="{{ route('reproduction-cycles.index') }}" class="btn">Breeding Records</a>
    <a href="{{ route('settings.farm.edit') }}" class="btn">Farm Settings</a>
    <a href="{{ route('pigs.create') }}" class="btn primary">+ Add Pig</a>
@endsection

@section('styles')
.dashboard-stack {
    display: grid;
    gap: 20px;
}

.dashboard-section {
    display: grid;
    gap: 14px;
}

.dashboard-section-title {
    font-size: 18px;
    font-weight: 800;
    letter-spacing: -0.02em;
    margin-bottom: 3px;
}

.dashboard-section-sub {
    color: var(--muted);
    font-size: 13px;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
}

.dashboard-grid-three {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
}

.dashboard-list {
    display: grid;
    gap: 10px;
}

.dashboard-row {
    display: grid;
    grid-template-columns: minmax(120px, 0.45fr) minmax(0, 1fr) auto;
    gap: 12px;
    align-items: center;
    border: 1px solid var(--line);
    border-radius: 14px;
    background: #fff;
    padding: 12px;
}

.dashboard-row strong {
    display: block;
    color: var(--text);
}

.dashboard-row span {
    display: block;
    color: var(--muted);
    font-size: 13px;
    margin-top: 2px;
}

.dashboard-quick-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.dashboard-note {
    border: 1px solid var(--line);
    border-radius: 14px;
    background: var(--panel-2);
    padding: 14px;
    color: var(--muted);
    font-size: 13px;
}

@media (max-width: 1100px) {
    .dashboard-grid,
    .dashboard-grid-three {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 700px) {
    .dashboard-grid,
    .dashboard-grid-three,
    .dashboard-row {
        grid-template-columns: 1fr;
    }
}
@endsection

@section('content')
<div class="dashboard-stack">
    <div class="dashboard-section">
        <div>
            <div class="dashboard-section-title">Farm Summary</div>
            <div class="dashboard-section-sub">Only the core numbers needed for daily farm decisions.</div>
        </div>

        <div class="dashboard-grid">
            <div class="stat-card metric-card-highlight">
                <div class="stat-top">
                    <span class="label">Live Asset Value</span>
                    <span class="badge green">Active</span>
                </div>
                <div class="stat-value">₱ {{ number_format($totalAssetValue, 2) }}</div>
                <div class="stat-sub">Active pigs counted in farm value.</div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <span class="label">Total Pigs</span>
                    <span class="badge blue">Records</span>
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
                <div class="stat-sub">Pigs currently in the farm.</div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <span class="label">Net Position</span>
                    <span class="badge orange">Summary</span>
                </div>
                <div class="stat-value">₱ {{ number_format($netPosition, 2) }}</div>
                <div class="stat-sub">Farm value after recorded costs and losses.</div>
            </div>
        </div>
    </div>

    <div class="dashboard-section">
        <div>
            <div class="dashboard-section-title">Basic Counts</div>
            <div class="dashboard-section-sub">Simple record status for quick checking.</div>
        </div>

        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="stat-top">
                    <span class="label">Sold Pigs</span>
                    <span class="badge orange">Closed</span>
                </div>
                <div class="stat-value">{{ $soldPigs->count() }}</div>
                <div class="stat-sub">Pigs with sale records.</div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <span class="label">Mortality</span>
                    <span class="badge red">Loss</span>
                </div>
                <div class="stat-value">{{ $deadPigs->count() }}</div>
                <div class="stat-sub">Pigs with mortality records.</div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <span class="label">Breeding Cost</span>
                    <span class="badge blue">Breeding</span>
                </div>
                <div class="stat-value">₱ {{ number_format($totalBreedingCost, 2) }}</div>
                <div class="stat-sub">Recorded breeding-related cost.</div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <span class="label">Operating Cost</span>
                    <span class="badge orange">Total</span>
                </div>
                <div class="stat-value">₱ {{ number_format($totalOperatingCost, 2) }}</div>
                <div class="stat-sub">Recorded farm operating cost.</div>
            </div>
        </div>
    </div>

    <div class="dashboard-section">
        <div>
            <div class="dashboard-section-title">Quick Actions</div>
            <div class="dashboard-section-sub">Common tasks for the handler.</div>
        </div>

        <div class="panel-card">
            <div class="dashboard-quick-actions">
                <a href="{{ route('pigs.create') }}" class="btn primary">Add Pig</a>
                <a href="{{ route('pigs.index') }}" class="btn">Open Pig List</a>
                <a href="{{ route('reproduction-cycles.index') }}" class="btn">Open Breeding Records</a>
                <a href="{{ route('settings.farm.edit') }}" class="btn">Download / Email Summary</a>
            </div>
        </div>
    </div>

    <div class="dashboard-section">
        <div>
            <div class="dashboard-section-title">Breeding Follow-up</div>
            <div class="dashboard-section-sub">Only the breeding items that need attention soon.</div>
        </div>

        <div class="dashboard-grid-three">
            <div class="stat-card">
                <div class="stat-top">
                    <span class="label">Upcoming Farrowing</span>
                    <span class="badge green">Prepare</span>
                </div>
                <div class="stat-value">{{ $upcomingFarrowings->count() }}</div>
                <div class="stat-sub">Expected within the alert window.</div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <span class="label">Active Breeding</span>
                    <span class="badge blue">Open</span>
                </div>
                <div class="stat-value">{{ $activeBreedingCycles->count() }}</div>
                <div class="stat-sub">Breeding records still in progress.</div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <span class="label">Pregnancy Checks</span>
                    <span class="badge orange">Pending</span>
                </div>
                <div class="stat-value">{{ $pendingPregnancyChecks->count() }}</div>
                <div class="stat-sub">Sows waiting for pregnancy confirmation.</div>
            </div>
        </div>
    </div>

    <div class="dashboard-section">
        <div>
            <div class="dashboard-section-title">Upcoming Farrowing</div>
            <div class="dashboard-section-sub">Sows that may need preparation soon.</div>
        </div>

        <div class="panel-card">
            @if($upcomingFarrowings->isEmpty())
                <div class="empty-state">No upcoming farrowing alerts right now.</div>
            @else
                <div class="dashboard-list">
                    @foreach($upcomingFarrowings as $cycle)
                        <div class="dashboard-row">
                            <div>
                                <strong>{{ $cycle->sow?->ear_tag ?? '—' }}</strong>
                                <span>Sow</span>
                            </div>

                            <div>
                                <strong>{{ $cycle->expected_farrow_date?->format('Y-m-d') ?? 'No date' }}</strong>
                                <span>
                                    {{ $cycle->status_label ?? 'Breeding record' }}
                                    @if($cycle->recommended_pen_type)
                                        · Recommended pen: {{ $cycle->recommended_pen_type }}
                                    @endif
                                </span>
                            </div>

                            @if($cycle->sow)
                                <a href="{{ route('pigs.show', $cycle->sow->id) }}" class="btn">Open Sow</a>
                            @else
                                <span class="client-muted">—</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="dashboard-section">
        <div>
            <div class="dashboard-section-title">Pending Pregnancy Checks</div>
            <div class="dashboard-section-sub">Recently serviced sows waiting for confirmation.</div>
        </div>

        <div class="panel-card">
            @if($pendingPregnancyChecks->isEmpty())
                <div class="empty-state">No pending pregnancy checks right now.</div>
            @else
                <div class="dashboard-list">
                    @foreach($pendingPregnancyChecks as $cycle)
                        <div class="dashboard-row">
                            <div>
                                <strong>{{ $cycle->sow?->ear_tag ?? '—' }}</strong>
                                <span>Sow</span>
                            </div>

                            <div>
                                <strong>{{ $cycle->service_date?->format('Y-m-d') ?? 'No service date' }}</strong>
                                <span>{{ $cycle->status_label ?? 'Pending check' }}</span>
                            </div>

                            @if($cycle->sow)
                                <a href="{{ route('pigs.show', $cycle->sow->id) }}" class="btn">Open Sow</a>
                            @else
                                <span class="client-muted">—</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="dashboard-note">
        Dashboard is intentionally simplified for client use. Detailed records remain available inside Pig Profile, Breeding Records, Farm Settings, and Reports.
    </div>
</div>
@endsection
