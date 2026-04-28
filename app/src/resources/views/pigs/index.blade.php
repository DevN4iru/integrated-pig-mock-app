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

.pig-lifecycle-stack {
    display: grid;
    gap: 16px;
    margin-top: 20px;
}

.pig-lifecycle-collapse {
    padding: 0;
    overflow: hidden;
    border-color: #dbe4f0;
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.055);
    position: relative;
}

.pig-lifecycle-collapse::before {
    content: "";
    position: absolute;
    inset: 0 0 auto 0;
    height: 3px;
    background: linear-gradient(90deg, var(--accent), rgba(37, 99, 235, 0.18), transparent);
}

.pig-lifecycle-collapse summary {
    list-style: none;
    cursor: pointer;
    padding: 18px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 18px;
    background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
    border-bottom: 1px solid transparent;
}

.pig-lifecycle-collapse[open] summary {
    border-bottom-color: #e2e8f0;
}

.pig-lifecycle-collapse summary::-webkit-details-marker {
    display: none;
}

.pig-lifecycle-title {
    display: grid;
    gap: 4px;
}

.pig-lifecycle-title h3 {
    font-size: 18px;
    letter-spacing: -0.02em;
}

.pig-lifecycle-title p {
    color: var(--muted);
    font-size: 13px;
    line-height: 1.45;
}

.pig-lifecycle-preview {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-end;
    gap: 8px;
}

.pig-preview-pill {
    border: 1px solid #dbe4f0;
    background: #f8fbff;
    color: var(--muted);
    border-radius: 999px;
    padding: 7px 10px;
    font-size: 12px;
    font-weight: 800;
    white-space: nowrap;
}

.pig-preview-pill.strong {
    color: var(--accent);
    background: #eff6ff;
    border-color: #bfdbfe;
}

.pig-lifecycle-toggle {
    border: 1px solid var(--line);
    border-radius: 999px;
    padding: 7px 12px;
    font-size: 12px;
    font-weight: 800;
    color: var(--accent);
    background: #fff;
}

.pig-lifecycle-toggle::after {
    content: "View";
}

.pig-lifecycle-collapse[open] .pig-lifecycle-toggle::after {
    content: "Hide";
}

.pig-lifecycle-body {
    padding: 18px;
}

@media (max-width: 760px) {
    .table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        padding-bottom: 8px;
    }

    .data-table {
        min-width: 760px;
    }

    .pig-table th,
    .pig-table td {
        white-space: nowrap;
    }

    .pig-meta {
        min-width: 130px;
    }

    .batch-toolbar {
        display: grid;
        grid-template-columns: 1fr;
    }

    .batch-toolbar .btn,
    .form-actions .btn,
    .inline-actions .btn,
    .inline-actions form,
    .inline-actions form button {
        width: 100%;
    }

    .inline-actions {
        min-width: 150px;
    }

    .pig-lifecycle-collapse summary {
        display: grid;
        grid-template-columns: 1fr;
        padding: 16px;
    }

    .pig-lifecycle-preview {
        justify-content: flex-start;
    }

    .pig-lifecycle-body {
        padding: 14px;
    }

    .pen-group-meta {
        display: grid;
        grid-template-columns: 1fr;
    }

    .pen-group-meta .inline-actions {
        width: 100%;
    }

    .section-title {
        gap: 10px;
    }

    .alert-badge {
        width: fit-content;
    }
}

/* Dashboard-style Pig List polish */
.panel-card {
    border-color: #dbe4f0;
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.055);
    position: relative;
    overflow: hidden;
}

.panel-card::before {
    content: "";
    position: absolute;
    inset: 0 0 auto 0;
    height: 3px;
    background: linear-gradient(90deg, var(--accent), rgba(37, 99, 235, 0.18), transparent);
}

.section-title {
    padding-bottom: 14px;
    border-bottom: 1px solid #e2e8f0;
    margin-bottom: 18px;
}

.section-title h3 {
    letter-spacing: -0.02em;
}

.section-title p {
    line-height: 1.45;
}

.form-grid input,
.form-grid select,
.form-grid textarea {
    border-color: #dbe4f0;
    min-height: 44px;
}

.form-grid input:focus,
.form-grid select:focus,
.form-grid textarea:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
}

.batch-panel {
    border-top: 1px solid #dbe4f0;
}

.batch-toolbar {
    padding-top: 2px;
}

.pen-group-stack > .panel-card {
    margin-top: 0;
}

.pen-group-card {
    border: 1px solid #dbe4f0;
    border-radius: 18px;
    background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
    box-shadow: none;
}

.pen-group-card::before {
    background: transparent;
}

.pen-group-meta {
    padding-bottom: 13px;
    border-bottom: 1px solid #e2e8f0;
}

.pen-group-sub {
    margin-top: 2px;
}

.table-wrap {
    border: 1px solid #dbe4f0;
    border-radius: 16px;
    overflow: hidden;
    background: #fff;
}

.data-table {
    border: 0;
}

.data-table thead th {
    background: #f8fbff;
    border-bottom: 1px solid #dbe4f0;
}

.data-table tbody tr + tr td {
    border-top: 1px solid #e2e8f0;
}

