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

.dashboard-quick-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.dashboard-toggle {
    border: 1px solid var(--line);
    border-radius: 18px;
    background: #fff;
    box-shadow: var(--shadow-soft);
    overflow: hidden;
}

.dashboard-toggle summary {
    list-style: none;
    cursor: pointer;
    padding: 16px 18px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 14px;
    font-weight: 800;
    color: var(--text);
}

.dashboard-toggle summary::-webkit-details-marker {
    display: none;
}

.dashboard-toggle summary span {
    display: block;
}

.dashboard-toggle summary small {
    display: block;
    color: var(--muted);
    font-size: 12px;
    font-weight: 500;
    margin-top: 3px;
}

.dashboard-toggle summary::after {
    content: "View";
    flex: 0 0 auto;
    border: 1px solid var(--line);
    border-radius: 999px;
    padding: 7px 12px;
    font-size: 12px;
    color: var(--primary);
    background: #f8fbff;
}

.dashboard-toggle[open] summary::after {
    content: "Hide";
}

.dashboard-detail-list {
    display: grid;
    gap: 0;
    border-top: 1px solid var(--line);
}

.dashboard-detail-row {
    display: grid;
    grid-template-columns: minmax(150px, 0.35fr) minmax(120px, 0.25fr) minmax(0, 1fr);
    gap: 14px;
    align-items: center;
    padding: 14px 18px;
    border-bottom: 1px solid var(--line);
}

.dashboard-detail-row:last-child {
    border-bottom: 0;
}

