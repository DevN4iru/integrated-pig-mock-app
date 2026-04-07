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

        $isDead = $pig->mortalityLogs->isNotEmpty();
        $isSold = $pig->sales->isNotEmpty();
        $statusLabel = $isDead ? 'Dead' : ($isSold ? 'Sold' : 'Active');
        $statusBadgeClass = $isDead ? 'red' : ($isSold ? 'orange' : 'green');
    @endphp

    <div class="panel-card">
        <div class="section-title">
            <div>
                <h3>Pig Overview</h3>
                <p>Core information for this pig record.</p>
            </div>
            <span class="badge {{ $statusBadgeClass }}">{{ $statusLabel }}</span>
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
            <div class="empty-state">No health logs yet.</div>
        @else
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Condition</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pig->healthLogs as $log)
                            <tr>
                                <td>{{ $log->log_date }}</td>
                                <td>{{ $log->condition }}</td>
                                <td>{{ $log->notes ?: '—' }}</td>
                                <td>
                                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                        <a href="{{ route('health-logs.edit', [$pig, $log]) }}" class="btn">Edit</a>
                                        <form method="POST" action="{{ route('health-logs.destroy', [$pig, $log]) }}" onsubmit="return confirm('Delete this health log?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
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
            <div class="empty-state">No medication records yet.</div>
        @else
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Medication</th>
                            <th>Dosage</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pig->medications as $med)
                            <tr>
                                <td>{{ $med->administered_at }}</td>
                                <td>{{ $med->medication_name }}</td>
                                <td>{{ $med->dosage }}</td>
                                <td>{{ $med->notes ?: '—' }}</td>
                                <td>
                                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                        <a href="{{ route('medications.edit', [$pig, $med]) }}" class="btn">Edit</a>
                                        <form method="POST" action="{{ route('medications.destroy', [$pig, $med]) }}" onsubmit="return confirm('Delete this medication record?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
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
                <h3>Vaccination</h3>
                <p>Vaccination records and immunization history for this pig.</p>
            </div>
            <a href="{{ route('vaccinations.create', $pig) }}" class="btn primary">Add Vaccination</a>
        </div>

        @if($pig->vaccinations->isEmpty())
            <div class="empty-state">No vaccination records yet.</div>
        @else
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Vaccine</th>
                            <th>Dose</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pig->vaccinations as $vac)
                            <tr>
                                <td>{{ $vac->vaccinated_at }}</td>
                                <td>{{ $vac->vaccine_name }}</td>
                                <td>{{ $vac->dose }}</td>
                                <td>{{ $vac->notes ?: '—' }}</td>
                                <td>
                                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                        <a href="{{ route('vaccinations.edit', [$pig, $vac]) }}" class="btn">Edit</a>
                                        <form method="POST" action="{{ route('vaccinations.destroy', [$pig, $vac]) }}" onsubmit="return confirm('Delete this vaccination record?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
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
                <h3>Mortality</h3>
                <p>Mortality records for this pig.</p>
            </div>
            @if($pig->sales->isEmpty())
                <a href="{{ route('mortality.create', $pig) }}" class="btn primary">Record Mortality</a>
            @endif
        </div>

        @if($pig->sales->isNotEmpty())
            <div class="flash error" style="margin-bottom: 16px;">
                Mortality recording is locked because this pig already has a sale record.
            </div>
        @endif

        @if($pig->mortalityLogs->isEmpty())
            <div class="empty-state">No mortality records yet.</div>
        @else
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Cause</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pig->mortalityLogs as $mortality)
                            <tr>
                                <td>{{ $mortality->death_date }}</td>
                                <td>{{ $mortality->cause }}</td>
                                <td>{{ $mortality->notes ?: '—' }}</td>
                                <td>
                                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                        <a href="{{ route('mortality.edit', [$pig, $mortality]) }}" class="btn">Edit</a>
                                        <form method="POST" action="{{ route('mortality.destroy', [$pig, $mortality]) }}" onsubmit="return confirm('Delete this mortality record?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
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
                <h3>Sold Records</h3>
                <p>Sale records for this pig.</p>
            </div>
            @if($pig->mortalityLogs->isEmpty())
                <a href="{{ route('sales.create', $pig) }}" class="btn primary">Record Sale</a>
            @endif
        </div>

        @if($pig->mortalityLogs->isNotEmpty())
            <div class="flash error" style="margin-bottom: 16px;">
                Sale recording is locked because this pig already has a mortality record.
            </div>
        @endif

        @if($pig->sales->isEmpty())
            <div class="empty-state">No sale records yet.</div>
        @else
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Price</th>
                            <th>Buyer</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pig->sales as $sale)
                            <tr>
                                <td>{{ $sale->sold_date }}</td>
                                <td>₱ {{ number_format((float) $sale->price, 2) }}</td>
                                <td>{{ $sale->buyer ?: '—' }}</td>
                                <td>{{ $sale->notes ?: '—' }}</td>
                                <td>
                                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                        <a href="{{ route('sales.edit', [$pig, $sale]) }}" class="btn">Edit</a>
                                        <form method="POST" action="{{ route('sales.destroy', [$pig, $sale]) }}" onsubmit="return confirm('Delete this sale record?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
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
                <h3>Feed Logs</h3>
                <p>Feeding periods and diet tracking.</p>
            </div>
            <a href="{{ route('feed-logs.create', $pig) }}" class="btn primary">Add Feed Log</a>
        </div>

        @if($pig->feedLogs->isEmpty())
            <div class="empty-state">No feed logs yet.</div>
        @else
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Feed Type</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Qty</th>
                            <th>Unit</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pig->feedLogs as $feed)
                            <tr>
                                <td>{{ $feed->feed_type }}</td>
                                <td>{{ $feed->start_feed_date }}</td>
                                <td>{{ $feed->end_feed_date ?: 'Pending' }}</td>
                                <td>{{ $feed->quantity }}</td>
                                <td>{{ $feed->unit }}</td>
                                <td>{{ $feed->feeding_time }}</td>
                                <td>
                                    <span class="badge {{ $feed->status === 'completed' ? 'green' : 'orange' }}">
                                        {{ ucfirst($feed->status) }}
                                    </span>
                                </td>
                                <td>{{ $feed->notes ?: '—' }}</td>
                                <td>
                                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                        <a href="{{ route('feed-logs.edit', [$pig, $feed]) }}" class="btn">Edit</a>
                                        <form method="POST" action="{{ route('feed-logs.destroy', [$pig, $feed]) }}" onsubmit="return confirm('Delete this feed log?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
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