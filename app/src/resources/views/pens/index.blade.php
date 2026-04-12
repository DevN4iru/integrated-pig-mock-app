@extends('layouts.app')

@section('title', 'Pens')
@section('page_title', 'Pen List')
@section('page_subtitle', 'View all housing pens in the system.')

@section('top_actions')
    <a href="{{ route('pens.create') }}" class="btn primary">Add Pen</a>
@endsection

@section('styles')
.pen-meta {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.pen-occupancy {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.pen-stat-badge {
    font-size: 12px;
    padding: 6px 10px;
    border-radius: 999px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}

.pen-stat-badge.full {
    background: var(--red-soft);
    color: var(--red);
}

.pen-stat-badge.limited {
    background: var(--orange-soft);
    color: var(--orange);
}

.pen-stat-badge.open {
    background: var(--green-soft);
    color: var(--green);
}
@endsection

@section('content')
    <div class="panel-card">
        <div class="section-title">
            <div>
                <h3>Saved Pens</h3>
                <p>Track housing classification, capacity, and current occupancy.</p>
            </div>
        </div>

        @if ($pens->isEmpty())
            <div class="empty-state">
                No pens found.
            </div>
        @else
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Pen</th>
                            <th>Classification</th>
                            <th>Capacity</th>
                            <th>Occupied</th>
                            <th>Available</th>
                            <th>Occupancy Status</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pens as $pen)
                            @php
                                $occupied = (int) $pen->pigs_count;
                                $available = $pen->availableSlots();
                                $occupancyStatus = $pen->occupancyStatus();
                                $occupancyPercent = $pen->occupancyPercent();
                            @endphp
                            <tr>
                                <td>{{ $pen->id }}</td>
                                <td>
                                    <div class="pen-meta">
                                        <strong>{{ $pen->name }}</strong>
                                        <span class="text-muted">{{ number_format($occupancyPercent, 0) }}% occupied</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $pen->typeBadgeClass() }}">
                                        {{ $pen->type }}
                                    </span>
                                </td>
                                <td>{{ $pen->capacity }}</td>
                                <td>{{ $occupied }}</td>
                                <td>{{ $available }}</td>
                                <td>
                                    <span class="pen-stat-badge {{ $occupancyStatus }}">
                                        {{ ucfirst($occupancyStatus) }}
                                    </span>
                                </td>
                                <td>{{ $pen->notes ?: '—' }}</td>
                                <td>
                                    <div style="display:grid; gap:8px;">
                                        <button
                                            type="button"
                                            class="btn"
                                            onclick="openPenEditPrompt('{{ route('pens.edit', $pen) }}')"
                                        >
                                            Edit
                                        </button>

                                        <button
                                            type="button"
                                            class="btn btn-danger"
                                            onclick="togglePenDelete('pen-delete-{{ $pen->id }}')"
                                        >
                                            Delete
                                        </button>

                                        <form
                                            id="pen-delete-{{ $pen->id }}"
                                            method="POST"
                                            action="{{ route('pens.destroy', $pen) }}"
                                            style="display:none; gap:8px;"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <input type="text" name="confirm_code" placeholder="type DELETE" required>
                                            <button type="submit" class="btn btn-danger">Confirm Delete</button>
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
function openPenEditPrompt(url) {
    const code = prompt('Type the edit access code to continue:');
    if (code === null) return;
    if (code !== '12345') {
        alert('Wrong access code.');
        return;
    }
    window.location.href = url + '?code=' + encodeURIComponent(code);
}

function togglePenDelete(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.style.display = el.style.display === 'none' ? 'grid' : 'none';
}
@endsection
