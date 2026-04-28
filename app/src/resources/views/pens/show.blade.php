@extends('layouts.app')

@section('title', 'Pen Details')
@section('page_title', 'Pen Details')
@section('page_subtitle', 'Simple detail page for this housing pen.')

@section('top_actions')
    <a href="{{ route('pens.index') }}" class="btn">Back to Pen List</a>
    <a href="{{ route('pigs.create') }}" class="btn">Add Pig</a>
    <button type="button" class="btn" onclick="openPenEditPrompt('{{ route('pens.edit', $pen) }}')">Edit Pen</button>
@endsection

@section('styles')
.pen-detail-grid {
    display: grid;
    gap: 20px;
}

.pen-detail-stats {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
}

.pen-detail-card {
    border: 1px solid var(--line);
    border-radius: 16px;
    background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
    padding: 18px;
}




.inline-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

@media (max-width: 1200px) {
    .pen-detail-stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 640px) {
    .pen-detail-stats {
        grid-template-columns: 1fr;
    }
}
@endsection

@section('content')
<div class="pen-detail-grid">

    <div class="panel-card">
        <div class="section-title">
            <div>
                <h3>{{ $pen->name }}</h3>
                <p>{{ $pen->display_type }} pen summary.</p>
            </div>
            <span class="badge {{ $pen->occupancyBadgeClass() }}">
                {{ ucfirst($summary['status']) }}
            </span>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label>Pen Name</label>
                <input type="text" value="{{ $pen->name }}" readonly>
            </div>

            <div class="form-group">
                <label>Pen Type</label>
                <input type="text" value="{{ $pen->display_type }}" readonly>
            </div>

            <div class="form-group">
                <label>Capacity</label>
                <input type="text" value="{{ $summary['capacity'] }}" readonly>
            </div>

            <div class="form-group">
                <label>Occupied</label>
                <input type="text" value="{{ $summary['occupied'] }}" readonly>
            </div>

            <div class="form-group">
                <label>Available</label>
                <input type="text" value="{{ $summary['available'] }}" readonly>
            </div>

            <div class="form-group full">
                <label>Notes</label>
                <textarea readonly>{{ $pen->notes ?: '—' }}</textarea>
            </div>
        </div>
    </div>

    <div class="pen-detail-stats">
        <div class="pen-detail-card">
            <div class="label">Occupied Slots</div>
            <div class="stat-value">{{ $summary['occupied'] }}</div>
        </div>

        <div class="pen-detail-card">
            <div class="label">Available Slots</div>
            <div class="stat-value">{{ $summary['available'] }}</div>
        </div>

        <div class="pen-detail-card">
            <div class="label">Total Capacity</div>
            <div class="stat-value">{{ $summary['capacity'] }}</div>
        </div>

        <div class="pen-detail-card">
            <div class="label">Occupancy Status</div>
            <div class="stat-value">{{ ucfirst($summary['status']) }}</div>
        </div>
    </div>

    <div class="panel-card">
        <div class="section-title">
            <div>
                <h3>Active Pigs Inside This Pen</h3>
                <p>Only active pigs count toward live pen occupancy.</p>
            </div>
        </div>

        @if ($activePigs->isEmpty())
            <div class="empty-state">No active pigs are currently assigned to this pen.</div>
        @else
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Ear Tag</th>
                            <th>Breed</th>
                            <th>Sex</th>
                            <th>Age</th>
                            <th>Source</th>
                            <th>Latest Weight</th>
                            <th>Value</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($activePigs as $pig)
                            <tr>
                                <td>{{ $pig->ear_tag }}</td>
                                <td>{{ $pig->breed }}</td>
                                <td>{{ ucfirst($pig->sex) }}</td>
                                <td>{{ (int) ($pig->age ?? 0) }}</td>
                                <td>{{ ucfirst($pig->pig_source) }}</td>
                                <td>{{ $pig->computed_weight !== null ? number_format((float) $pig->computed_weight, 2) . ' kg' : '—' }}</td>
                                <td>₱ {{ number_format((float) $pig->computed_asset_value, 2) }}</td>
                                <td>
                                    <a href="{{ route('pigs.show', $pig) }}" class="btn">Go to Pig</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="panel-card">
        <div class="section-title">
            <div>
                <h3>Recent Transfer History</h3>
                <p>Latest transfer activity connected to this pen.</p>
            </div>
        </div>

        @if ($recentTransfers->isEmpty())
            <div class="empty-state">No transfer history recorded for this pen yet.</div>
        @else
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Pig</th>
                            <th>Route</th>
                            <th>Reason</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentTransfers as $transfer)
                            <tr>
                                <td>{{ optional($transfer->transfer_date)->format('Y-m-d') ?? '—' }}</td>
                                <td>{{ $transfer->pig?->ear_tag ?? '—' }}</td>
                                <td>
                                    {{ $transfer->fromPen?->name ?? '—' }}
                                    →
                                    {{ $transfer->toPen?->name ?? '—' }}
                                </td>
                                <td>{{ $transfer->reason_label }}</td>
                                <td>{{ $transfer->reason_notes ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection

@section('scripts')
function openPenEditPrompt(url) {
    const code = prompt('Type the edit access code to continue:');
    if (code === null) return;
    if (code !== '12345') {
        alert('Wrong code');
        return;
    }
    window.location.href = url + '?code=' + encodeURIComponent(code);
}
@endsection