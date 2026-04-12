@extends('layouts.app')

@section('title', 'Pigs')
@section('page_title', 'Pig List')
@section('page_subtitle', 'View active and archived pig records.')

@section('top_actions')
    <a href="{{ route('pigs.create') }}" class="btn primary">+ Add Pig</a>
@endsection

@section('styles')
.pig-table td { vertical-align: middle; }

.pig-meta {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.trend-up { color: #16a34a; font-weight: 700; }
.trend-down { color: #dc2626; font-weight: 700; }
.trend-flat { color: #64748b; font-weight: 700; }

.alert-badge {
    background: #fef3c7;
    color: #92400e;
    padding: 4px 8px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    display: inline-block;
    margin-top: 4px;
}
@endsection

@section('content')

@php
    $buildTrend = function ($pig) {
        $logs = $pig->healthLogs
            ->whereNotNull('weight')
            ->sortByDesc(fn ($l) => sprintf('%s-%010d', (string) ($l->log_date ?? ''), (int) $l->id))
            ->values();

        $latest = $logs->get(0);
        $prev = $logs->get(1);

        if (!$latest || !$prev) {
            return ['symbol' => '—', 'class' => 'trend-flat'];
        }

        if ((float) $latest->weight > (float) $prev->weight) {
            return ['symbol' => '↑', 'class' => 'trend-up'];
        }

        if ((float) $latest->weight < (float) $prev->weight) {
            return ['symbol' => '↓', 'class' => 'trend-down'];
        }

        return ['symbol' => '→', 'class' => 'trend-flat'];
    };

    $isStale = function ($pig) {
        $latest = $pig->healthLogs
            ->whereNotNull('weight')
            ->sortByDesc(fn ($log) => sprintf('%s-%010d', (string) ($log->log_date ?? ''), (int) $log->id))
            ->first();

        if (!$latest) return true;

        return \Carbon\Carbon::parse($latest->log_date)->diffInDays(now()) > 7;
    };
@endphp

<div class="panel-card">
    <div class="section-title">
        <div>
            <h3>Search & Filters</h3>
            <p>Quickly locate pigs and filter by operational status.</p>
        </div>
    </div>

    <form method="GET" action="{{ route('pigs.index') }}">
        <div class="form-grid">
            <div class="form-group">
                <label>Search</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Ear tag, breed, pen...">
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All</option>
                    <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="sold" {{ $status === 'sold' ? 'selected' : '' }}>Sold</option>
                    <option value="dead" {{ $status === 'dead' ? 'selected' : '' }}>Dead</option>
                    <option value="archived" {{ $status === 'archived' ? 'selected' : '' }}>Archived</option>
                </select>
            </div>

            <div class="form-group">
                <label>Source</label>
                <select name="source">
                    <option value="all" {{ $source === 'all' ? 'selected' : '' }}>All</option>
                    <option value="birthed" {{ $source === 'birthed' ? 'selected' : '' }}>Birthed</option>
                    <option value="purchased" {{ $source === 'purchased' ? 'selected' : '' }}>Purchased</option>
                </select>
            </div>

            <div class="form-group full">
                <div class="form-actions">
                    <button type="submit" class="btn primary">Apply</button>
                    <a href="{{ route('pigs.index') }}" class="btn">Reset</a>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="panel-card" style="margin-top: 20px;">
    <div class="section-title">
        <div>
            <h3>Active Pigs</h3>
            <p>Live operational pigs with real-time indicators.</p>
        </div>
    </div>

    @if ($activePigs->isEmpty())
        <div class="empty-state">No active pigs.</div>
    @else
        <div class="table-wrap">
            <table class="data-table pig-table">
                <thead>
                    <tr>
                        <th>Ear Tag</th>
                        <th>Breed</th>
                        <th>Pen</th>
                        <th>Status</th>
                        <th>Weight</th>
                        <th>Trend</th>
                        <th>Value</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($activePigs as $pig)
                        @php
                            $trend = $buildTrend($pig);
                            $stale = $isStale($pig);

                            $isDead = $pig->mortalityLogs->isNotEmpty();
                            $isSold = $pig->sales->isNotEmpty();

                            $statusLabel = $isDead ? 'Dead' : ($isSold ? 'Sold' : 'Active');
                            $statusClass = $isDead ? 'red' : ($isSold ? 'orange' : 'green');

                            // 🔥 FINAL WEIGHT FIX
                            $latestLog = $pig->healthLogs
                                ->whereNotNull('weight')
                                ->sortByDesc(fn ($log) => sprintf('%s-%010d', (string) ($log->log_date ?? ''), (int) $log->id))
                                ->first();

                            $displayWeight = $latestLog?->weight ?? $pig->latest_weight;
                        @endphp

                        <tr>
                            <td>
                                <div class="pig-meta">
                                    <strong>{{ $pig->ear_tag }}</strong>
                                    <span class="text-muted">{{ ucfirst($pig->sex) }}</span>

                                    @if ($stale)
                                        <span class="alert-badge">⚠ No recent weight</span>
                                    @endif
                                </div>
                            </td>

                            <td>{{ $pig->breed }}</td>
                            <td>{{ $pig->pen?->name ?? '—' }}</td>

                            <td>
                                <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                            </td>

                            <td>
                                {{ $displayWeight ? number_format($displayWeight, 2) . ' kg' : '—' }}
                            </td>

                            <td class="{{ $trend['class'] }}">
                                {{ $trend['symbol'] }}
                            </td>

                            <td>₱ {{ number_format((float) $pig->asset_value, 2) }}</td>

                            <td>
                                <div style="display:flex; gap:6px; flex-wrap:wrap;">
                                    <a href="{{ route('pigs.show', $pig->id) }}" class="btn">View</a>

                                    <button class="btn"
                                        onclick="openPigEditPrompt('{{ route('pigs.edit', $pig) }}')">
                                        Edit
                                    </button>

                                    <form method="POST" action="{{ route('pigs.destroy', $pig->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-warning">Archive</button>
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
            <h3>Archived Pigs</h3>
            <p>Stored records (non-operational).</p>
        </div>
    </div>

    @if ($archivedPigs->isEmpty())
        <div class="empty-state">No archived pigs.</div>
    @else
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Ear Tag</th>
                        <th>Breed</th>
                        <th>Weight</th>
                        <th>Value</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($archivedPigs as $pig)
                        @php
                            $latestLog = $pig->healthLogs
                                ->whereNotNull('weight')
                                ->sortByDesc(fn ($log) => sprintf('%s-%010d', (string) ($log->log_date ?? ''), (int) $log->id))
                                ->first();

                            $displayWeight = $latestLog?->weight ?? $pig->latest_weight;
                        @endphp

                        <tr>
                            <td>{{ $pig->ear_tag }}</td>
                            <td>{{ $pig->breed }}</td>
                            <td>{{ number_format((float) $displayWeight, 2) }} kg</td>
                            <td>₱ {{ number_format((float) $pig->asset_value, 2) }}</td>
                            <td>
                                <div style="display:flex; gap:6px;">
                                    <a href="{{ route('pigs.show', $pig->id) }}" class="btn">View</a>

                                    <form method="POST" action="{{ route('pigs.restore', $pig->id) }}">
                                        @csrf
                                        <button class="btn">Restore</button>
                                    </form>

                                    <button class="btn btn-danger"
                                        onclick="confirmPigPermanentDelete('{{ route('pigs.force-delete', $pig->id) }}')">
                                        Delete
                                    </button>
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
    const code = prompt('Enter edit code:');
    if (code === '12345') {
        window.location.href = url + '?code=' + code;
    } else if (code !== null) {
        alert('Wrong code');
    }
}

function confirmPigPermanentDelete(url) {
    const code = prompt('Enter delete code (12345):');
    if (!code) return;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = url;

    form.innerHTML = `
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="_method" value="DELETE">
        <input type="hidden" name="code" value="${code}">
    `;

    document.body.appendChild(form);
    form.submit();
}
@endsection
