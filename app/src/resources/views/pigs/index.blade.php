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
                        <th style="width:44px;">
                            <input type="checkbox" id="toggleAllActivePigs" onclick="toggleAllFromHeader(this.checked)">
                        </th>
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
                                    class="batch-pig-checkbox"
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
                            <td>{{ $pig->pen?->name ?? '—' }}</td>

                            <td>
                                <span class="badge green">Active</span>
                            </td>

                            <td>
                                {{ $displayWeight ? number_format($displayWeight, 2) . ' kg' : '—' }}
                            </td>

                            <td class="{{ $trend['class'] }}">
                                {{ $trend['symbol'] }}
                            </td>

                            <td>₱ {{ number_format((float) $pig->computed_asset_value, 2) }}</td>

                            <td>
                                <div style="display:flex; gap:6px; flex-wrap:wrap;">
                                    <a href="{{ route('pigs.show', $pig->id) }}" class="btn">View</a>

                                    <button class="btn"
                                        onclick="openPigEditPrompt('{{ route('pigs.edit', $pig) }}')"
                                        type="button">
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
        <div class="empty-state">No sold pigs.</div>
    @else
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Ear Tag</th>
                        <th>Breed</th>
                        <th>Sold Date</th>
                        <th>Sold Price</th>
                        <th>Buyer</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($soldPigs as $pig)
                        @php
                            $sale = $pig->sales->sortByDesc('sold_date')->first();
                        @endphp
                        <tr>
                            <td>{{ $pig->ear_tag }}</td>
                            <td>{{ $pig->breed }}</td>
                            <td>{{ $sale?->sold_date ? $sale->sold_date->format('Y-m-d') : '—' }}</td>
                            <td>₱ {{ number_format((float) ($sale->price ?? 0), 2) }}</td>
                            <td>{{ $sale->buyer ?: '—' }}</td>
                            <td>
                                <div style="display:flex; gap:6px; flex-wrap:wrap;">
                                    <a href="{{ route('pigs.show', $pig->id) }}" class="btn">View Profile</a>

                                    @if ($sale)
                                        <a href="{{ route('sales.edit', [$pig->id, $sale->id]) }}" class="btn">Edit Record</a>
                                    @endif

                                    <button class="btn btn-danger"
                                        onclick="confirmRemoveRecords('{{ route('pigs.remove-records', $pig->id) }}')"
                                        type="button">
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
            <p>Mortality records currently classified as dead.</p>
        </div>
    </div>

    @if ($deadPigs->isEmpty())
        <div class="empty-state">No dead pigs.</div>
    @else
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Ear Tag</th>
                        <th>Breed</th>
                        <th>Last Pen</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($deadPigs as $pig)
                        <tr>
                            <td>{{ $pig->ear_tag }}</td>
                            <td>{{ $pig->breed }}</td>
                            <td>{{ $pig->pen?->name ?? $pig->pen_location ?? '—' }}</td>
                            <td>
                                <a href="{{ route('pigs.show', $pig->id) }}" class="btn">View</a>
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
                                        onclick="confirmPigPermanentDelete('{{ route('pigs.force-delete', $pig->id) }}')"
                                        type="button">
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
@endif

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

