@extends('layouts.app')

@section('title', 'Breeding Records')
@section('page_title', 'Breeding Records')
@section('page_subtitle', 'Parent breeding records with current snapshot and append-only timeline flow.')

@section('top_actions')
    <a href="{{ route('dashboard') }}" class="btn">Back to Dashboard</a>
    <a href="{{ route('pigs.index') }}" class="btn">Open Pig List</a>
@endsection

@section('content')
    <div class="grid" style="gap: 20px;">
        <div class="grid stats">
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
                    <span class="label">Primary Workflow</span>
                    <span class="badge blue">Flow A</span>
                </div>
                <div class="stat-sub">Create the record once from the sow profile, then append biological milestones as separate timeline events. Due soon is date-derived, not manually submitted.</div>
            </div>
        </div>

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
                                        <div style="display:flex; gap:8px; flex-wrap:wrap;">
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
