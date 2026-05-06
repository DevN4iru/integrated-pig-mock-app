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
/* PIGSTEP DASHBOARD CARD COLOR RESTORE START */
.stat-card-live {
    border-color: #93c5fd !important;
    background: linear-gradient(180deg, #eff6ff 0%, #ffffff 72%) !important;
    box-shadow: 0 0 0 1px rgba(59, 130, 246, 0.14), 0 18px 36px rgba(59, 130, 246, 0.10) !important;
}

.stat-card-profit {
    border-color: #86efac !important;
    background: linear-gradient(180deg, #f0fdf4 0%, #ffffff 72%) !important;
    box-shadow: 0 0 0 1px rgba(34, 197, 94, 0.14), 0 18px 36px rgba(34, 197, 94, 0.10) !important;
}

.stat-card-loss {
    border-color: #fca5a5 !important;
    background: linear-gradient(180deg, #fff1f2 0%, #ffffff 72%) !important;
    box-shadow: 0 0 0 1px rgba(239, 68, 68, 0.14), 0 18px 36px rgba(239, 68, 68, 0.10) !important;
}

.stat-card-net {
    border-color: #a5b4fc !important;
    background: linear-gradient(180deg, #eef2ff 0%, #ffffff 72%) !important;
    box-shadow: 0 0 0 1px rgba(99, 102, 241, 0.14), 0 18px 36px rgba(99, 102, 241, 0.10) !important;
}

.stat-card-value {
    border-color: #cbd5e1 !important;
    background: linear-gradient(180deg, #f8fafc 0%, #ffffff 72%) !important;
    box-shadow: 0 0 0 1px rgba(148, 163, 184, 0.12), 0 18px 36px rgba(148, 163, 184, 0.08) !important;
}

.stat-card-live .stat-value { color: #1e3a8a; }
.stat-card-profit .stat-value { color: #14532d; }
.stat-card-loss .stat-value { color: #991b1b; }
.stat-card-net .stat-value { color: #312e81; }
.stat-card-value .stat-value { color: #334155; }
/* PIGSTEP DASHBOARD CARD COLOR RESTORE END */

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

.dashboard-section > div:first-child {
    position: relative;
}

.dashboard-section > div:first-child::after {
    content: "";
    display: block;
    height: 1px;
    margin-top: 10px;
    background: linear-gradient(90deg, rgba(37, 99, 235, 0.18), rgba(226, 232, 240, 0.85), transparent);
}

.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
}

.dashboard-grid .stat-card {
    border-color: #dbe4f0;
    box-shadow: 0 10px 26px rgba(15, 23, 42, 0.055);
}

.dashboard-grid .stat-card.metric-card-highlight {
    border-color: rgba(37, 99, 235, 0.22);
}

.dashboard-quick-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.dashboard-toggle {
    border: 1px solid #dbe4f0;
    border-radius: 18px;
    background: #fff;
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.055);
    overflow: hidden;
    position: relative;
}

.dashboard-toggle::before {
    content: "";
    position: absolute;
    inset: 0 0 auto 0;
    height: 3px;
    background: linear-gradient(90deg, var(--accent), rgba(37, 99, 235, 0.18), transparent);
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
    border-bottom: 1px solid transparent;
}

.dashboard-toggle[open] summary {
    border-bottom-color: #dbe4f0;
    background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
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
    border-bottom: 1px solid #e2e8f0;
    position: relative;
}

.dashboard-detail-row::before {
    content: "";
    position: absolute;
    left: 0;
    top: 12px;
    bottom: 12px;
    width: 3px;
    border-radius: 999px;
    background: transparent;
}

.dashboard-detail-row:hover {
    background: #fbfdff;
}

.dashboard-detail-row:hover::before {
    background: rgba(37, 99, 235, 0.28);
}


/* PIGSTEP SYNC PREVIEW COLOR CARD START */
.dashboard-detail-row.sync-preview-card {
    margin: 10px 14px 14px;
    border: 1px solid #cbd5e1;
    border-radius: 16px;
    background: linear-gradient(180deg, #f8fafc 0%, #ffffff 78%);
    box-shadow: 0 14px 30px rgba(15, 23, 42, 0.075);
    overflow: hidden;
}

.dashboard-detail-row.sync-preview-card::before {
    top: 0;
    bottom: 0;
    left: 0;
    width: 5px;
    border-radius: 0;
    background: #64748b;
}

.dashboard-detail-row.sync-preview-card.is-positive {
    border-color: #86efac;
    background: linear-gradient(180deg, #f0fdf4 0%, #ffffff 78%);
    box-shadow: 0 14px 30px rgba(34, 197, 94, 0.12);
}

.dashboard-detail-row.sync-preview-card.is-positive::before {
    background: #22c55e;
}

.dashboard-detail-row.sync-preview-card.is-positive .dashboard-detail-value {
    color: #14532d;
}

.dashboard-detail-row.sync-preview-card.is-negative {
    border-color: #fca5a5;
    background: linear-gradient(180deg, #fff1f2 0%, #ffffff 78%);
    box-shadow: 0 14px 30px rgba(239, 68, 68, 0.12);
}

.dashboard-detail-row.sync-preview-card.is-negative::before {
    background: #ef4444;
}

.dashboard-detail-row.sync-preview-card.is-negative .dashboard-detail-value {
    color: #991b1b;
}

.dashboard-detail-row.sync-preview-card.is-neutral {
    border-color: #bfdbfe;
    background: linear-gradient(180deg, #eff6ff 0%, #ffffff 78%);
    box-shadow: 0 14px 30px rgba(59, 130, 246, 0.10);
}

.dashboard-detail-row.sync-preview-card.is-neutral::before {
    background: #3b82f6;
}

.dashboard-detail-row.sync-preview-card.is-neutral .dashboard-detail-value {
    color: #1e3a8a;
}
/* PIGSTEP SYNC PREVIEW COLOR CARD END */

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
    border-left: 1px solid #e2e8f0;
    padding-left: 14px;
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

    .dashboard-detail-note {
        border-left: 0;
        padding-left: 0;
        padding-top: 6px;
        border-top: 1px dashed #e2e8f0;
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

            <div class="stat-card metric-card-highlight">
                <div class="stat-top">
                    <span class="label">Sold Profit</span>
                    <span class="badge green">Sold</span>
                </div>
                <div class="stat-value">₱ {{ number_format($totalRevenue, 2) }}</div>
                <div class="stat-sub">Total money received from sold pigs.</div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <span class="label">Mortality Loss</span>
                    <span class="badge red">Loss</span>
                </div>
                <div class="stat-value">₱ {{ number_format($totalLossValue, 2) }}</div>
                <div class="stat-sub">Total money lost from mortality records.</div>
            </div>

            <div class="stat-card metric-card-highlight">
                <div class="stat-top">
                    <span class="label">Net Position</span>
                    <span class="badge blue">Net</span>
                </div>
                <div class="stat-value">₱ {{ number_format($netPosition, 2) }}</div>
                <div class="stat-sub">Official net = Sold Profit - Mortality Loss.</div>
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


    
    <details class="dashboard-toggle">
        <summary>
            <span>
                View Basic Counts
                <small>Show total active, sold, and mortality counts.</small>
            </span>
        </summary>

        <div class="dashboard-detail-list">
            <div class="dashboard-detail-row">
                <div class="dashboard-detail-label">Total Pigs</div>
                <div class="dashboard-detail-value">{{ $pigs->count() }}</div>
                <div class="dashboard-detail-note">All non-archived pig records currently counted by the dashboard.</div>
            </div>

            <div class="dashboard-detail-row">
                <div class="dashboard-detail-label">Active Pigs</div>
                <div class="dashboard-detail-value">{{ $livePigs->count() }}</div>
                <div class="dashboard-detail-note">Pigs currently in live farm operations.</div>
            </div>

            <div class="dashboard-detail-row">
                <div class="dashboard-detail-label">Sold Pigs</div>
                <div class="dashboard-detail-value">{{ $soldPigs->count() }}</div>
                <div class="dashboard-detail-note">Pigs with completed sale records.</div>
            </div>

            <div class="dashboard-detail-row">
                <div class="dashboard-detail-label">Mortality Records</div>
                <div class="dashboard-detail-value">{{ $deadPigs->count() }}</div>
                <div class="dashboard-detail-note">Pigs with mortality records.</div>
            </div>
        </div>
    </details>


    
    <details class="dashboard-toggle">
        <summary>
            <span>
                View Financial Details
                <small>Show official sold profit, mortality loss, and net position.</small>
            </span>
        </summary>

        <div class="dashboard-detail-list">
            <div class="dashboard-detail-row">
                <div class="dashboard-detail-label">Sold Profit</div>
                <div class="dashboard-detail-value">₱ {{ number_format($totalRevenue, 2) }}</div>
                <div class="dashboard-detail-note">Total money received from sold pigs.</div>
            </div>

            <div class="dashboard-detail-row">
                <div class="dashboard-detail-label">Mortality Loss</div>
                <div class="dashboard-detail-value">₱ {{ number_format($totalLossValue, 2) }}</div>
                <div class="dashboard-detail-note">Frozen value lost from dead pigs.</div>
            </div>

            <div class="dashboard-detail-row">
                <div class="dashboard-detail-label">Net Position</div>
                <div class="dashboard-detail-value">₱ {{ number_format($netPosition, 2) }}</div>
                <div class="dashboard-detail-note">Official net = Sold Profit - Mortality Loss. Reference costs are shown separately below and are not deducted here.</div>
            </div>
        </div>
    </details>


    <details class="dashboard-toggle">
        <summary>
            <span>
                View Reference Cost / Sync Preview
                <small>Show purchased pig, purchased semen, and breeding costs. Not included in official net.</small>
            </span>
        </summary>

        <div class="dashboard-detail-list">
            <div class="dashboard-detail-row">
                <div class="dashboard-detail-label">Purchased Pig Cost</div>
                <div class="dashboard-detail-value">₱ {{ number_format($totalPurchasedPigCost, 2) }}</div>
                <div class="dashboard-detail-note">Reference only. This is not deducted from official Net Position.</div>
            </div>

            <div class="dashboard-detail-row">
                <div class="dashboard-detail-label">Purchased Semen Cost</div>
                <div class="dashboard-detail-value">₱ {{ number_format($totalPurchasedSemenCost, 2) }}</div>
                <div class="dashboard-detail-note">Reference only. Comes from breeding records and retry attempts.</div>
            </div>

            <div class="dashboard-detail-row">
                <div class="dashboard-detail-label">Breeding Service Cost</div>
                <div class="dashboard-detail-value">₱ {{ number_format($totalBreedingServiceCost, 2) }}</div>
                <div class="dashboard-detail-note">Reference only. Covers recorded breeding/service handling cost.</div>
            </div>

            <div class="dashboard-detail-row">
                <div class="dashboard-detail-label">Total Reference Cost</div>
                <div class="dashboard-detail-value">₱ {{ number_format($totalReferenceCost, 2) }}</div>
                <div class="dashboard-detail-note">Purchased pigs + purchased semen + breeding service cost only.</div>
            </div>

            @php
                $projectedSyncPreview = $netPosition - $totalReferenceCost;
                $projectedSyncPreviewClass = $projectedSyncPreview < 0
                    ? 'is-negative'
                    : ($projectedSyncPreview > 0 ? 'is-positive' : 'is-neutral');
            @endphp

            <div class="dashboard-detail-row sync-preview-card {{ $projectedSyncPreviewClass }}">
                <div class="dashboard-detail-label">Projected Sync Preview</div>
                <div class="dashboard-detail-value">₱ {{ number_format($projectedSyncPreview, 2) }}</div>
                <div class="dashboard-detail-note">Preview only = Official Net - Total Reference Cost. This does not include medication, vaccination, feed, or health costs.</div>
            </div>
        </div>
    </details>


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

<script id="pigstep-dashboard-card-color-restore">
document.addEventListener('DOMContentLoaded', function () {
    const cards = Array.from(document.querySelectorAll('.stat-card'));

    cards.forEach(function (card) {
        const text = (card.textContent || '').toUpperCase();

        card.classList.remove(
            'stat-card-live',
            'stat-card-profit',
            'stat-card-loss',
            'stat-card-net',
            'stat-card-value'
        );

        if (text.includes('ACTIVE PIGS')) {
            card.classList.add('stat-card-live');
        } else if (text.includes('SOLD PROFIT') || text.includes('SALE REVENUE') || text.includes('SALE INCOME')) {
            card.classList.add('stat-card-profit');
        } else if (text.includes('MORTALITY') || text.includes('LOSS')) {
            card.classList.add('stat-card-loss');
        } else if (text.includes('NET POSITION')) {
            card.classList.add('stat-card-net');
        } else if (text.includes('LIVE ASSET VALUE') || text.includes('PURCHASED COST') || text.includes('VALUE')) {
            card.classList.add('stat-card-value');
        }
    });
});
</script>

@endsection
