@extends('layouts.app')

@section('title', 'Pens')
@section('page_title', 'Pen List')
@section('page_subtitle', 'View all housing pens in the system.')

@section('top_actions')
    <div class="pen-view-actions">
        <button id="penSimpleViewButton" type="button" class="btn pen-view-toggle" onclick="setView('simple')">Simple View</button>
        <button id="penGridViewButton" type="button" class="btn pen-view-toggle" onclick="setView('grid')">Card View</button>
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
    margin-bottom: 18px;
    padding: 0;
    overflow: hidden;
    border-color: #dbe4f0;
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.055);
}

.pen-group::before {
    height: 3px;
    background: linear-gradient(90deg, var(--accent), rgba(37, 99, 235, 0.18), transparent);
}

.pen-group summary {
    list-style: none;
    cursor: pointer;
    padding: 18px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 18px;
    border-bottom: 1px solid transparent;
    background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
}

.pen-group summary::-webkit-details-marker {
    display: none;
}

.pen-group[open] summary {
    border-bottom-color: #e2e8f0;
}

.pen-category-title {
    display: grid;
    gap: 4px;
}

.pen-category-title h3 {
    font-size: 19px;
    letter-spacing: -0.02em;
}

.pen-category-title p {
    color: var(--muted);
    font-size: 13px;
    line-height: 1.45;
}

.pen-category-preview {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-end;
    gap: 8px;
}

.pen-preview-pill {
    border: 1px solid #dbe4f0;
    background: #f8fbff;
    color: var(--muted);
    border-radius: 999px;
    padding: 7px 10px;
    font-size: 12px;
    font-weight: 800;
    white-space: nowrap;
}

.pen-preview-pill.strong {
    color: var(--accent);
    background: #eff6ff;
    border-color: #bfdbfe;
}

.pen-group-body {
    padding: 18px;
}

.pen-group-toggle {
    border: 1px solid var(--line);
    border-radius: 999px;
    padding: 7px 12px;
    font-size: 12px;
    font-weight: 800;
    color: var(--accent);
    background: #fff;
}

.pen-group summary .pen-group-toggle::after {
    content: "View";
}

.pen-group[open] summary .pen-group-toggle::after {
    content: "Hide";
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

.pen-card-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
}

.pen-simple-card {
    border-radius: 16px;
    border: 1px solid var(--line);
    padding: 14px;
    min-height: 166px;
    display: grid;
    gap: 10px;
}


.pen-simple-card-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 10px;
}

.pen-simple-card-name {
    font-weight: 800;
    font-size: 15px;
    line-height: 1.2;
}

.pen-simple-card-type {
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

.breeding-status-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    align-items: center;
}

.breeding-status-badges .badge {
    font-size: 11px;
    white-space: nowrap;
}

.badge.gray {
    background: #f1f5f9;
    color: #475569;
}










.hidden {
    display: none;
}

