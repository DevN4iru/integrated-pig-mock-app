@extends('layouts.app')

@section('title', 'Pens')
@section('page_title', 'Pen List')
@section('page_subtitle', 'View all housing pens in the system.')

@section('top_actions')
    <div class="pen-view-actions">
        <button id="penSimpleViewButton" type="button" class="btn pen-view-toggle" onclick="setView('simple')">Simple View</button>
        <button id="penGridViewButton" type="button" class="btn pen-view-toggle" onclick="setView('grid')">Grid View</button>
        <a href="{{ route('pens.create') }}" class="btn primary">Add Pen</a>
    </div>
@endsection

@section('styles')
.pen-view-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
}

.pen-view-toggle.is-active {
    background: #eaf2ff;
    border-color: #bcd4ff;
    color: var(--primary);
}

.pen-dashboard {
    display: grid;
    gap: 20px;
}

.pen-summary-grid {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 16px;
}

.pen-summary-card {
    border: 1px solid var(--line);
    border-radius: 14px;
    padding: 16px;
    background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
}

.pen-summary-label {
    font-size: 12px;
    color: var(--muted);
    margin-bottom: 6px;
    font-weight: 700;
    letter-spacing: 0.02em;
    text-transform: uppercase;
}

.pen-summary-value {
    font-size: 24px;
    font-weight: 800;
    letter-spacing: -0.03em;
}

.pen-summary-sub {
    margin-top: 6px;
    color: var(--muted);
    font-size: 12px;
}

.pen-group {
    margin-bottom: 28px;
}

.pen-card {
    border: 1px solid var(--line);
    border-radius: 14px;
    padding: 14px;
    background: #fff;
    transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
}

.pen-card:hover {
    transform: translateY(-1px);
    box-shadow: var(--shadow-sm);
}

.pen-card-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 10px;
}

.pen-card-meta {
    display: grid;
    gap: 6px;
    margin-top: 10px;
}

.pen-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
}

.pen-heat-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
}

.pen-heat-card {
    border-radius: 16px;
    border: 1px solid var(--line);
    padding: 14px;
    min-height: 166px;
    display: grid;
    gap: 10px;
}

