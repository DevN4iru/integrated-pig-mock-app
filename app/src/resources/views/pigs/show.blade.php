@extends('layouts.app')

@section('title', 'Pig Profile')
@section('page_title', 'Pig Profile')
@section('page_subtitle', 'Detailed view of selected pig.')

@section('top_actions')
    <a href="{{ route('pigs.index') }}" class="btn">Back to Pig List</a>
    <button type="button" class="btn" onclick="openPigEditPrompt('{{ route('pigs.edit', $pig) }}')">Edit Pig</button>
    <a href="{{ route('health-logs.create', $pig) }}" class="btn primary">Add Health Log</a>
@endsection

@section('content')
    @php
        $dateAdded = $pig->date_added ? substr((string) $pig->date_added, 0, 10) : '—';
        $weight = is_numeric($pig->latest_weight) ? number_format((float) $pig->latest_weight, 2) : $pig->latest_weight;
        $assetValue = is_numeric($pig->asset_value) ? number_format((float) $pig->asset_value, 2) : $pig->asset_value;
        $penName = optional($pig->pen)->name ?: ($pig->pen_location ?? '—');
    @endphp

    <div class="panel-card">
        <div class="section-title">
            <div>
                <h3>Pig Overview</h3>
                <p>Core information for this pig record.</p>
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label>Ear Tag</label>
                <input type="text" value="{{ $pig->ear_tag }}" readonly>
            </div>

            <div class="form-group">
                <label>Breed</label>
                <input type="text" value="{{ $pig->breed }}" readonly>
            </div>

            <div class="form-group">
                <label>Sex</label>
                <input type="text" value="{{ ucfirst($pig->sex) }}" readonly>
            </div>

            <div class="form-group">
                <label>Assigned Pen</label>
                <input type="text" value="{{ $penName }}" readonly>
            </div>

            <div class="form-group">
                <label>Source</label>
                <input type="text" value="{{ ucfirst($pig->pig_source) }}" readonly>
            </div>

            <div class="form-group">
                <label>Date Added</label>
                <input type="text" value="{{ $dateAdded }}" readonly>
            </div>

            <div class="form-group">
                <label>Latest Weight</label>
                <input type="text" value="{{ $weight }} kg" readonly>
            </div>

            <div class="form-group">
                <label>Asset Value</label>
                <input type="text" value="₱ {{ $assetValue }}" readonly>
            </div>
        </div>
    </div>

    <div class="panel-card" style="margin-top: 20px;">
        <div class="section-title">
            <div>
                <h3>Health Logs</h3>
                <p>Recorded health conditions and notes for this pig.</p>
            </div>
            <a href="{{ route('health-logs.create', $pig) }}" class="btn primary">Add Health Log</a>
        </div>

        @if($pig->healthLogs->isEmpty())
            <div class="empty-state">
                No health logs yet.
            </div>
        @else
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Condition</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pig->healthLogs as $log)
                            <tr>
                                <td>{{ $log->log_date }}</td>
                                <td>{{ $log->condition }}</td>
                                <td>{{ $log->notes ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="panel-card" style="margin-top: 20px;">
        <div class="section-title">
            <div>
                <h3>Medication</h3>
                <p>Treatments and administered medicines for this pig.</p>
            </div>
            <a href="{{ route('medications.create', $pig) }}" class="btn primary">Add Medication</a>
        </div>

        @if($pig->medications->isEmpty())
            <div class="empty-state">
                No medication records yet.
            </div>
        @else
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Medication</th>
                            <th>Dosage</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pig->medications as $med)
                            <tr>
                                <td>{{ $med->administered_at }}</td>
                                <td>{{ $med->medication_name }}</td>
                                <td>{{ $med->dosage }}</td>
                                <td>{{ $med->notes ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="panel-card" style="margin-top: 20px;">
        <div class="section-title">
            <div>
                <h3>Lifecycle Modules</h3>
                <p>These sections will be connected next.</p>
            </div>
        </div>

        <div class="grid stats-grid">
            <div class="stat-card">
                <div class="stat-top">
                    <span class="label">Vaccination</span>
                    <span class="badge orange">Soon</span>
                </div>
                <div class="stat-sub">Vaccine records and immunization history.</div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <span class="label">Feed Logs</span>
                    <span class="badge blue">Soon</span>
                </div>
                <div class="stat-sub">Feed intake, schedule, and growth support tracking.</div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <span class="label">Mortality / Sold</span>
                    <span class="badge red">Soon</span>
                </div>
                <div class="stat-sub">Lifecycle outcome, sold records, and mortality tracking.</div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
function openPigEditPrompt(url) {
    const code = prompt('Type the edit access code to continue:');
    if (code === null) return;
    if (code !== '12345') {
        alert('Wrong access code.');
        return;
    }
    window.location.href = url + '?code=' + encodeURIComponent(code);
}
@endsection