.data-table tbody tr:hover td {
    background: #fbfdff;
}

.pig-meta strong {
    color: var(--text);
}

.alert-badge {
    border: 1px solid #fde68a;
}

.pig-lifecycle-stack {
    margin: 18px 0 20px;
}

.pig-lifecycle-collapse {
    border-color: #dbe4f0;
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.055);
}

.pig-lifecycle-collapse summary {
    background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
}

.pig-lifecycle-title h3 {
    letter-spacing: -0.02em;
}

.pig-preview-pill,
.pig-lifecycle-toggle {
    box-shadow: 0 1px 0 rgba(15, 23, 42, 0.03);
}

.pig-lifecycle-body {
    border-top: 1px solid #e2e8f0;
    background: #fbfdff;
}

.empty-state {
    border-color: #dbe4f0;
    background: #fbfdff;
}


@endsection

@section('content')

@php
    $showActiveSection = in_array($status, ['all', 'active'], true);
    $showSoldSection = in_array($status, ['all', 'sold'], true);
    $showDeadSection = in_array($status, ['all', 'dead'], true);
    $showArchivedSection = in_array($status, ['all', 'archived'], true);

    $soldPigCount = $soldPigs->count();
    $soldRevenuePreview = $soldPigs->sum(function ($pig) {
        $latestSale = $pig->sales->sortByDesc('sold_date')->first();
        return $latestSale ? (float) $latestSale->price : 0;
    });

    $deadPigCount = $deadPigs->count();
    $archivedPigCount = $archivedPigs->count();
    $archivedValuePreview = $archivedPigs->sum(fn ($pig) => (float) $pig->display_value_amount);
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

@if ($showSoldSection || $showDeadSection || $showArchivedSection)
<div class="pig-lifecycle-stack">
@if ($showSoldSection)
    <details class="panel-card pig-lifecycle-collapse">
        <summary>
            <div class="pig-lifecycle-title">
                <h3>Sold Pigs</h3>
                <p>Completed sale records. Kept separate from live farm operations.</p>
            </div>

            <div class="pig-lifecycle-preview">
                <span class="pig-preview-pill strong">{{ $soldPigCount }} sold pig(s)</span>
                <span class="pig-preview-pill">₱ {{ number_format((float) $soldRevenuePreview, 2) }} sale value</span>
                <span class="pig-lifecycle-toggle"></span>
            </div>
        </summary>

        <div class="pig-lifecycle-body">
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
    </details>
@endif

@if ($showDeadSection)
    <details class="panel-card pig-lifecycle-collapse">
        <summary>
            <div class="pig-lifecycle-title">
                <h3>Dead Pigs</h3>
                <p>Mortality records remain separated from live operations.</p>
            </div>

            <div class="pig-lifecycle-preview">
                <span class="pig-preview-pill strong">{{ $deadPigCount }} dead pig(s)</span>
                <span class="pig-preview-pill">mortality records</span>
                <span class="pig-lifecycle-toggle"></span>
            </div>
        </summary>

        <div class="pig-lifecycle-body">
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
    </details>
@endif

@if ($showArchivedSection)
    <details class="panel-card pig-lifecycle-collapse">
        <summary>
            <div class="pig-lifecycle-title">
                <h3>Archived Pigs</h3>
                <p>Archived pigs no longer count as active and do not occupy pen capacity.</p>
            </div>

            <div class="pig-lifecycle-preview">
                <span class="pig-preview-pill strong">{{ $archivedPigCount }} archived pig(s)</span>
                <span class="pig-preview-pill">₱ {{ number_format((float) $archivedValuePreview, 2) }} value</span>
                <span class="pig-lifecycle-toggle"></span>
            </div>
        </summary>

        <div class="pig-lifecycle-body">
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
                                    <td>₱ {{ number_format((float) $pig->display_value_amount, 2) }}</td>
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
    </details>
@endif
</div>
@endif

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
                            <label>Selected Pig Value Total</label>
                            <input type="text" id="batchSaleRecommendedTotal" value="₱ 0.00" readonly>
                        </div>

                        <div class="form-group">
                            <label for="batch_sale_pricing_mode">Pricing Mode</label>
                            <select id="batch_sale_pricing_mode" name="pricing_mode" required onchange="toggleBatchSalePricingMode()">
                                <option value="recommended" {{ old('pricing_mode', 'recommended') === 'recommended' ? 'selected' : '' }}>Use current farm value per pig</option>
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
                                            $trendDirection = $pig->recent_weight_trend_direction;
                                            $trendSymbol = $pig->recent_weight_trend_symbol;
                                            $trendLabel = $pig->recent_weight_trend_label;
                                            $trendClass = match ($trendDirection) {
                                                'up' => 'trend-up',
                                                'down' => 'trend-down',
                                                default => 'trend-flat',
                                            };

                                            $stale = $pig->has_stale_weight;
                                            $displayWeight = $pig->computed_weight;
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
                                            <td>{{ $displayWeight !== null ? number_format((float) $displayWeight, 2) . ' kg' : '—' }}</td>
                                            <td class="{{ $trendClass }}" title="{{ $trendLabel }}">{{ $trendSymbol }}</td>
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
