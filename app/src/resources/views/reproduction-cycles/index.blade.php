@extends('layouts.app')

@section('title', 'Breeding Records')
@section('page_title', 'Breeding Records')
@section('page_subtitle', 'Parent breeding records with current snapshot and append-only timeline flow.')

@section('top_actions')
    <a href="{{ route('dashboard') }}" class="btn">Back to Dashboard</a>
    <a href="{{ route('pigs.index') }}" class="btn">Open Pig List</a>
@endsection


@section('styles')
.breeding-stack {
    display: grid;
    gap: 20px;
}

.breeding-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
}

.breeding-stack .stat-card,
.breeding-stack .panel-card,
.breeding-guide-toggle {
    border-color: #dbe4f0;
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.055);
    position: relative;
    overflow: hidden;
}

.breeding-stack .panel-card::before,
.breeding-guide-toggle::before {
    content: "";
    position: absolute;
    inset: 0 0 auto 0;
    height: 3px;
    background: linear-gradient(90deg, var(--accent), rgba(37, 99, 235, 0.18), transparent);
}

.breeding-stack .section-title {
    padding-bottom: 14px;
    border-bottom: 1px solid #e2e8f0;
    margin-bottom: 18px;
}

.breeding-guide-toggle {
    border: 1px solid #dbe4f0;
    border-radius: 18px;
    background: #fff;
}

.breeding-guide-toggle summary {
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
    background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
}

.breeding-guide-toggle[open] summary {
    border-bottom-color: #dbe4f0;
}

.breeding-guide-toggle summary::-webkit-details-marker {
    display: none;
}

.breeding-guide-toggle summary small {
    display: block;
    color: var(--muted);
    font-size: 12px;
    font-weight: 500;
    margin-top: 3px;
}

.breeding-guide-toggle summary::after {
    content: "View";
    flex: 0 0 auto;
    border: 1px solid var(--line);
    border-radius: 999px;
    padding: 7px 12px;
    font-size: 12px;
    color: var(--primary);
    background: #f8fbff;
}

.breeding-guide-toggle[open] summary::after {
    content: "Hide";
}

.breeding-guide-body {
    display: grid;
    gap: 12px;
    padding: 16px 18px 18px;
    background: #fbfdff;
}

.breeding-guide-row {
    display: grid;
    grid-template-columns: minmax(150px, 0.3fr) minmax(0, 1fr);
    gap: 14px;
    border: 1px solid #dbe4f0;
    border-radius: 14px;
    background: #fff;
    padding: 13px;
}

.breeding-guide-row strong {
    color: var(--text);
}

.breeding-guide-row span {
    color: var(--muted);
    font-size: 13px;
    line-height: 1.45;
}

.breeding-guide-note {
    border: 1px solid #bfdbfe;
    background: #eff6ff;
    color: #1e3a8a;
    border-radius: 14px;
    padding: 13px;
    font-size: 13px;
    line-height: 1.45;
}

.breeding-table-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.breeding-stack .table-wrap {
    border: 1px solid #dbe4f0;
    border-radius: 16px;
    overflow: hidden;
    background: #fff;
}

.breeding-stack .data-table thead th {
    background: #f8fbff;
    border-bottom: 1px solid #dbe4f0;
}

.breeding-stack .data-table tbody tr + tr td {
    border-top: 1px solid #e2e8f0;
}

.breeding-stack .data-table tbody tr:hover td {
    background: #fbfdff;
}

@media (max-width: 1100px) {
    .breeding-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 760px) {
    .breeding-grid {
        grid-template-columns: 1fr;
    }

    .breeding-guide-toggle summary {
        align-items: flex-start;
    }

    .breeding-guide-row {
        grid-template-columns: 1fr;
        gap: 6px;
    }

    .table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .data-table {
        min-width: 980px;
    }

    .breeding-table-actions,
    .breeding-table-actions .btn {
        width: 100%;
    }
}
@endsection