.dashboard-detail-label {
    color: var(--muted);
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.dashboard-detail-value {
    color: var(--text);
    font-size: 18px;
    font-weight: 900;
}

.dashboard-detail-note {
    color: var(--muted);
    font-size: 13px;
    line-height: 1.4;
}

.dashboard-record-list {
    display: grid;
    gap: 10px;
    padding: 14px 18px 18px;
    border-top: 1px solid var(--line);
}

.dashboard-record-row {
    display: grid;
    grid-template-columns: minmax(120px, 0.3fr) minmax(0, 1fr) auto;
    gap: 12px;
    align-items: center;
    border: 1px solid var(--line);
    border-radius: 14px;
    background: var(--panel-2);
    padding: 12px;
}

.dashboard-record-row strong {
    display: block;
    color: var(--text);
}

.dashboard-record-row span {
    display: block;
    color: var(--muted);
    font-size: 13px;
    margin-top: 2px;
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
    .dashboard-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 700px) {
    .dashboard-stack {
        gap: 18px;
    }

    .dashboard-section {
        gap: 12px;
    }

    .dashboard-section-title {
        font-size: 17px;
        line-height: 1.2;
    }

    .dashboard-section-sub {
        font-size: 12px;
        line-height: 1.4;
    }

    .dashboard-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .stat-card {
        padding: 16px;
    }

    .stat-top {
        gap: 8px;
    }

    .stat-value {
        font-size: 26px;
        line-height: 1.1;
        overflow-wrap: anywhere;
    }

    .stat-sub {
        font-size: 12px;
        line-height: 1.4;
    }

    .dashboard-quick-actions {
        display: grid;
        grid-template-columns: 1fr;
        gap: 8px;
    }

    .dashboard-quick-actions .btn,
    .dashboard-record-row .btn {
        width: 100%;
        justify-content: center;
    }

    .dashboard-toggle summary {
        align-items: flex-start;
        padding: 14px;
    }

    .dashboard-detail-row,
    .dashboard-record-row {
        grid-template-columns: 1fr;
        gap: 6px;
        padding: 14px;
    }

    .dashboard-detail-value {
        font-size: 20px;
    }

    .dashboard-note {
        font-size: 12px;
        line-height: 1.45;
    }
}

@media (max-width: 420px) {
    .stat-value {
        font-size: 24px;
    }

    .badge {
        font-size: 11px;
    }
}
@endsection

@section('content')
<div class="dashboard-stack">
    <div class="dashboard-section">
        <div>
            <div class="dashboard-section-title">Daily Farm Status</div>
            <div class="dashboard-section-sub">Most important numbers for daily checking.</div>
        </div>

        <div class="dashboard-grid">
            <div class="stat-card metric-card-highlight">
                <div class="stat-top">
                    <span class="label">Active Pigs</span>
                    <span class="badge green">Live</span>
                </div>
                <div class="stat-value">{{ $livePigs->count() }}</div>
                <div class="stat-sub">Pigs currently in the farm.</div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <span class="label">Mortality</span>
                    <span class="badge red">Loss</span>
                </div>
                <div class="stat-value">{{ $deadPigs->count() }}</div>
                <div class="stat-sub">Pigs with mortality records.</div>
            </div>

            <div class="stat-card metric-card-highlight">
                <div class="stat-top">
                    <span class="label">Net Position</span>
                    <span class="badge blue">Position</span>
                </div>
                <div class="stat-value">₱ {{ number_format($netPosition, 2) }}</div>
                <div class="stat-sub">Active value + sale income - mortality loss - recorded costs.</div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <span class="label">Live Asset Value</span>
                    <span class="badge green">Value</span>
                </div>
                <div class="stat-value">₱ {{ number_format($totalAssetValue, 2) }}</div>
                <div class="stat-sub">Active pigs counted in farm value.</div>
            </div>
        </div>
    </div>

    <details class="dashboard-toggle">
        <summary>
            <span>
                View Basic Counts
                <small>Show total records, sold pigs, and breeding cost.</small>
            </span>
        </summary>

        <div class="dashboard-detail-list">
            <div class="dashboard-detail-row">
                <div class="dashboard-detail-label">Total Pigs</div>
                <div class="dashboard-detail-value">{{ $pigs->count() }}</div>
                <div class="dashboard-detail-note">All non-archived pig records currently counted by the dashboard.</div>
            </div>

            <div class="dashboard-detail-row">
                <div class="dashboard-detail-label">Sold Pigs</div>
                <div class="dashboard-detail-value">{{ $soldPigs->count() }}</div>
                <div class="dashboard-detail-note">Pigs with completed sale records.</div>
            </div>

            <div class="dashboard-detail-row">
                <div class="dashboard-detail-label">Breeding Cost</div>
                <div class="dashboard-detail-value">₱ {{ number_format($totalBreedingCost, 2) }}</div>
                <div class="dashboard-detail-note">Recorded breeding-related cost.</div>
            </div>

            {{--
                Future advanced accounting row.
                Kept for future client update if detailed operating-cost visibility is requested again.

                <div class="dashboard-detail-row">
                    <div class="dashboard-detail-label">Operating Cost</div>
                    <div class="dashboard-detail-value">₱ {{ number_format($totalOperatingCost, 2) }}</div>
                    <div class="dashboard-detail-note">Recorded farm operating cost.</div>
                </div>
            --}}
        </div>
    </details>

    <details class="dashboard-toggle">
        <summary>
            <span>
                View Financial Details
                <small>Show sale income, mortality loss, and recorded costs.</small>
            </span>
        </summary>

        <div class="dashboard-detail-list">
            <div class="dashboard-detail-row">
                <div class="dashboard-detail-label">Sale Revenue</div>
                <div class="dashboard-detail-value">₱ {{ number_format($totalRevenue, 2) }}</div>
                <div class="dashboard-detail-note">Total recorded income from sold pigs.</div>
            </div>

            <div class="dashboard-detail-row">
                <div class="dashboard-detail-label">Mortality Loss</div>
                <div class="dashboard-detail-value">₱ {{ number_format($totalLossValue, 2) }}</div>
                <div class="dashboard-detail-note">Frozen value lost from dead pigs.</div>
            </div>

            <div class="dashboard-detail-row">
                <div class="dashboard-detail-label">Recorded Costs</div>
                <div class="dashboard-detail-value">₱ {{ number_format($totalOperatingCost, 2) }}</div>
                <div class="dashboard-detail-note">Costs saved from breeding and medication program records.</div>
            </div>

            <div class="dashboard-detail-row">
                <div class="dashboard-detail-label">Net Position</div>
                <div class="dashboard-detail-value">₱ {{ number_format($netPosition, 2) }}</div>
                <div class="dashboard-detail-note">Active value + sale income - mortality loss - recorded costs.</div>
            </div>

            {{--
                Future advanced accounting row.
                Kept for future client update if care/input liability visibility is requested again.

                <div class="dashboard-detail-row">
                    <div class="dashboard-detail-label">Care Liability</div>
                    <div class="dashboard-detail-value">₱ {{ number_format($totalCareLiability, 2) }}</div>
                    <div class="dashboard-detail-note">Recorded care/input cost attached to pigs.</div>
                </div>
            --}}
        </div>
    </details>

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

    <details class="dashboard-toggle">
        <summary>
            <span>
                View Breeding Follow-up
                <small>Show upcoming farrowing, active breeding, and pregnancy checks.</small>
            </span>
        </summary>

        <div class="dashboard-detail-list">
            <div class="dashboard-detail-row">
                <div class="dashboard-detail-label">Upcoming Farrowing</div>
                <div class="dashboard-detail-value">{{ $upcomingFarrowings->count() }}</div>
                <div class="dashboard-detail-note">Expected within the alert window.</div>
            </div>

            <div class="dashboard-detail-row">
                <div class="dashboard-detail-label">Active Breeding</div>
                <div class="dashboard-detail-value">{{ $activeBreedingCycles->count() }}</div>
                <div class="dashboard-detail-note">Breeding records still in progress.</div>
            </div>

            <div class="dashboard-detail-row">
                <div class="dashboard-detail-label">Pregnancy Checks</div>
                <div class="dashboard-detail-value">{{ $pendingPregnancyChecks->count() }}</div>
                <div class="dashboard-detail-note">Sows waiting for pregnancy confirmation.</div>
            </div>
        </div>

        <div class="dashboard-record-list">
            @if($upcomingFarrowings->isEmpty() && $pendingPregnancyChecks->isEmpty())
                <div class="empty-state">No breeding follow-up alerts right now.</div>
            @else
                @foreach($upcomingFarrowings as $cycle)
                    <div class="dashboard-record-row">
                        <div>
                            <strong>{{ $cycle->sow?->ear_tag ?? '—' }}</strong>
                            <span>Sow</span>
                        </div>

                        <div>
                            <strong>{{ $cycle->expected_farrow_date?->format('Y-m-d') ?? 'No date' }}</strong>
                            <span>
                                Upcoming farrowing
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

                @foreach($pendingPregnancyChecks as $cycle)
                    <div class="dashboard-record-row">
                        <div>
                            <strong>{{ $cycle->sow?->ear_tag ?? '—' }}</strong>
                            <span>Sow</span>
                        </div>

                        <div>
                            <strong>{{ $cycle->service_date?->format('Y-m-d') ?? 'No service date' }}</strong>
                            <span>Pending pregnancy check</span>
                        </div>

                        @if($cycle->sow)
                            <a href="{{ route('pigs.show', $cycle->sow->id) }}" class="btn">Open Sow</a>
                        @else
                            <span class="client-muted">—</span>
                        @endif
                    </div>
                @endforeach
            @endif
        </div>
    </details>

    <div class="dashboard-note">
        Dashboard is intentionally simplified for client use. Detailed records remain available inside Pig Profile, Breeding Records, Farm Settings, and Reports.
    </div>
</div>
@endsection
