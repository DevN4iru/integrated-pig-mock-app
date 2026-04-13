@extends('layouts.app')

@section('title', 'Breeding Records')
@section('page_title', 'Breeding Records')
@section('page_subtitle', 'Sow reproduction history, active cycles, and farrowing tracking.')

@section('top_actions')
    <a href="{{ route('dashboard') }}" class="btn">Back to Dashboard</a>
    <a href="{{ route('pigs.index') }}" class="btn">Open Pig List</a>
@endsection

@section('content')
    <div class="grid" style="gap: 20px;">
        <div class="grid stats">
            <div class="stat-card">
                <div class="stat-top">
                    <span class="label">Total Cycles</span>
                    <span class="badge blue">All</span>
                </div>
                <div class="stat-value">{{ $cycles->count() }}</div>
                <div class="stat-sub">All recorded reproduction cycles.</div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <span class="label">Active Cycles</span>
                    <span class="badge green">Live</span>
                </div>
                <div class="stat-value">{{ $activeCycles->count() }}</div>
                <div class="stat-sub">Open or pregnant breeding records.</div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <span class="label">Closed Cycles</span>
                    <span class="badge orange">Done</span>
                </div>
                <div class="stat-value">{{ $closedCycles->count() }}</div>
                <div class="stat-sub">Failed or farrowed cycles.</div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <span class="label">How to Add</span>
                    <span class="badge blue">Flow</span>
                </div>
                <div class="stat-sub">Open a female pig profile and start a new reproduction cycle there.</div>
            </div>
        </div>

        <div class="panel-card">
            <div class="section-title">
                <div>
                    <h3>All Reproduction Cycles</h3>
                    <p>Each row represents one breeding attempt for one sow.</p>
                </div>
            </div>

            @if($cycles->isEmpty())
                <div class="empty-state">No breeding records yet.</div>
            @else
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Sow</th>
                                <th>Type</th>
                                <th>Boar</th>
                                <th>Status</th>
                                <th>Service Date</th>
                                <th>Expected Farrow</th>
                                <th>Actual Farrow</th>
                                <th>Breeding Cost</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cycles as $cycle)
                                <tr>
                                    <td>{{ $cycle->sow?->ear_tag ?? '—' }}</td>
                                    <td>{{ $cycle->breeding_type_label }}</td>
                                    <td>{{ $cycle->boar?->ear_tag ?? '—' }}</td>
                                    <td>
                                        <span class="badge {{ match($cycle->status) {
                                            'pregnant' => 'green',
                                            'farrowed' => 'blue',
                                            'failed' => 'red',
                                            default => 'orange',
                                        } }}">
                                            {{ $cycle->status_label }}
                                        </span>
                                    </td>
                                    <td>{{ $cycle->service_date?->format('Y-m-d') ?? '—' }}</td>
                                    <td>{{ $cycle->expected_farrow_date?->format('Y-m-d') ?? '—' }}</td>
                                    <td>{{ $cycle->actual_farrow_date?->format('Y-m-d') ?? '—' }}</td>
                                    <td>₱ {{ number_format((float) $cycle->breeding_cost, 2) }}</td>
                                    <td>
                                        <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                            <a href="{{ route('reproduction-cycles.edit', $cycle) }}" class="btn">Edit</a>
                                            @if($cycle->sow)
                                                <a href="{{ route('pigs.show', $cycle->sow) }}" class="btn">Go to Sow</a>
                                            @endif
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
