@extends('layouts.app')

@section('title', 'Pigs')
@section('page_title', 'Pig List')
@section('page_subtitle', 'View active, sold, dead, and archived pig records.')

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

.batch-panel {
    margin-top: 20px;
}

.batch-toolbar {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
}

.batch-hidden {
    display: none;
}

.batch-grid {
    display: grid;
    gap: 16px;
}

.batch-info-box {
    background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
    border: 1px solid var(--line);
    border-radius: 14px;
    padding: 14px;
}

.inline-note {
    color: var(--muted);
    font-size: 13px;
    margin-top: 6px;
}

.pen-group-stack {
    display: grid;
    gap: 18px;
}

.pen-group-card {
    display: grid;
    gap: 14px;
}

.pen-group-meta {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    align-items: center;
}

.pen-group-sub {
    color: var(--muted);
    font-size: 13px;
}

.inline-actions {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
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

        if (!$latest) {
            return true;
        }

        return \Carbon\Carbon::parse($latest->log_date)->diffInDays(now()) > 7;
    };

    $showActiveSection = in_array($status, ['all', 'active'], true);
    $showSoldSection = in_array($status, ['all', 'sold'], true);
    $showDeadSection = in_array($status, ['all', 'dead'], true);
    $showArchivedSection = in_array($status, ['all', 'archived'], true);
@endphp

<div class="panel-card">
    <div class="section-title">
        <div>
            <h3>Search & Filters</h3>
            <p>Quickly locate pigs and filter by operational status, source, and assigned pen.</p>
        </div>
    </div>

    <form method="GET" action="{{ route('pigs.index') }}">
        <div class="form-grid">
            <div class="form-group">
                <label>Search</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Ear tag, breed, age, pen...">
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

            <div class="form-group">
                <label>Pen</label>
                <select name="pen">
                    <option value="all" {{ $penFilter === 'all' ? 'selected' : '' }}>All Pens</option>
                    @foreach ($pensForFilter as $pen)
                        <option value="{{ $pen->id }}" {{ (string) $penFilter === (string) $pen->id ? 'selected' : '' }}>
                            {{ $pen->name }} — {{ $pen->type }}
                        </option>
                    @endforeach
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