@section('content')
    <div class="breeding-stack">
        <div class="breeding-grid">
            <div class="stat-card">
                <div class="stat-top">
                    <span class="label">Total Records</span>
                    <span class="badge blue">All</span>
                </div>
                <div class="stat-value">{{ $cycles->count() }}</div>
                <div class="stat-sub">All breeding records currently recorded in the farm.</div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <span class="label">Active Records</span>
                    <span class="badge green">Live</span>
                </div>
                <div class="stat-value">{{ $activeCycles->count() }}</div>
                <div class="stat-sub">Serviced, pregnant, or derived due-soon breeding records.</div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <span class="label">Inactive Records</span>
                    <span class="badge orange">History</span>
                </div>
                <div class="stat-value">{{ $closedCycles->count() }}</div>
                <div class="stat-sub">Not pregnant, returned-to-heat, farrowed, or closed breeding records.</div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <span class="label">Breeding Guide</span>
                    <span class="badge blue">Guide</span>
                </div>
                <div class="stat-sub">Use one breeding case per sow exposure. Open the record to append pregnancy, return-to-heat, farrowing, and piglet registration updates.</div>
            </div>
        </div>

        <details class="breeding-guide-toggle">
            <summary>
                <span>
                    View Full Breeding Guide
                    <small>Explains breeding records, retry attempts, farrowing, and piglet lineage.</small>
                </span>
            </summary>

            <div class="breeding-guide-body">
                <div class="breeding-guide-note">
                    Breeding records are parent cases. The case keeps the sow, service date, pregnancy status, farrowing result, costs, and piglet lineage together.
                </div>

                <div class="breeding-guide-row">
                    <strong>1. Start from the sow profile</strong>
                    <span>Create the breeding record once for the sow. The service date becomes the anchor date for expected farrowing and follow-up reminders.</span>
                </div>

                <div class="breeding-guide-row">
                    <strong>2. Attempts</strong>
                    <span>An attempt means one service or retry inside the same breeding case. Attempt 1 is the first service. If the sow returns to heat or is not pregnant, append that update and continue with the next attempt instead of creating messy duplicate records.</span>
                </div>

                <div class="breeding-guide-row">
                    <strong>3. Pregnancy result</strong>
                    <span>Mark pregnant, not pregnant, or returned to heat through the case timeline. Active and due-soon status are calculated from the timeline and dates, not typed manually.</span>
                </div>

                <div class="breeding-guide-row">
                    <strong>4. Farrowing</strong>
                    <span>When the sow farrows, record the actual farrow date in the breeding case. Future farrow dates are blocked so medication schedules do not start from the wrong future anchor.</span>
                </div>

                <div class="breeding-guide-row">
                    <strong>5. Piglet registration</strong>
                    <span>Register born piglets from the farrowed breeding case. This is what connects the piglet to the dam, birth case, lineage, and medication program.</span>
                </div>

                <div class="breeding-guide-row">
                    <strong>6. Medication program</strong>
                    <span>Only farrowing-linked piglets and lactating sows receive medication schedules. Manual Birthed pigs remain simple records and do not start protocols.</span>
                </div>

                <div class="breeding-guide-row">
                    <strong>7. Costs</strong>
                    <span>Breeding cost stays attached to the breeding case and is reflected in dashboard financial summaries. Sale, mortality, and pig value records stay separate.</span>
                </div>
            </div>
        </details>

        <div class="panel-card">
            <div class="section-title">
                <div>
                    <h3>All Breeding Records</h3>
                    <p>Each row is a parent breeding record. Open the record to see the summary, timeline, and event-specific progress form.</p>
                </div>
            </div>

            @if($cycles->isEmpty())
                <div class="empty-state">No breeding records recorded yet.</div>
            @else
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Sow</th>
                                <th>Type</th>
                                <th>Boar</th>
                                <th>Status</th>
                                <th>Pregnancy Result</th>
                                <th>Service Date</th>
                                <th>Expected Farrow</th>
                                <th>Timeline Events</th>
                                <th>Registered Piglets</th>
                                <th>Breeding Cost</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cycles as $cycle)
                                @php
                                    $displayStatus = $cycle->display_status;

                                    $statusBadgeClass = match($displayStatus) {
                                        \App\Models\ReproductionCycle::STATUS_PREGNANT => 'green',
                                        \App\Models\ReproductionCycle::STATUS_DUE_SOON => 'blue',
                                        \App\Models\ReproductionCycle::STATUS_FARROWED => 'blue',
                                        \App\Models\ReproductionCycle::STATUS_NOT_PREGNANT => 'red',
                                        \App\Models\ReproductionCycle::STATUS_RETURNED_TO_HEAT => 'orange',
                                        \App\Models\ReproductionCycle::STATUS_CLOSED => 'orange',
                                        default => 'orange',
                                    };

                                    $pregnancyBadgeClass = match($cycle->pregnancy_result) {
                                        \App\Models\ReproductionCycle::PREGNANCY_RESULT_PREGNANT => 'green',
                                        \App\Models\ReproductionCycle::PREGNANCY_RESULT_NOT_PREGNANT => 'red',
                                        default => 'blue',
                                    };

                                    $registeredPiglets = (int) ($cycle->born_piglets_count ?? 0);
                                @endphp
                                <tr>
                                    <td>{{ $cycle->sow?->ear_tag ?? '—' }}</td>
                                    <td>{{ $cycle->breeding_type_label }}</td>
                                    <td>{{ $cycle->boar?->ear_tag ?? '—' }}</td>
                                    <td>
                                        <span class="badge {{ $statusBadgeClass }}">
                                            {{ $cycle->status_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $pregnancyBadgeClass }}">
                                            {{ $cycle->pregnancy_result_label }}
                                        </span>
                                    </td>
                                    <td>{{ $cycle->service_date?->format('Y-m-d') ?? '—' }}</td>
                                    <td>{{ $cycle->expected_farrow_date?->format('Y-m-d') ?? '—' }}</td>
                                    <td>{{ (int) ($cycle->updates_count ?? 0) }}</td>
                                    <td>{{ $registeredPiglets }}</td>
                                    <td>₱ {{ number_format((float) $cycle->breeding_cost, 2) }}</td>
                                    <td>
                                        <div class="breeding-table-actions">
                                            <a href="{{ route('reproduction-cycles.show', $cycle) }}" class="btn primary">Open Record</a>
                                            <a href="{{ route('reproduction-cycles.edit', $cycle) }}" class="btn">Edit Metadata</a>
                                        </div>
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