@media (max-width: 1200px) {
    .pen-grid,
    .pen-card-grid,
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
    .pen-card-grid,
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

    .pen-simple-card {
        min-height: auto;
    }

    .pen-simple-card-top,
    .pen-card-top {
        display: grid;
        grid-template-columns: 1fr;
    }

    .pen-group summary {
        display: grid;
        grid-template-columns: 1fr;
        padding: 16px;
    }

    .pen-category-preview {
        justify-content: flex-start;
    }

    .pen-group-body {
        padding: 14px;
    }

    .pen-simple-card .btn,
    .pen-simple-card form,
    .pen-simple-card input,
    .pen-simple-card button,
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
                <h3>Pen Overview</h3>
                <p>Quick view of pens, capacity, and current pigs.</p>
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

            @php
                $displayType = \App\Models\Pen::displayTypeLabel($type);
                $categoryPenCount = $typePens->count();
                $categoryActivePens = $typePens->filter(fn ($pen) => $pen->occupiedCount() > 0)->count();
                $categoryPigCount = $typePens->sum(fn ($pen) => $pen->occupiedCount());
                $categoryCapacity = $typePens->sum(fn ($pen) => (int) $pen->capacity);
            @endphp

            <details class="panel-card pen-group" {{ $typePens->isNotEmpty() ? 'open' : '' }}>
                <summary>
                    <div class="pen-category-title">
                        <h3>{{ $displayType }}</h3>
                        <p>{{ $categoryActivePens }} active pen(s) • {{ $categoryPigCount }} total pig(s) in {{ strtolower($displayType) }} • {{ $categoryCapacity }} total slot(s)</p>
                    </div>

                    <div class="pen-category-preview">
                        <span class="pen-preview-pill strong">{{ $categoryPenCount }} pen(s)</span>
                        <span class="pen-preview-pill">{{ $categoryPigCount }} pig(s)</span>
                        <span class="pen-preview-pill">{{ $categoryCapacity }} capacity</span>
                        <span class="pen-group-toggle"></span>
                    </div>
                </summary>

                <div class="pen-group-body">
                    @if ($typePens->isEmpty())
                        <div class="empty-state">No {{ strtolower($displayType) }} pens yet.</div>
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
                                    <th>Breeding / Heat Status</th>
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
                                        $femaleBreedingPigs = $pen->activePigs
                                            ->filter(fn ($pig) => strtolower((string) $pig->sex) === 'female')
                                            ->values();
                                    @endphp
                                    <tr>
                                        <td>
                                            <div style="display:grid; gap:4px;">
                                                <strong>{{ $pen->name }}</strong>
                                            </div>
                                        </td>
                                        <td>{{ $pen->capacity }}</td>
                                        <td>{{ $occupied }}</td>
                                        <td>{{ $available }}</td>
                                        <td>
                                            <span class="pen-status-pill {{ $status }}">{{ ucfirst($status) }}</span>
                                        </td>
                                        <td>
                                            <div class="breeding-status-badges">
                                                @if ($femaleBreedingPigs->isEmpty())
                                                    <span class="text-muted">—</span>
                                                @else
                                                    @foreach ($femaleBreedingPigs->take(3) as $pig)
                                                        <span class="badge {{ $pig->breeding_status_badge_class }}">
                                                            {{ $pig->ear_tag }}: {{ $pig->breeding_status_label }}
                                                        </span>
                                                    @endforeach

                                                    @if ($femaleBreedingPigs->count() > 3)
                                                        <span class="badge gray">+{{ $femaleBreedingPigs->count() - 3 }} more</span>
                                                    @endif
                                                @endif
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
            </details>
        @endforeach
    </div>

    <div id="gridView" class="hidden">

        @foreach ($penTypes as $type)
            @php
                $typePens = $penGroups[$type] ?? collect();
            @endphp

            @php
                $displayType = \App\Models\Pen::displayTypeLabel($type);
                $categoryPenCount = $typePens->count();
                $categoryActivePens = $typePens->filter(fn ($pen) => $pen->occupiedCount() > 0)->count();
                $categoryPigCount = $typePens->sum(fn ($pen) => $pen->occupiedCount());
                $categoryCapacity = $typePens->sum(fn ($pen) => (int) $pen->capacity);
            @endphp

            <details class="panel-card pen-group" {{ $typePens->isNotEmpty() ? 'open' : '' }}>
                <summary>
                    <div class="pen-category-title">
                        <h3>{{ $displayType }}</h3>
                        <p>{{ $categoryActivePens }} active pen(s) • {{ $categoryPigCount }} total pig(s) in {{ strtolower($displayType) }} • {{ $categoryCapacity }} total slot(s)</p>
                    </div>

                    <div class="pen-category-preview">
                        <span class="pen-preview-pill strong">{{ $categoryPenCount }} pen(s)</span>
                        <span class="pen-preview-pill">{{ $categoryPigCount }} pig(s)</span>
                        <span class="pen-preview-pill">{{ $categoryCapacity }} capacity</span>
                        <span class="pen-group-toggle"></span>
                    </div>
                </summary>

                <div class="pen-group-body">
                    @if ($typePens->isEmpty())
                        <div class="empty-state">No {{ strtolower($displayType) }} pens yet.</div>
                    @else
                        <div class="pen-card-grid">
                        @foreach ($typePens as $pen)
                            @php
                                $occupied = $pen->occupiedCount();
                                $available = $pen->availableSlots();
                                $percent = $pen->occupancyPercent();
                                $status = $pen->occupancyStatus();
                                $femaleBreedingPigs = $pen->activePigs
                                    ->filter(fn ($pig) => strtolower((string) $pig->sex) === 'female')
                                    ->values();
                            @endphp

                            <div class="pen-simple-card">
                                <div class="pen-simple-card-top">
                                    <div style="display:grid; gap:4px;">
                                        <div class="pen-simple-card-name">{{ $pen->name }}</div>
                                        <div class="pen-simple-card-type">{{ $pen->display_type }}</div>
                                    </div>

                                    <span class="pen-status-pill {{ $status }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                </div>

                                <div class="pen-card-meta">
                                    <div><span class="text-muted">Capacity:</span> {{ $pen->capacity }}</div>
                                    <div><span class="text-muted">Occupied:</span> {{ $occupied }}</div>
                                    <div><span class="text-muted">Available:</span> {{ $available }}</div>
                                    <div>
                                        <span class="text-muted">Breeding / Heat:</span>
                                        <div class="breeding-status-badges" style="margin-top:6px;">
                                            @if ($femaleBreedingPigs->isEmpty())
                                                <span class="text-muted">—</span>
                                            @else
                                                @foreach ($femaleBreedingPigs->take(3) as $pig)
                                                    <span class="badge {{ $pig->breeding_status_badge_class }}">
                                                        {{ $pig->ear_tag }}: {{ $pig->breeding_status_label }}
                                                    </span>
                                                @endforeach

                                                @if ($femaleBreedingPigs->count() > 3)
                                                    <span class="badge gray">+{{ $femaleBreedingPigs->count() - 3 }} more</span>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
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
            </details>
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