@if ($showActiveSection)
<div class="panel-card batch-panel">
    <div class="section-title">
        <div>
            <h3>Batch Actions</h3>
            <p>Select active pigs below and perform one transfer or one sale action in bulk.</p>
        </div>
    </div>

    <div class="batch-toolbar">
        <button type="button" class="btn" onclick="toggleAllPigSelection(true)">Select All Visible</button>
        <button type="button" class="btn" onclick="toggleAllPigSelection(false)">Clear Selection</button>
        <button type="button" class="btn primary" onclick="showBatchTransfer()">Batch Transfer</button>
        <button type="button" class="btn btn-warning" onclick="showBatchSale()">Batch Sell</button>
        <span class="text-muted" id="selectedCountText">0 pig(s) selected</span>
    </div>

    <div id="batchTransferPanel" class="batch-hidden" style="margin-top: 18px;">
        <div class="batch-info-box">
            <form method="POST" action="{{ route('pig-transfers.batch') }}">
                @csrf
                <input type="hidden" name="pig_ids" id="batchTransferPigIds" value="{{ old('pig_ids') }}">

                <div class="batch-grid">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="batch_transfer_to_pen_id">Destination Pen</label>
                            <select id="batch_transfer_to_pen_id" name="to_pen_id" required>
                                <option value="">Select destination pen</option>
                                @foreach ($destinationPens->groupBy('type') as $type => $pensByType)
                                    <optgroup label="{{ strtoupper($type) }}">
                                        @foreach ($pensByType as $pen)
                                            @php
                                                $isFull = $pen->pigs_count >= $pen->capacity;
                                                $remaining = $pen->capacity - $pen->pigs_count;
                                                $label = "{$pen->name} ({$pen->pigs_count}/{$pen->capacity})";
                                                if ($isFull) {
                                                    $label .= ' - FULL';
                                                } elseif ($remaining <= 2) {
                                                    $label .= ' - NEAR FULL';
                                                }
                                            @endphp
                                            <option value="{{ $pen->id }}" {{ old('to_pen_id') == $pen->id ? 'selected' : '' }} {{ $isFull ? 'disabled' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            <div class="inline-note">Whole batch fails if the destination pen cannot fit every selected pig.</div>
                        </div>

                        <div class="form-group">
                            <label for="batch_transfer_date">Transfer Date</label>
                            <input id="batch_transfer_date" type="date" name="transfer_date" value="{{ old('transfer_date', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required>
                        </div>

                        <div class="form-group">
                            <label for="batch_transfer_reason_code">Transfer Reason</label>
                            <select id="batch_transfer_reason_code" name="reason_code" required onchange="toggleBatchTransferOther()">
                                <option value="">Select reason</option>
                                @foreach ($reasonOptions as $value => $label)
                                    <option value="{{ $value }}" {{ old('reason_code') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group full" id="batchTransferReasonNotesGroup">
                            <label for="batch_transfer_reason_notes">Reason Notes</label>
                            <textarea id="batch_transfer_reason_notes" name="reason_notes" placeholder="Add transfer note. This becomes required when Other is selected.">{{ old('reason_notes') }}</textarea>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn primary" onclick="return syncBatchTransferPigIds()">Confirm Batch Transfer</button>
                        <button type="button" class="btn" onclick="hideBatchPanels()">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div id="batchSalePanel" class="batch-hidden" style="margin-top: 18px;">
        <div class="batch-info-box">
            <form method="POST" action="{{ route('sales.batch') }}">
                @csrf
                <input type="hidden" name="pig_ids" id="batchSalePigIds" value="{{ old('pig_ids') }}">

                <div class="batch-grid">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Current Global Price per kg</label>
                            <input type="text" value="₱ {{ number_format((float) $pricePerKg, 2) }}" readonly>
                        </div>

                        <div class="form-group">
                            <label>Recommended Pricing Logic</label>
                            <input type="text" value="Current Weight × Current Global Price per kg" readonly>
                            <div class="inline-note">Recommended prices are computed live for active pigs. Saved sale prices are historically locked once recorded.</div>
                        </div>

                        <div class="form-group">
                            <label>Recommended Total for Current Selection</label>
                            <input type="text" id="batchSaleRecommendedTotal" value="₱ 0.00" readonly>
                        </div>

                        <div class="form-group">
                            <label for="batch_sale_pricing_mode">Pricing Mode</label>
                            <select id="batch_sale_pricing_mode" name="pricing_mode" required onchange="toggleBatchSalePricingMode()">
                                <option value="recommended" {{ old('pricing_mode', 'recommended') === 'recommended' ? 'selected' : '' }}>Use recommended price per pig</option>
                                <option value="custom" {{ old('pricing_mode') === 'custom' ? 'selected' : '' }}>Use one custom price for every selected pig</option>
                            </select>
                        </div>

                        <div class="form-group" id="batchSaleCustomPriceGroup">
                            <label for="batch_sale_custom_price">Custom Price per Pig</label>
                            <input id="batch_sale_custom_price" type="number" step="0.01" min="0" name="custom_price" value="{{ old('custom_price') }}">
                        </div>

                        <div class="form-group">
                            <label for="batch_sale_sold_date">Sold Date</label>
                            <input id="batch_sale_sold_date" type="date" name="sold_date" value="{{ old('sold_date', now()->toDateString()) }}" required>
                        </div>

                        <div class="form-group">
                            <label for="batch_sale_buyer">Buyer</label>
                            <input id="batch_sale_buyer" type="text" name="buyer" value="{{ old('buyer') }}">
                        </div>

                        <div class="form-group full">
                            <label for="batch_sale_notes">Notes</label>
                            <textarea id="batch_sale_notes" name="notes">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-warning" onclick="return syncBatchSalePigIds()">Confirm Batch Sale</button>
                        <button type="button" class="btn" onclick="hideBatchPanels()">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@if ($showActiveSection)
<div class="pen-group-stack">
    <div class="panel-card">
        <div class="section-title">
            <div>
                <h3>Active Pigs by Pen</h3>
                <p>Live pigs are now grouped by assigned pen for easier housing operations.</p>
            </div>
        </div>

        @if ($activePenGroups->isEmpty())
            <div class="empty-state">No active pigs match the current filters.</div>
        @else
            <div class="pen-group-stack">
                @foreach ($activePenGroups as $group)
                    @php
                        $pen = $group['pen'];
                    @endphp

                    <div class="panel-card pen-group-card">
                        <div class="pen-group-meta">
                            <div>
                                <h3 style="margin-bottom: 4px;">{{ $group['title'] }}</h3>
                                <div class="pen-group-sub">
                                    @if ($pen)
                                        {{ $group['type'] }} • {{ $pen->occupiedCount() }}/{{ $pen->capacity }} occupied
                                    @else
                                        No assigned pen
                                    @endif
                                </div>
                            </div>

                            <div class="inline-actions">
                                @if ($pen)
                                    <a href="{{ route('pens.show', $pen) }}" class="btn">Go to Pen</a>
                                @endif
                            </div>
                        </div>

                        <div class="table-wrap">
                            <table class="data-table pig-table">
                                <thead>
                                    <tr>
                                        <th style="width:44px;">
                                            <input type="checkbox" class="batch-group-toggle" data-group-index="{{ $loop->index }}">
                                        </th>
                                        <th>Ear Tag</th>
                                        <th>Breed</th>
                                        <th>Age</th>
                                        <th>Source</th>
                                        <th>Weight</th>
                                        <th>Trend</th>
                                        <th>Value</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($group['pigs'] as $pig)
                                        @php
                                            $trend = $buildTrend($pig);
                                            $stale = $isStale($pig);

                                            $latestLog = $pig->healthLogs
                                                ->whereNotNull('weight')
                                                ->sortByDesc(fn ($log) => sprintf('%s-%010d', (string) ($log->log_date ?? ''), (int) $log->id))
                                                ->first();

                                            $displayWeight = $latestLog?->weight ?? $pig->latest_weight;
                                            $recommendedValue = (float) $pig->computed_asset_value;
                                        @endphp

                                        <tr>
                                            <td>
                                                <input
                                                    type="checkbox"
                                                    class="batch-pig-checkbox batch-group-{{ $loop->parent->index }}"
                                                    value="{{ $pig->id }}"
                                                    data-recommended="{{ $recommendedValue }}"
                                                >
                                            </td>

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
                                            <td>{{ $pig->age_display }}</td>
                                            <td>{{ ucfirst($pig->pig_source) }}</td>
                                            <td>{{ $displayWeight ? number_format((float) $displayWeight, 2) . ' kg' : '—' }}</td>
                                            <td class="{{ $trend['class'] }}">{{ $trend['symbol'] }}</td>
                                            <td>₱ {{ number_format((float) $pig->computed_asset_value, 2) }}</td>
                                            <td>
                                                <div class="inline-actions">
                                                    <a href="{{ route('pigs.show', $pig->id) }}" class="btn">View</a>
                                                    @if ($pig->pen)
                                                        <a href="{{ route('pens.show', $pig->pen) }}" class="btn">Pen</a>
                                                    @endif
                                                    <button class="btn" onclick="openPigEditPrompt('{{ route('pigs.edit', $pig) }}')" type="button">Edit</button>

                                                    <form method="POST" action="{{ route('pigs.destroy', $pig->id) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-warning" type="submit">Archive</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endif

@if ($showSoldSection)
<div class="panel-card" style="margin-top: 20px;">
    <div class="section-title">
        <div>
            <h3>Sold Pigs</h3>
            <p>Completed sale records. Removing from records permanently deletes the pig and all related records and affects dashboard totals.</p>
        </div>
    </div>

    @if ($soldPigs->isEmpty())
        <div class="empty-state">No sold pigs match the current filters.</div>
    @else
        <div class="table-wrap">
            <table class="data-table pig-table">
                <thead>
                    <tr>
                        <th>Ear Tag</th>
                        <th>Breed</th>
                        <th>Age</th>
                        <th>Pen</th>
                        <th>Latest Weight</th>
                        <th>Latest Sale Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($soldPigs as $pig)
                        @php
                            $latestSale = $pig->sales->sortByDesc('sold_date')->first();
                        @endphp
                        <tr>
                            <td>{{ $pig->ear_tag }}</td>
                            <td>{{ $pig->breed }}</td>
                            <td>{{ $pig->age_display }}</td>
                            <td>{{ $pig->pen?->name ?? $pig->pen_location ?? '—' }}</td>
                            <td>{{ $pig->computed_weight !== null ? number_format((float) $pig->computed_weight, 2) . ' kg' : '—' }}</td>
                            <td>{{ $latestSale ? '₱ ' . number_format((float) $latestSale->price, 2) : '—' }}</td>
                            <td>
                                <div class="inline-actions">
                                    <a href="{{ route('pigs.show', $pig) }}" class="btn">View</a>
                                    <button type="button" class="btn btn-danger" onclick="confirmPigRemoveFromRecords('{{ route('pigs.remove-records', $pig) }}')">
                                        Remove from Records
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
@endif

@if ($showDeadSection)
<div class="panel-card" style="margin-top: 20px;">
    <div class="section-title">
        <div>
            <h3>Dead Pigs</h3>
            <p>Mortality records remain separated from live operations. Removing from records permanently deletes the pig and all related records.</p>
        </div>
    </div>

    @if ($deadPigs->isEmpty())
        <div class="empty-state">No dead pigs match the current filters.</div>
    @else
        <div class="table-wrap">
            <table class="data-table pig-table">
                <thead>
                    <tr>
                        <th>Ear Tag</th>
                        <th>Breed</th>
                        <th>Age</th>
                        <th>Pen</th>
                        <th>Latest Weight</th>
                        <th>Mortality Cause</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($deadPigs as $pig)
                        @php
                            $latestMortality = $pig->mortalityLogs->sortByDesc('death_date')->first();
                        @endphp
                        <tr>
                            <td>{{ $pig->ear_tag }}</td>
                            <td>{{ $pig->breed }}</td>
                            <td>{{ $pig->age_display }}</td>
                            <td>{{ $pig->pen?->name ?? $pig->pen_location ?? '—' }}</td>
                            <td>{{ $pig->computed_weight !== null ? number_format((float) $pig->computed_weight, 2) . ' kg' : '—' }}</td>
                            <td>{{ $latestMortality?->cause ?? '—' }}</td>
                            <td>
                                <div class="inline-actions">
                                    <a href="{{ route('pigs.show', $pig) }}" class="btn">View</a>
                                    <button type="button" class="btn btn-danger" onclick="confirmPigRemoveFromRecords('{{ route('pigs.remove-records', $pig) }}')">
                                        Remove from Records
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
@endif

@if ($showArchivedSection)
<div class="panel-card" style="margin-top: 20px;">
    <div class="section-title">
        <div>
            <h3>Archived Pigs</h3>
            <p>Archived pigs no longer count as active and do not occupy pen capacity.</p>
        </div>
    </div>

    @if ($archivedPigs->isEmpty())
        <div class="empty-state">No archived pigs match the current filters.</div>
    @else
        <div class="table-wrap">
            <table class="data-table pig-table">
                <thead>
                    <tr>
                        <th>Ear Tag</th>
                        <th>Breed</th>
                        <th>Age</th>
                        <th>Pen</th>
                        <th>Source</th>
                        <th>Value</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($archivedPigs as $pig)
                        <tr>
                            <td>{{ $pig->ear_tag }}</td>
                            <td>{{ $pig->breed }}</td>
                            <td>{{ $pig->age_display }}</td>
                            <td>{{ $pig->pen?->name ?? $pig->pen_location ?? '—' }}</td>
                            <td>{{ ucfirst($pig->pig_source) }}</td>
                            <td>₱ {{ number_format((float) $pig->computed_asset_value, 2) }}</td>
                            <td>
                                <div class="inline-actions">
                                    <a href="{{ route('pigs.show', $pig) }}" class="btn">View</a>

                                    <form method="POST" action="{{ route('pigs.restore', $pig) }}">
                                        @csrf
                                        <button class="btn" type="submit">Restore</button>
                                    </form>

                                    <button type="button" class="btn btn-danger" onclick="confirmPigPermanentDelete('{{ route('pigs.force-delete', $pig) }}')">
                                        Permanent Delete
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
@endif
@endsection

@section('scripts')
function getSelectedPigCheckboxes() {
    return Array.from(document.querySelectorAll('.batch-pig-checkbox'))
        .filter((checkbox) => checkbox.checked);
}

function updateSelectedCount() {
    const selected = getSelectedPigCheckboxes();
    const countText = document.getElementById('selectedCountText');

    if (countText) {
        countText.textContent = `${selected.length} pig(s) selected`;
    }

    updateBatchSaleRecommendedTotal();
}

function toggleAllPigSelection(checked) {
    document.querySelectorAll('.batch-pig-checkbox').forEach((checkbox) => {
        checkbox.checked = checked;
    });

    const headerToggle = document.getElementById('toggleAllActivePigs');
    if (headerToggle) {
        headerToggle.checked = checked;
    }

    document.querySelectorAll('.batch-group-toggle').forEach((checkbox) => {
        checkbox.checked = checked;
    });

    updateSelectedCount();
}

function toggleAllFromHeader(checked) {
    toggleAllPigSelection(checked);
}

document.querySelectorAll('.batch-group-toggle').forEach((toggle) => {
    toggle.addEventListener('change', function () {
        const groupIndex = this.dataset.groupIndex;
        document.querySelectorAll(`.batch-group-${groupIndex}`).forEach((checkbox) => {
            checkbox.checked = this.checked;
        });
        updateSelectedCount();
    });
});

document.querySelectorAll('.batch-pig-checkbox').forEach((checkbox) => {
    checkbox.addEventListener('change', updateSelectedCount);
});

function syncBatchTransferPigIds() {
    const selected = getSelectedPigCheckboxes().map((checkbox) => checkbox.value);

    if (selected.length === 0) {
        alert('Select at least one active pig first.');
        return false;
    }

    document.getElementById('batchTransferPigIds').value = selected.join(',');
    return true;
}

function syncBatchSalePigIds() {
    const selected = getSelectedPigCheckboxes().map((checkbox) => checkbox.value);

    if (selected.length === 0) {
        alert('Select at least one active pig first.');
        return false;
    }

    document.getElementById('batchSalePigIds').value = selected.join(',');
    return true;
}

function showBatchTransfer() {
    document.getElementById('batchTransferPanel').style.display = 'block';
    document.getElementById('batchSalePanel').style.display = 'none';
    toggleBatchTransferOther();
}

function showBatchSale() {
    document.getElementById('batchSalePanel').style.display = 'block';
    document.getElementById('batchTransferPanel').style.display = 'none';
    toggleBatchSalePricingMode();
    updateBatchSaleRecommendedTotal();
}

function hideBatchPanels() {
    document.getElementById('batchTransferPanel').style.display = 'none';
    document.getElementById('batchSalePanel').style.display = 'none';
}

function toggleBatchTransferOther() {
    const reason = document.getElementById('batch_transfer_reason_code');
    const notes = document.getElementById('batch_transfer_reason_notes');

    if (!reason || !notes) return;

    notes.required = reason.value === 'other';
}

function toggleBatchSalePricingMode() {
    const mode = document.getElementById('batch_sale_pricing_mode');
    const customGroup = document.getElementById('batchSaleCustomPriceGroup');
    const customInput = document.getElementById('batch_sale_custom_price');

    if (!mode || !customGroup || !customInput) return;

    const showCustom = mode.value === 'custom';
    customGroup.style.display = showCustom ? '' : 'none';
    customInput.required = showCustom;
}

function updateBatchSaleRecommendedTotal() {
    const selected = getSelectedPigCheckboxes();
    const total = selected.reduce((carry, checkbox) => {
        return carry + parseFloat(checkbox.dataset.recommended || '0');
    }, 0);

    const totalInput = document.getElementById('batchSaleRecommendedTotal');
    if (totalInput) {
        totalInput.value = `₱ ${total.toFixed(2)}`;
    }
}

function openPigEditPrompt(url) {
    const code = prompt('Type the edit access code to continue:');
    if (code === null) return;
    if (code !== '12345') {
        alert('Wrong code');
        return;
    }
    window.location.href = url + '?code=' + encodeURIComponent(code);
}

function confirmPigPermanentDelete(url) {
    const code = prompt('Type 12345 to permanently delete this archived pig:');
    if (code === null) return;
    if (code !== '12345') {
        alert('Wrong code');
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = url;

    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_token';
    csrf.value = '{{ csrf_token() }}';

    const method = document.createElement('input');
    method.type = 'hidden';
    method.name = '_method';
    method.value = 'DELETE';

    const codeInput = document.createElement('input');
    codeInput.type = 'hidden';
    codeInput.name = 'code';
    codeInput.value = code;

    form.appendChild(csrf);
    form.appendChild(method);
    form.appendChild(codeInput);
    document.body.appendChild(form);
    form.submit();
}

function confirmPigRemoveFromRecords(url) {
    const code = prompt('Type REMOVE to permanently delete this pig and all related records:');
    if (code === null) return;
    if (code !== 'REMOVE') {
        alert('Wrong code');
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = url;

    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_token';
    csrf.value = '{{ csrf_token() }}';

    const method = document.createElement('input');
    method.type = 'hidden';
    method.name = '_method';
    method.value = 'DELETE';

    const codeInput = document.createElement('input');
    codeInput.type = 'hidden';
    codeInput.name = 'code';
    codeInput.value = code;

    form.appendChild(csrf);
    form.appendChild(method);
    form.appendChild(codeInput);
    document.body.appendChild(form);
    form.submit();
}

toggleBatchTransferOther();
toggleBatchSalePricingMode();
updateSelectedCount();
@endsection