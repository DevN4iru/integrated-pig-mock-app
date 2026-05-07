@extends('layouts.app')

@section('title', 'Choose Sow for Breeding Record')
@section('page_title', 'Choose Sow for Breeding Record')
@section('page_subtitle', 'Select a sow or female pig, then start a breeding record.')

@section('top_actions')
    <a href="{{ route('reproduction-cycles.index') }}" class="btn">Back to Breeding Records</a>
    <a href="{{ route('pigs.index') }}" class="btn">Open Pig List</a>
@endsection

@section('styles')
.not-ready-sows-toggle {
    padding: 0;
    overflow: hidden;
}

.not-ready-sows-toggle summary {
    list-style: none;
    cursor: pointer;
    padding: 18px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    background: linear-gradient(180deg, #fff7ed 0%, #ffffff 100%);
}

.not-ready-sows-toggle summary::-webkit-details-marker {
    display: none;
}

.not-ready-sows-toggle summary h3 {
    margin: 0 0 4px;
    color: #7c2d12;
}

.not-ready-sows-toggle summary p {
    margin: 0;
    color: var(--muted);
}

.not-ready-sows-toggle summary::after {
    content: "View";
    flex: 0 0 auto;
    border: 1px solid #fed7aa;
    border-radius: 999px;
    padding: 7px 12px;
    font-size: 12px;
    font-weight: 800;
    color: #c2410c;
    background: #fff;
}

.not-ready-sows-toggle[open] summary::after {
    content: "Hide";
}

.not-ready-sows-body {
    padding: 16px 18px 18px;
    border-top: 1px solid #fed7aa;
    background: #fff;
}
@endsection

@section('content')
    @php
        $readySows = $readySows ?? collect();
        $notReadySows = $notReadySows ?? collect();
        $dateLabel = fn ($date) => $date ? $date->format('F j, Y') : '—';

        $notReadyReason = function ($pig) {
            $penType = $pig->pen?->type;

            return $penType
                ? 'Currently in ' . $penType . ' pen. Move to Replacement Gilt or Sow pen when ready.'
                : 'No breeding-ready pen assigned yet. Move to Replacement Gilt or Sow pen when ready.';
        };
    @endphp

    <div class="panel-card" style="margin-bottom: 16px;">
        <div class="section-title">
            <div>
                <h3>Breeding Candidate List</h3>
                <p>Choose from breeding-ready females first. Young or not-ready females are listed separately below.</p>
            </div>
            <span class="badge blue">{{ $readySows->count() + $notReadySows->count() }} female pig(s)</span>
        </div>

        <div class="flash" style="margin-bottom: 10px;">
            Medication programs are health schedules and can appear for piglets or young pigs. Breeding records are separate and start only when the animal is ready to be bred.
        </div>
    </div>

    <div class="panel-card" style="margin-bottom: 16px;">
        <div class="section-title">
            <div>
                <h3>Ready for Breeding Record</h3>
                <p>These females are in sow or breeding-related pen types and are the best starting point for new breeding records.</p>
            </div>
            <span class="badge green">{{ $readySows->count() }}</span>
        </div>

        @if($readySows->isEmpty())
            <div class="empty-state">No breeding-ready female pigs found right now.</div>
        @else
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>Sow / Female Pig</th>
                        <th>Current Pen</th>
                        <th>Breeding Status</th>
                        <th>Breeding History</th>
                        <th>Latest Record</th>
                        <th>Piglet / Family Hint</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($readySows as $sow)
                        @php
                            $latestCycle = $sow->latestBreedingRecordForStatus();
                            $activeCycle = $sow->reproductionCyclesAsSow
                                ->first(fn ($cycle) => $cycle->is_active_cycle);
                        @endphp
                        <tr>
                            <td><strong>{{ $sow->ear_tag ?: 'Unnamed Pig' }}</strong><br><small>{{ $sow->breed ?: 'Breed not set' }}</small></td>
                            <td>{{ $sow->pen?->name ?? 'No pen assigned' }}</td>
                            <td><span class="badge {{ $sow->breeding_status_badge_class }}">{{ $sow->breeding_status_label }}</span></td>
                            <td>{{ number_format((int) $sow->reproduction_cycles_as_sow_count) }} breeding record(s)</td>
                            <td>
                                @if($latestCycle)
                                    <div><strong>{{ $latestCycle->display_status_label }}</strong></div>
                                    <small>Service: {{ $dateLabel($latestCycle->service_date) }}</small>
                                @else
                                    <small>No breeding record yet.</small>
                                @endif
                            </td>
                            <td><div>{{ number_format((int) $sow->birthed_piglets_count) }} registered piglet(s)</div><small>Family tree available from pig profile.</small></td>
                            <td>
                                <div class="breeding-table-actions">
                                    @if($activeCycle)
                                        <span class="badge orange">Active breeding record</span>
                                        <a href="{{ route('reproduction-cycles.show', $activeCycle) }}" class="btn primary">Open Active Record</a>
                                    @else
                                        <a href="{{ route('reproduction-cycles.create', $sow) }}" class="btn primary">Start Breeding Record</a>
                                    @endif
                                    <a href="{{ route('pigs.show', $sow) }}" class="btn">View Pig Profile</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <details class="panel-card not-ready-sows-toggle">
        <summary>
            <div>
                <h3>Hidden / Not Ready Females</h3>
                <p>Reference only. These females are hidden from breeding actions until moved to a breeding-ready pen.</p>
            </div>
            <span class="badge orange">{{ $notReadySows->count() }}</span>
        </summary>

        <div class="not-ready-sows-body">
            @if($notReadySows->isEmpty())
                <div class="empty-state">No hidden or not-ready female pigs right now.</div>
            @else
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                        <tr>
                            <th>Female Pig</th>
                            <th>Current Pen</th>
                            <th>Breeding History</th>
                            <th>Readiness Note</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($notReadySows as $pig)
                            <tr>
                                <td><strong>{{ $pig->ear_tag ?: 'Unnamed Pig' }}</strong><br><small>{{ $pig->breed ?: 'Breed not set' }}</small></td>
                                <td>{{ $pig->pen?->name ?? 'No pen assigned' }}</td>
                                <td>{{ number_format((int) $pig->reproduction_cycles_as_sow_count) }} breeding record(s)</td>
                                <td>{{ $notReadyReason($pig) }}</td>
                                <td>
                                    <div class="breeding-table-actions">
                                        <span class="badge gray">Not ready yet</span>
                                        <a href="{{ route('pigs.show', $pig) }}" class="btn">View Pig Profile</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </details>
@endsection