function confirmRemoveRecords(url) {
    const proceed = confirm(
        'WARNING:\\n\\nThis will permanently remove the pig and ALL related records, including sales, transfers, health logs, feed logs, medication, vaccination, and mortality records.\\n\\nDashboard totals will change.\\n\\nDo you want to continue?'
    );

    if (!proceed) return;

    const code = prompt('Enter REMOVE to permanently remove this pig from all records:');
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

function getSelectedPigIds() {
    return Array.from(document.querySelectorAll('.batch-pig-checkbox:checked')).map(cb => cb.value);
}

function updateSelectedPigCount() {
    const count = getSelectedPigIds().length;
    const selectedCountText = document.getElementById('selectedCountText');

    if (selectedCountText) {
        selectedCountText.textContent = `${count} pig(s) selected`;
    }

    updateRecommendedBatchTotal();
}

function toggleAllPigSelection(checked) {
    document.querySelectorAll('.batch-pig-checkbox').forEach(cb => {
        cb.checked = checked;
    });

    const headerToggle = document.getElementById('toggleAllActivePigs');
    if (headerToggle) {
        headerToggle.checked = checked;
    }

    updateSelectedPigCount();
}

function toggleAllFromHeader(checked) {
    toggleAllPigSelection(checked);
}

function hideBatchPanels() {
    const transferPanel = document.getElementById('batchTransferPanel');
    const salePanel = document.getElementById('batchSalePanel');

    if (transferPanel) {
        transferPanel.classList.add('batch-hidden');
    }

    if (salePanel) {
        salePanel.classList.add('batch-hidden');
    }
}

function showBatchTransfer() {
    const ids = getSelectedPigIds();
    if (!ids.length) {
        alert('Select at least one active pig first.');
        return;
    }

    hideBatchPanels();

    const transferPanel = document.getElementById('batchTransferPanel');
    if (transferPanel) {
        transferPanel.classList.remove('batch-hidden');
    }

    syncBatchTransferPigIds();
    toggleBatchTransferOther();
}

function showBatchSale() {
    const ids = getSelectedPigIds();
    if (!ids.length) {
        alert('Select at least one active pig first.');
        return;
    }

    hideBatchPanels();

    const salePanel = document.getElementById('batchSalePanel');
    if (salePanel) {
        salePanel.classList.remove('batch-hidden');
    }

    syncBatchSalePigIds();
    toggleBatchSalePricingMode();
    updateRecommendedBatchTotal();
}

function syncBatchTransferPigIds() {
    const ids = getSelectedPigIds();
    if (!ids.length) {
        alert('Select at least one active pig first.');
        return false;
    }

    const input = document.getElementById('batchTransferPigIds');
    if (input) {
        input.value = ids.join(',');
    }

    return true;
}

function syncBatchSalePigIds() {
    const ids = getSelectedPigIds();
    if (!ids.length) {
        alert('Select at least one active pig first.');
        return false;
    }

    const input = document.getElementById('batchSalePigIds');
    if (input) {
        input.value = ids.join(',');
    }

    return true;
}

function toggleBatchTransferOther() {
    const reasonSelect = document.getElementById('batch_transfer_reason_code');
    const notesField = document.getElementById('batch_transfer_reason_notes');
    const isOther = reasonSelect && reasonSelect.value === 'other';

    if (notesField) {
        notesField.required = !!isOther;
        notesField.placeholder = isOther
            ? 'Explain the custom transfer reason'
            : 'Optional transfer note';
    }
}

function toggleBatchSalePricingMode() {
    const mode = document.getElementById('batch_sale_pricing_mode');
    const customGroup = document.getElementById('batchSaleCustomPriceGroup');
    const customInput = document.getElementById('batch_sale_custom_price');

    if (!mode || !customGroup || !customInput) return;

    const useCustom = mode.value === 'custom';
    customGroup.style.display = useCustom ? '' : 'none';
    customInput.required = useCustom;
}

function updateRecommendedBatchTotal() {
    const selected = Array.from(document.querySelectorAll('.batch-pig-checkbox:checked'));
    const total = selected.reduce((carry, checkbox) => {
        return carry + parseFloat(checkbox.dataset.recommended || '0');
    }, 0);

    const output = document.getElementById('batchSaleRecommendedTotal');
    if (output) {
        output.value = `₱ ${total.toFixed(2)}`;
    }
}

document.querySelectorAll('.batch-pig-checkbox').forEach(cb => {
    cb.addEventListener('change', updateSelectedPigCount);
});

toggleBatchTransferOther();
toggleBatchSalePricingMode();
updateSelectedPigCount();
@endsection
