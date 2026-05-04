@extends('layouts.app')

@section('title', 'Choose Sow for Breeding Record')
@section('page_title', 'Choose Sow for Breeding Record')
@section('page_subtitle', 'Select a sow or female pig, then start a breeding record.')

@section('top_actions')
    <a href="{{ route('reproduction-cycles.index') }}" class="btn">Back to Breeding Records</a>
    <a href="{{ route('pigs.index') }}" class="btn">Open Pig List</a>
@endsection

@section('content')
    @php
        $sows = $sows ?? collect();
        $dateLabel = fn ($date) => $date ? $date->format('F j, Y') : '—';
    @endphp

    <div class="panel-card" style="margin-bottom: 16px;">
        <div class="section-title">
            <div>
                <h3>Breeding Candidate List</h3>
                <p>Choose a female pig below to start a new breeding record.</p>
            </div>
            <span class="badge blue">{{ $sows->count() }} candidate(s)</span>
        </div>

        <div class="flash" style="margin-bottom: 0;">
            This list shows active female pigs only. Piglets and family tree details are available from each pig profile.
        </div>
    </div>

    @if($sows->isEmpty())
        <div class="panel-card">
            <div class="empty-state">
                No sow candidates found yet. Add female pigs first, then create a breeding record.
            </div>
            <div class="form-actions" style="margin-top: 12px;">
                <a href="{{ route('pigs.create') }}" class="btn primary">Add Pig</a>
            </div>
        </div>
    @else
        <div class="panel-card">
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
                        @foreach($sows as $sow)
                            @php
                                $latestCycle = $sow->latestBreedingRecordForStatus();
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $sow->ear_tag ?: 'Unnamed Pig' }}</strong><br>
                                    <small>{{ $sow->breed ?: 'Breed not set' }}</small>
                                </td>
                                <td>{{ $sow->pen?->name ?? 'No pen assigned' }}</td>
                                <td>
                                    <span class="badge {{ $sow->breeding_status_badge_class }}">{{ $sow->breeding_status_label }}</span>
                                </td>
                                <td>{{ number_format((int) $sow->reproduction_cycles_as_sow_count) }} breeding record(s)</td>
                                <td>
                                    @if($latestCycle)
                                        <div><strong>{{ $latestCycle->display_status_label }}</strong></div>
                                        <small>Service: {{ $dateLabel($latestCycle->service_date) }}</small>
                                    @else
                                        <small>No breeding record yet.</small>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ number_format((int) $sow->birthed_piglets_count) }} registered piglet(s)</div>
                                    <small>Family tree available from pig profile.</small>
                                </td>
                                <td>
                                    <div class="breeding-table-actions">
                                        <a href="{{ route('reproduction-cycles.create', $sow) }}" class="btn primary">Start Breeding Record</a>
                                        <a href="{{ route('pigs.show', $sow) }}" class="btn">View Breeding History</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