.pen-heat-card.heat-open {
    background: linear-gradient(180deg, #f0fdf4 0%, #ffffff 100%);
    border-color: #bbf7d0;
}

.pen-heat-card.heat-limited {
    background: linear-gradient(180deg, #fff7ed 0%, #ffffff 100%);
    border-color: #fed7aa;
}

.pen-heat-card.heat-full {
    background: linear-gradient(180deg, #fef2f2 0%, #ffffff 100%);
    border-color: #fecaca;
}

.pen-heat-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 10px;
}

.pen-heat-name {
    font-weight: 800;
    font-size: 15px;
    line-height: 1.2;
}

.pen-heat-type {
    color: var(--muted);
    font-size: 12px;
}

.pen-status-pill {
    font-size: 12px;
    padding: 6px 10px;
    border-radius: 999px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}

.pen-status-pill.full {
    background: var(--red-soft);
    color: var(--red);
}

.pen-status-pill.limited {
    background: var(--orange-soft);
    color: var(--orange);
}

.pen-status-pill.open {
    background: var(--green-soft);
    color: var(--green);
}

.pen-progress {
    height: 8px;
    border-radius: 999px;
    background: #e5e7eb;
    overflow: hidden;
    margin-top: 8px;
}

.pen-progress-bar {
    height: 100%;
    border-radius: 999px;
}

.pen-progress.open .pen-progress-bar,
.pen-progress-bar.open {
    background: #22c55e;
}

.pen-progress.limited .pen-progress-bar,
.pen-progress-bar.limited {
    background: #f59e0b;
}

.pen-progress.full .pen-progress-bar,
.pen-progress-bar.full {
    background: #ef4444;
}

.pen-legend {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
}

.pen-legend-item {
    display: inline-flex;
    gap: 8px;
    align-items: center;
    font-size: 12px;
    color: var(--muted);
    font-weight: 700;
}

.pen-legend-dot {
    width: 12px;
    height: 12px;
    border-radius: 999px;
}

.pen-legend-dot.open { background: #22c55e; }
.pen-legend-dot.limited { background: #f59e0b; }
.pen-legend-dot.full { background: #ef4444; }

.hidden {
    display: none;
}

@media (max-width: 1200px) {
    .pen-grid,
    .pen-heat-grid,
    .pen-summary-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 640px) {
    .pen-view-actions {
        display: grid;
        grid-template-columns: 1fr;
        width: 100%;
    }

    .pen-view-actions .btn {
        width: 100%;
        justify-content: center;
    }

    .pen-grid,
    .pen-heat-grid,
    .pen-summary-grid {
        grid-template-columns: 1fr;
    }

    .table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        padding-bottom: 8px;
    }

    .data-table {
        min-width: 760px;
    }

    .data-table th,
    .data-table td {
        white-space: nowrap;
    }

    .pen-heat-card {
        min-height: auto;
    }

    .pen-heat-top,
    .pen-card-top {
        display: grid;
        grid-template-columns: 1fr;
    }

    .pen-heat-card .btn,
    .pen-heat-card form,
    .pen-heat-card input,
    .pen-heat-card button,
    .data-table .btn {
        width: 100%;
    }

    .pen-summary-card {
        padding: 14px;
    }

    .pen-summary-value {
        font-size: 22px;
    }
}
@endsection

@section('content')
<div class="pen-dashboard">

    <div class="panel-card">
        <div class="section-title">
            <div>
                <h3>Pen Occupancy Overview</h3>
                <p>Live occupancy snapshot across all pens based on current assigned pigs.</p>
            </div>
        </div>

        <div class="pen-summary-grid">
            <div class="pen-summary-card">
                <div class="pen-summary-label">Total Pens</div>
                <div class="pen-summary-value">{{ $summary['total_pens'] }}</div>
                <div class="pen-summary-sub">All pen records in the system.</div>
            </div>

            <div class="pen-summary-card">
                <div class="pen-summary-label">Open Pens</div>
                <div class="pen-summary-value">{{ $summary['open'] }}</div>
                <div class="pen-summary-sub">Pens with comfortable available slots.</div>
            </div>

            <div class="pen-summary-card">
                <div class="pen-summary-label">Limited Pens</div>
                <div class="pen-summary-value">{{ $summary['limited'] }}</div>
                <div class="pen-summary-sub">Pens nearing capacity.</div>
            </div>

            <div class="pen-summary-card">
                <div class="pen-summary-label">Full Pens</div>
                <div class="pen-summary-value">{{ $summary['full'] }}</div>
                <div class="pen-summary-sub">Pens with no remaining slots.</div>
            </div>

            <div class="pen-summary-card">
                <div class="pen-summary-label">Farm Occupancy</div>
                <div class="pen-summary-value">
                    {{ $summary['total_capacity'] > 0 ? number_format(($summary['occupied_slots'] / $summary['total_capacity']) * 100, 0) : 0 }}%
                </div>
                <div class="pen-summary-sub">
                    {{ $summary['occupied_slots'] }} / {{ $summary['total_capacity'] }} total slots occupied.
                </div>
            </div>
        </div>
    </div>

    <div id="simpleView">
        @foreach ($penTypes as $type)
            @php
                $typePens = $penGroups[$type] ?? collect();
            @endphp

            <div class="panel-card pen-group">
                <div class="section-title">
                    <div>
                        <h3>{{ $type }}</h3>
                        <p>Pens classified under {{ strtolower($type) }}.</p>
                    </div>
                </div>

                @if ($typePens->isEmpty())
                    <div class="empty-state">No {{ strtolower($type) }} pens yet.</div>
                @else
                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Pen</th>
                                    <th>Capacity</th>
                                    <th>Occupied</th>
                                    <th>Available</th>
                                    <th>Status</th>
                                    <th>Heat</th>
                                    <th>Notes</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($typePens as $pen)
                                    @php
                                        $occupied = $pen->occupiedCount();
                                        $available = $pen->availableSlots();
                                        $status = $pen->occupancyStatus();
                                        $occupancyPercent = $pen->occupancyPercent();
                                    @endphp
                                    <tr>
                                        <td>
                                            <div style="display:grid; gap:4px;">
                                                <strong>{{ $pen->name }}</strong>
                                                <span class="text-muted">{{ number_format($occupancyPercent, 0) }}% occupied</span>
                                            </div>
                                        </td>
                                        <td>{{ $pen->capacity }}</td>
                                        <td>{{ $occupied }}</td>
                                        <td>{{ $available }}</td>
                                        <td>
                                            <span class="pen-status-pill {{ $status }}">{{ ucfirst($status) }}</span>
                                        </td>
                                        <td style="min-width:150px;">
                                            <div class="pen-progress {{ $status }}">
                                                <div class="pen-progress-bar {{ $status }}" style="width: {{ $occupancyPercent }}%"></div>
                                            </div>
                                        </td>
                                        <td>{{ $pen->notes ?: '—' }}</td>
                                        <td>
                                            <div style="display:grid; gap:8px;">
                                                <a href="{{ route('pens.show', $pen) }}" class="btn">Go to Pen</a>

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
                                                    <input type="text" name="confirm_code" placeholder="DELETE" required>
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
        @endforeach
    </div>

    <div id="gridView" class="hidden">
        <div class="panel-card" style="margin-bottom: 22px;">
            <div class="section-title">
                <div>
                    <h3>Heatmap Legend</h3>
                    <p>Green = open, orange = near full, red = full.</p>
                </div>
            </div>

            <div class="pen-legend">
                <span class="pen-legend-item"><span class="pen-legend-dot open"></span> Open</span>
                <span class="pen-legend-item"><span class="pen-legend-dot limited"></span> Limited</span>
                <span class="pen-legend-item"><span class="pen-legend-dot full"></span> Full</span>
            </div>
        </div>

        @foreach ($penTypes as $type)
            @php
                $typePens = $penGroups[$type] ?? collect();
            @endphp

            <div class="panel-card pen-group">
                <div class="section-title">
                    <div>
                        <h3>{{ $type }}</h3>
                        <p>Heatmap grid for {{ strtolower($type) }} pens.</p>
                    </div>
                </div>

                @if ($typePens->isEmpty())
                    <div class="empty-state">No {{ strtolower($type) }} pens yet.</div>
                @else
                    <div class="pen-heat-grid">
                        @foreach ($typePens as $pen)
                            @php
                                $occupied = $pen->occupiedCount();
                                $available = $pen->availableSlots();
                                $percent = $pen->occupancyPercent();
                                $status = $pen->occupancyStatus();
                            @endphp

                            <div class="pen-heat-card {{ $pen->heatClass() }}">
                                <div class="pen-heat-top">
                                    <div style="display:grid; gap:4px;">
                                        <div class="pen-heat-name">{{ $pen->name }}</div>
                                        <div class="pen-heat-type">{{ $pen->type }}</div>
                                    </div>

                                    <span class="pen-status-pill {{ $status }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                </div>

                                <div class="pen-card-meta">
                                    <div><span class="text-muted">Capacity:</span> {{ $pen->capacity }}</div>
                                    <div><span class="text-muted">Occupied:</span> {{ $occupied }}</div>
                                    <div><span class="text-muted">Available:</span> {{ $available }}</div>
                                    <div><span class="text-muted">Usage:</span> {{ number_format($percent, 0) }}%</div>
                                </div>

                                <div class="pen-progress {{ $status }}">
                                    <div class="pen-progress-bar {{ $status }}" style="width: {{ $percent }}%"></div>
                                </div>

                                <div style="margin-top: 4px; display:flex; gap:8px; flex-wrap:wrap;">
                                    <a href="{{ route('pens.show', $pen) }}" class="btn">Go to Pen</a>

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
                                        onclick="togglePenDelete('grid-pen-delete-{{ $pen->id }}')"
                                    >
                                        Delete
                                    </button>
                                </div>

                                <form
                                    id="grid-pen-delete-{{ $pen->id }}"
                                    method="POST"
                                    action="{{ route('pens.destroy', $pen) }}"
                                    style="display:none; gap:8px; margin-top:10px;"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <input type="text" name="confirm_code" placeholder="DELETE" required>
                                    <button type="submit" class="btn btn-danger">Confirm Delete</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>

</div>
@endsection

@section('scripts')
function setView(type) {
    const simpleView = document.getElementById('simpleView');
    const gridView = document.getElementById('gridView');
    const simpleButton = document.getElementById('penSimpleViewButton');
    const gridButton = document.getElementById('penGridViewButton');

    if (!simpleView || !gridView) return;

    simpleView.classList.toggle('hidden', type !== 'simple');
    gridView.classList.toggle('hidden', type !== 'grid');

    if (simpleButton) {
        simpleButton.classList.toggle('is-active', type === 'simple');
    }

    if (gridButton) {
        gridButton.classList.toggle('is-active', type === 'grid');
    }

    localStorage.setItem('penViewMode', type);
}

function openPenEditPrompt(url) {
    const code = prompt('Type the edit access code to continue:');
    if (code === null) return;
    if (code !== '12345') {
        alert('Wrong code');
        return;
    }
    window.location.href = url + '?code=' + encodeURIComponent(code);
}

function togglePenDelete(id) {
    const el = document.getElementById(id);
    if (!el) return;

    el.style.display = el.style.display === 'none' ? 'grid' : 'none';
}

(function restorePenView() {
    const saved = localStorage.getItem('penViewMode');
    setView(saved === 'grid' ? 'grid' : 'simple');
})();
@endsection