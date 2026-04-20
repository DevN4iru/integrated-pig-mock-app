@extends('layouts.app')

@section('title', 'Pig Profile')
@section('page_title', 'Pig Profile')
@section('page_subtitle', 'Detailed view of selected pig.')

@section('top_actions')
    @php
        $isArchivedTop = !is_null($pig->deleted_at);
        $isDeadTop = !$isArchivedTop && $pig->mortalityLogs->isNotEmpty();
        $isSoldTop = !$isArchivedTop && $pig->sales->isNotEmpty();
        $isOperationalLockedTop = $isArchivedTop || $isDeadTop || $isSoldTop;
        $isFemaleTop = strtolower((string) $pig->sex) === 'female';
    @endphp

    <a href="{{ route('pigs.index') }}" class="btn">Back to Pig List</a>

    @if (!$isArchivedTop)
        <button type="button" class="btn" onclick="openPigEditPrompt('{{ route('pigs.edit', $pig) }}')">Edit Pig</button>

        <form method="POST" action="{{ route('pigs.destroy', $pig->id) }}" style="display:inline-block;"
            onsubmit="return confirm('Archive this pig? It will be removed from the active list but can still be restored later.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-warning">Archive</button>
        </form>

        @if (!$isOperationalLockedTop)
            <a href="{{ route('health-logs.create', $pig) }}" class="btn primary">Add Health Log</a>
            <a href="{{ route('pig-transfers.create', $pig) }}" class="btn">Transfer Pig</a>
            @if ($isFemaleTop)
                <a href="{{ route('reproduction-cycles.create', $pig) }}" class="btn">Add Breeding Record</a>
            @endif
        @endif
    @else
        <form method="POST" action="{{ route('pigs.restore', $pig->id) }}" style="display:inline-block;"
            onsubmit="return confirm('Restore this pig back to the active list?');">
            @csrf
            <button type="submit" class="btn">Restore</button>
        </form>

        <button type="button" class="btn btn-danger"
            onclick="confirmPigPermanentDelete('{{ route('pigs.force-delete', $pig->id) }}')">
            Permanently Delete
        </button>
    @endif
@endsection

@section('styles')
.profile-stack {
    display: grid;
    gap: 20px;
}

.profile-grid-two {
    display: grid;
    grid-template-columns: 1.15fr 0.85fr;
    gap: 20px;
}

.profile-grid-half {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.filter-inline {
    min-width: 200px;
}

.metric-note {
    margin-top: 2px;
    color: var(--muted);
    font-size: 13px;
}

.info-banner {
    display: grid;
    gap: 14px;
}

.section-subtle {
    color: var(--muted);
    font-size: 13px;
}

.tight-table td,
.tight-table th {
    white-space: nowrap;
}

.chart-wrap {
    width: 100%;
    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    border: 1px solid var(--line);
    border-radius: 16px;
    padding: 14px;
}

.chart-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
    flex-wrap: wrap;
}

.chart-meta p {
    color: var(--muted);
    font-size: 13px;
}

.chart-legend {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: var(--muted);
    font-weight: 600;
}

.chart-legend-line {
    width: 24px;
    height: 3px;
    border-radius: 999px;
    background: #2563eb;
}

#weightChart {
    width: 100%;
    display: block;
}

.transfer-route {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    font-weight: 600;
}

.transfer-arrow {
    color: var(--muted);
    font-weight: 800;
}

.reason-badge {
    display: inline-flex;
    align-items: center;
    padding: 6px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    line-height: 1;
}

.reason-badge.health {
    background: var(--red-soft);
    color: var(--red);
}

.reason-badge.weight {
    background: var(--orange-soft);
    color: var(--orange);
}

.reason-badge.production {
    background: var(--accent-soft);
    color: var(--accent);
}

.reason-badge.breeding {
    background: var(--green-soft);
    color: var(--green);
}

.reason-badge.other {
    background: #eef2ff;
    color: #4f46e5;
}

.pen-advisory {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.pen-advisory-note {
    color: var(--muted);
    font-size: 12px;
}

.protocol-grid {
    display: grid;
    grid-template-columns: 360px minmax(0, 1fr);
    gap: 20px;
    align-items: start;
}

.protocol-summary-panel {
    border: 2px solid #cfd9e8;
    border-radius: 18px;
    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    padding: 16px;
    box-shadow: 0 10px 22px rgba(15, 23, 42, 0.04);
}

.protocol-summary-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.protocol-count-card {
    border: 2px solid #d6e0ee;
    border-radius: 14px;
    background: #ffffff;
    padding: 12px;
    box-shadow: inset 0 0 0 1px rgba(255,255,255,0.65);
}

.protocol-count-card label {
    display: block;
    color: var(--muted);
    font-size: 12px;
    margin-bottom: 6px;
}

.protocol-count-value {
    font-size: 22px;
    font-weight: 800;
    color: #0f172a;
}

.protocol-anchor-note {
    font-size: 12px;
    line-height: 1.5;
    color: var(--muted);
}

.protocol-columns {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
}

.protocol-column {
    border: 2px solid #cfd9e8;
    border-radius: 18px;
    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    padding: 14px;
    box-shadow: 0 10px 22px rgba(15, 23, 42, 0.04);
}

.protocol-column.protocol-due-today {
    border-color: #f3c5c5;
}

.protocol-column.protocol-upcoming {
    border-color: #bfd2ff;
}

.protocol-column.protocol-overdue {
    border-color: #f3d5a4;
}

.protocol-column h4 {
    margin: 0 0 10px;
    font-size: 15px;
}

.protocol-list {
    display: grid;
    gap: 12px;
}

.protocol-item {
    border: 2px solid #d6e0ee;
    border-radius: 16px;
    padding: 14px;
    background: #ffffff;
    box-shadow: 0 8px 18px rgba(15, 23, 42, 0.04);
}

.protocol-item-head {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    align-items: flex-start;
    margin-bottom: 10px;
}

.protocol-item-title {
    font-weight: 800;
    font-size: 16px;
    color: #0f172a;
}

.protocol-meta {
    display: grid;
    gap: 5px;
    font-size: 12px;
    color: #64748b;
    margin-bottom: 10px;
}

.protocol-meta strong {
    color: #334155;
}

.protocol-empty {
    color: var(--muted);
    font-size: 13px;
}

.protocol-form {
    margin-top: 10px;
    padding: 12px;
    border: 2px solid #d6e0ee;
    border-radius: 14px;
    background: #f8fbff;
}

.protocol-form .form-group {
    margin-bottom: 10px;
}

.protocol-form label {
    font-size: 12px;
    font-weight: 700;
    color: #334155;
    display: block;
    margin-bottom: 6px;
}

.protocol-form input,
.protocol-form select,
.protocol-form textarea {
    width: 100% !important;
    min-width: 0;
    border: 2px solid #c9d5e7 !important;
    border-radius: 10px;
    background: #ffffff !important;
    box-sizing: border-box;
}

.protocol-form textarea {
    resize: vertical;
}

.protocol-action-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.protocol-action-submit {
    margin-top: 4px;
}

.protocol-action-submit .btn {
    width: 100%;
    justify-content: center;
}

.protocol-note {
    font-size: 12px;
    color: var(--muted);
}

.protocol-status-badge.pending {
    background: #eef2ff;
    color: #4f46e5;
    border: 1px solid #c7d2fe;
}

.protocol-status-badge.completed {
    background: var(--green-soft);
    color: var(--green);
    border: 1px solid #bde7cb;
}

.protocol-status-badge.skipped {
    background: var(--red-soft);
    color: var(--red);
    border: 1px solid #f4c4c4;
}

.protocol-status-badge.deferred {
    background: var(--orange-soft);
    color: var(--orange);
    border: 1px solid #f5d6a4;
}

.protocol-bucket-note {
    font-size: 12px;
    color: var(--muted);
    margin-top: -4px;
    margin-bottom: 10px;
}

.protocol-guide-box {
    margin-top: 10px;
    padding: 12px;
    border: 2px dashed #c9d5e7;
    border-radius: 14px;
    background: #f8fbff;
}

.protocol-guide-title {
    font-size: 12px;
    font-weight: 800;
    color: #334155;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.protocol-guide-grid {
    display: grid;
    gap: 5px;
    font-size: 12px;
    color: #64748b;
}

.protocol-guide-grid strong {
    color: #334155;
}

.protocol-inline-error {
    margin-top: 10px;
}

.protocol-history-panel {
    border: 2px solid #cfd9e8;
    border-radius: 18px;
    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    padding: 16px;
    box-shadow: 0 10px 22px rgba(15, 23, 42, 0.04);
}

.protocol-history-list {
    display: grid;
    gap: 12px;
}

.protocol-history-item {
    border: 2px solid #d6e0ee;
    border-radius: 16px;
    padding: 14px;
    background: #ffffff;
    box-shadow: 0 8px 18px rgba(15, 23, 42, 0.04);
}

.protocol-history-head {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    align-items: flex-start;
    margin-bottom: 10px;
}

.protocol-history-title {
    font-weight: 800;
    font-size: 16px;
    color: #0f172a;
    margin-bottom: 8px;
}

.protocol-history-meta {
    display: grid;
    gap: 5px;
    font-size: 12px;
    color: #64748b;
}

.protocol-history-meta strong {
    color: #334155;
}

.protocol-history-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-top: 12px;
}

.protocol-history-cell {
    border: 2px solid #d6e0ee;
    border-radius: 12px;
    padding: 10px;
    background: #f8fbff;
    font-size: 12px;
    color: #475569;
}

.protocol-history-cell strong {
    display: block;
    margin-bottom: 4px;
    color: #334155;
}

.protocol-history-active-note {
    margin-top: 10px;
    padding: 10px 12px;
    border-radius: 12px;
    background: var(--orange-soft);
    color: var(--orange);
    font-size: 12px;
    font-weight: 700;
}

@media (max-width: 1200px) {
    .profile-grid-two,
    .profile-grid-half,
    .protocol-grid,
    .protocol-summary-grid,
    .protocol-history-grid {
        grid-template-columns: 1fr;
    }

    .protocol-action-row {
        grid-template-columns: 1fr;
    }
}
@endsection

@section('content')
    @php
        use App\Models\ProtocolExecution;
        use App\Models\ReproductionCycle;

        $pig->loadMissing([
            'pen',
            'reproductionCyclesAsSow.boar',
            'protocolExecutions.rule.template',
            'protocolExecutions.medication',
            'protocolExecutions.vaccination',
        ]);

        $dateAdded = $pig->date_added ? substr((string) $pig->date_added, 0, 10) : '—';
        $weight = is_numeric($pig->computed_weight) ? number_format((float) $pig->computed_weight, 2) : $pig->computed_weight;
        $assetValue = is_numeric($pig->asset_value) ? number_format((float) $pig->asset_value, 2) : $pig->asset_value;
        $penName = $pig->pen?->name ?? '—';
        $ageDisplay = $pig->age_display;

        $isArchived = !is_null($pig->deleted_at);
        $isDead = !$isArchived && $pig->mortalityLogs->isNotEmpty();
        $isSold = !$isArchived && $pig->sales->isNotEmpty();
        $isOperationalLocked = $isArchived || $isDead || $isSold;
        $isFemale = strtolower((string) $pig->sex) === 'female';

        if ($isArchived) {
            $statusLabel = 'Archived';
            $statusBadgeClass = 'blue';
        } elseif ($isDead) {
            $statusLabel = 'Dead';
            $statusBadgeClass = 'red';
        } elseif ($isSold) {
            $statusLabel = 'Sold';
            $statusBadgeClass = 'orange';
        } else {
            $statusLabel = 'Active';
            $statusBadgeClass = 'green';
        }

        $purposeLabels = [
            'weight_update' => 'Weight Update',
            'sick' => 'Sick',
            'recovered' => 'Recovered',
            'checkup' => 'Checkup',
            'injury' => 'Injury',
            'observation' => 'Observation',
        ];

        $weightLogs = $pig->healthLogs
            ->filter(fn ($log) => $log->purpose === 'weight_update' && $log->weight !== null)
            ->sortByDesc(fn ($log) => sprintf('%s-%010d', (string) ($log->log_date ?? ''), (int) $log->id))
            ->values();

        $transferLogs = $pig->transfers
            ->sortByDesc(fn ($transfer) => sprintf(
                '%s-%010d',
                optional($transfer->transfer_date)->format('Y-m-d') ?? (string) $transfer->transfer_date,
                (int) $transfer->id
            ))
            ->values();

        $reproductionCycles = $pig->reproductionCyclesAsSow
            ->sortByDesc(fn ($cycle) => sprintf(
                '%s-%010d',
                optional($cycle->service_date)->format('Y-m-d') ?? (string) $cycle->service_date,
                (int) $cycle->id
            ))
            ->values();

        $gain = $pig->weight_gain;
        $daily = $pig->daily_gain;
        $growthStatus = $pig->growth_status;

        $growthBadgeClass = match($growthStatus) {
            'good' => 'green',
            'declining' => 'red',
            'stagnant' => 'orange',
            default => 'blue',
        };

        if ($gain === null) {
            $trendSymbol = '—';
            $trendText = 'No data';
        } elseif ($gain > 0) {
            $trendSymbol = '↑';
            $trendText = 'Increasing';
        } elseif ($gain < 0) {
            $trendSymbol = '↓';
            $trendText = 'Dropping';
        } else {
            $trendSymbol = '→';
            $trendText = 'Stable';
        }

        if ($isArchived) {
            $lockMessage = 'This pig is archived. Operational records are locked until the pig is restored.';
        } elseif ($isDead) {
            $lockMessage = 'This pig has a mortality record. Health, feed, medication, vaccination, transfer, and breeding records are locked.';
        } elseif ($isSold) {
            $lockMessage = 'This pig has a sale record. Health, feed, medication, vaccination, transfer, and breeding records are locked.';
        } else {
            $lockMessage = null;
        }

        $feedKg = $pig->total_feed_kg;
        $feedEfficiency = $pig->feed_efficiency;
        $totalFeedCost = $pig->total_feed_cost;
        $totalMedicationCost = $pig->total_medication_cost;
        $totalVaccinationCost = $pig->total_vaccination_cost;
        $totalBreedingCost = $pig->total_breeding_cost;
        $totalCareLiability = $pig->total_care_liability;
        $totalOperatingCost = $pig->total_operating_cost;
        $costPerKgGain = $pig->cost_per_kg_gain;
        $performanceStatus = $pig->performance_status;

        $performanceBadgeClass = match($performanceStatus) {
            'good' => 'green',
            'inefficient' => 'orange',
            'risk' => 'red',
            'monitor' => 'orange',
            default => 'blue',
        };

        $performanceLabel = match($performanceStatus) {
            'good' => 'Efficient',
            'inefficient' => 'Inefficient',
            'risk' => 'Risk',
            'monitor' => 'Monitor',
            default => 'No Data',
        };

        $performanceMessage = match($performanceStatus) {
            'good' => 'This pig is gaining weight with acceptable operating efficiency.',
            'inefficient' => 'This pig is gaining weight, but the cost or feed use is becoming inefficient.',
            'risk' => 'This pig is currently weight-negative and needs attention.',
            'monitor' => 'This pig is not gaining weight yet and should be monitored closely.',
            default => 'There is not enough data yet to assess pig-level performance.',
        };

        $transferReasonClass = function ($reasonCode) {
            return match ($reasonCode) {
                'quarantine_due_to_sickness',
                'hospital_treatment',
                'health_monitoring' => 'health',

                'low_weight_separation',
                'same_weight_grouping',
                'finisher_transition',
                'nursery_to_grower',
                'grower_to_finisher' => 'weight',

                'breeding_service',
                'pregnancy_monitoring',
                'farrowing_preparation',
                'boar_assignment',
                'breeding_preparation',
                'gestation_transfer',
                'farrowing_transfer' => 'breeding',

                'pen_maintenance',
                'capacity_balancing',
                'production_regrouping' => 'production',

                default => 'other',
            };
        };

        $cycleBadgeClass = function (string $status) {
            return match ($status) {
                ReproductionCycle::STATUS_PREGNANT => 'green',
                ReproductionCycle::STATUS_DUE_SOON => 'blue',
                ReproductionCycle::STATUS_FARROWED => 'blue',
                ReproductionCycle::STATUS_NOT_PREGNANT => 'red',
                ReproductionCycle::STATUS_RETURNED_TO_HEAT => 'orange',
                ReproductionCycle::STATUS_CLOSED => 'orange',
                default => 'orange',
            };
        };

        $protocol = $pig->protocol_summary;
        $protocolTemplateCode = $protocol['template_code'] ?? null;
        $protocolAnchorDate = $protocol['anchor_date'] ?? null;
        $protocolDueToday = collect($protocol['due_today'] ?? []);
        $protocolUpcoming = collect($protocol['upcoming'] ?? []);
        $protocolOverdue = collect($protocol['overdue'] ?? []);
        $protocolExecutionHistory = collect($pig->protocol_execution_history ?? []);
    @endphp

    <div class="profile-stack">

        @if ($isArchived)
            <div class="flash error">
                This pig is archived. Its records are preserved, but it is hidden from the active list until restored.
            </div>
        @endif

        @if ($lockMessage)
            <div class="flash error">
                {{ $lockMessage }}
            </div>
        @endif

        <div class="profile-grid-two">
            <div class="panel-card">
                <div class="section-title">
                    <div>
                        <h3>Pig Overview</h3>
                        <p>Core identity, pen assignment, and current valuation snapshot.</p>
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
                        <label>Age</label>
                        <input type="text" value="{{ $ageDisplay }}" readonly>
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

                <div class="flash" style="margin-top: 16px;">
                    System age is stored in days for schedule-based protocol tracking. Current stored age:
                    <strong>{{ (int) ($pig->age ?? 0) }} day(s)</strong>.
                </div>
            </div>

            <div class="panel-card">
                <div class="section-title">
                    <div>
                        <h3>Performance Intelligence</h3>
                        <p>Business-level view of gain, cost, and operational efficiency.</p>
                    </div>
                    <span class="badge {{ $performanceBadgeClass }}">{{ $performanceLabel }}</span>
                </div>

                <div class="flash {{ $performanceStatus === 'risk' ? 'error' : 'success' }}">
                    {{ $performanceMessage }}
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Feed Efficiency</label>
                        <input type="text" value="{{ $feedEfficiency !== null ? number_format($feedEfficiency, 2) . ' kg feed / kg gain' : '—' }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Cost per kg Gain</label>
                        <input type="text" value="{{ $costPerKgGain !== null ? '₱ ' . number_format($costPerKgGain, 2) . ' / kg gain' : '—' }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Performance Status</label>
                        <input type="text" value="{{ $performanceLabel }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Total Feed (kg only)</label>
                        <input type="text" value="{{ number_format($feedKg, 2) }} kg" readonly>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel-card">
            <div class="section-title">
                <div>
                    <h3>Protocol Schedule</h3>
                    <p>Template, anchor date, and current due/upcoming/overdue protocol items for this pig.</p>
                </div>
                @if ($protocolTemplateCode)
                    <span class="badge blue">{{ $protocolTemplateCode }}</span>
                @endif
            </div>

            @if (!$protocol)
                <div class="empty-state">No protocol template currently applies to this pig.</div>
            @else
                <div class="protocol-grid">
                    <div class="protocol-summary-panel">
                        <div class="form-grid" style="margin-bottom: 14px;">
                            <div class="form-group">
                                <label>Template Code</label>
                                <input type="text" value="{{ $protocolTemplateCode }}" readonly>
                            </div>

                            <div class="form-group">
                                <label>Anchor Date</label>
                                <input type="text" value="{{ $protocolAnchorDate ?? '—' }}" readonly>
                            </div>
                        </div>

                        <div class="protocol-summary-grid">
                            <div class="protocol-count-card">
                                <label>Due Today</label>
                                <div class="protocol-count-value">{{ $protocolDueToday->count() }}</div>
                            </div>

                            <div class="protocol-count-card">
                                <label>Upcoming</label>
                                <div class="protocol-count-value">{{ $protocolUpcoming->count() }}</div>
                            </div>

                            <div class="protocol-count-card">
                                <label>Overdue</label>
                                <div class="protocol-count-value">{{ $protocolOverdue->count() }}</div>
                            </div>

                            <div class="protocol-count-card">
                                <label>Current Anchor Rule</label>
                                <div class="protocol-anchor-note">
                                    Birthed pigs use the birth anchor from <strong>Date Added</strong> in the current MVP.
                                </div>
                            </div>
                        </div>

                        @if (
                            $errors->has('status')
                            || $errors->has('executed_date')
                            || $errors->has('notes')
                            || $errors->has('protocol_rule_id')
                            || $errors->has('actual_product_name')
                            || $errors->has('actual_dose')
                            || $errors->has('actual_cost')
                        )
                            <div class="flash error" style="margin-top: 14px;">
                                @if ($errors->has('status'))
                                    {{ $errors->first('status') }}
                                @elseif ($errors->has('executed_date'))
                                    {{ $errors->first('executed_date') }}
                                @elseif ($errors->has('actual_product_name'))
                                    {{ $errors->first('actual_product_name') }}
                                @elseif ($errors->has('actual_dose'))
                                    {{ $errors->first('actual_dose') }}
                                @elseif ($errors->has('actual_cost'))
                                    {{ $errors->first('actual_cost') }}
                                @elseif ($errors->has('notes'))
                                    {{ $errors->first('notes') }}
                                @else
                                    {{ $errors->first('protocol_rule_id') }}
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="protocol-columns">
                        @php
                            $protocolBuckets = [
                                'Due Today' => [$protocolDueToday, 'red', 'protocol-due-today', 'Items that are due inside the current active window.'],
                                'Upcoming' => [$protocolUpcoming, 'blue', 'protocol-upcoming', 'Future scheduled items based on the current birth anchor.'],
                                'Overdue' => [$protocolOverdue, 'orange', 'protocol-overdue', 'Items whose window has passed without a resolved execution.'],
                            ];
                        @endphp

                        @foreach ($protocolBuckets as $bucketTitle => [$bucketItems, $bucketBadge, $bucketClass, $bucketNote])
                            <div class="protocol-column {{ $bucketClass }}">
                                <div class="section-title" style="margin-bottom: 10px;">
                                    <div>
                                        <h4>{{ $bucketTitle }}</h4>
                                        <p class="section-subtle">{{ $bucketItems->count() }} item(s)</p>
                                    </div>
                                    <span class="badge {{ $bucketBadge }}">{{ $bucketItems->count() }}</span>
                                </div>

                                <div class="protocol-bucket-note">{{ $bucketNote }}</div>

                                @if ($bucketItems->isEmpty())
                                    <div class="protocol-empty">No items in this bucket.</div>
                                @else
                                    <div class="protocol-list">
                                        @foreach ($bucketItems as $item)
                                            @php
                                                $executionStatus = $item['execution_status'] ?? null;
                                                $executionStatusLabel = $executionStatus ? ucfirst($executionStatus) : 'Pending';
                                                $executionStatusClass = $executionStatus ?: 'pending';

                                                $isMedicationProtocol = ($item['type'] ?? null) === 'medication';
                                                $isVaccinationProtocol = ($item['type'] ?? null) === 'vaccination';
                                                $isDetailedAdminType = $isMedicationProtocol || $isVaccinationProtocol;

                                                $isCurrentOldForm = (string) old('protocol_rule_id') === (string) $item['rule_id']
                                                    && (string) old('scheduled_for_date') === (string) $item['due_start'];

                                                $defaultStatus = $executionStatus ?: ProtocolExecution::STATUS_COMPLETED;
                                                $prefillStatus = $isCurrentOldForm
                                                    ? old('status', $defaultStatus)
                                                    : $defaultStatus;

                                                $prefillExecutedDate = $isCurrentOldForm
                                                    ? old('executed_date', $item['executed_date'] ?? '')
                                                    : ($item['executed_date'] ?? '');

                                                $prefillNotes = $isCurrentOldForm
                                                    ? old('notes', $item['execution_notes'] ?? '')
                                                    : ($item['execution_notes'] ?? '');

                                                $prefillActualProductName = $isCurrentOldForm
                                                    ? old('actual_product_name', $item['actual_product_name'] ?? '')
                                                    : ($item['actual_product_name'] ?? '');

                                                $prefillActualDose = $isCurrentOldForm
                                                    ? old('actual_dose', $item['actual_dose'] ?? '')
                                                    : ($item['actual_dose'] ?? '');

                                                $prefillActualCost = $isCurrentOldForm
                                                    ? old('actual_cost', $item['actual_cost'] ?? '')
                                                    : ($item['actual_cost'] ?? '');

                                                $actualProductLabel = $isVaccinationProtocol ? 'Actual Vaccine Used' : 'Actual Product Used';
                                                $actualDoseLabel = $isVaccinationProtocol ? 'Actual Dose' : 'Actual Dosage';
                                            @endphp

                                            <div class="protocol-item">
                                                <div class="protocol-item-head">
                                                    <div class="protocol-item-title">{{ $item['action'] }}</div>
                                                    <span class="badge protocol-status-badge {{ $executionStatusClass }}">
                                                        {{ $executionStatusLabel }}
                                                    </span>
                                                </div>

                                                <div class="protocol-meta">
                                                    <div><strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $item['type'])) }}</div>
                                                    <div><strong>Requirement:</strong> {{ ucfirst($item['requirement']) }}</div>
                                                    <div><strong>Window:</strong> {{ $item['due_start'] }} to {{ $item['due_end'] }}</div>
                                                    <div><strong>Recommended Product:</strong> {{ $item['product_note'] ?: '—' }}</div>
                                                    <div><strong>Recommended Dosage:</strong> {{ $item['dosage_note'] ?: '—' }}</div>
                                                    <div><strong>Administration Note:</strong> {{ $item['administration_note'] ?: '—' }}</div>
                                                    <div><strong>Alternatives / Market Note:</strong> {{ $item['market_note'] ?: '—' }}</div>
                                                    <div><strong>Condition:</strong> {{ $item['condition_note'] ?: '—' }}</div>
                                                    <div><strong>Executed Date:</strong> {{ $item['executed_date'] ?: '—' }}</div>
                                                    <div><strong>Protocol Notes:</strong> {{ $item['execution_notes'] ?: '—' }}</div>
                                                    @if ($isDetailedAdminType)
                                                        <div><strong>Linked Detailed Record:</strong> {{ $item['has_linked_admin_log'] ? 'Yes' : 'No' }}</div>
                                                        <div><strong>Current Actual Product:</strong> {{ $item['has_linked_admin_log'] ? ($item['actual_product_name'] ?: '—') : '—' }}</div>
                                                        <div><strong>Current Actual Dose:</strong> {{ $item['has_linked_admin_log'] ? ($item['actual_dose'] ?: '—') : '—' }}</div>
                                                        <div><strong>Current Actual Cost:</strong> {{ $item['actual_cost'] !== null ? '₱ ' . number_format((float) $item['actual_cost'], 2) : '—' }}</div>
                                                        <div><strong>Current Actual Notes:</strong> {{ $item['actual_notes'] ?: '—' }}</div>
                                                    @endif
                                                </div>

                                                @if ($isDetailedAdminType)
                                                    <div class="protocol-guide-box">
                                                        <div class="protocol-guide-title">Protocol Guide</div>
                                                        <div class="protocol-guide-grid">
                                                            <div><strong>Recommended Product:</strong> {{ $item['product_note'] ?: '—' }}</div>
                                                            <div><strong>Recommended Dosage:</strong> {{ $item['dosage_note'] ?: '—' }}</div>
                                                            <div><strong>Administration Note:</strong> {{ $item['administration_note'] ?: '—' }}</div>
                                                            <div><strong>Alternative / Market Note:</strong> {{ $item['market_note'] ?: '—' }}</div>
                                                        </div>
                                                    </div>
                                                @endif

                                                @if (!$isOperationalLocked)
                                                    <form method="POST" action="{{ route('protocol-executions.upsert', $pig) }}" class="protocol-form">
                                                        @csrf
                                                        <input type="hidden" name="protocol_rule_id" value="{{ $item['rule_id'] }}">
                                                        <input type="hidden" name="scheduled_for_date" value="{{ $item['due_start'] }}">

                                                        @if (
                                                            $isCurrentOldForm
                                                            && (
                                                                $errors->has('status')
                                                                || $errors->has('executed_date')
                                                                || $errors->has('notes')
                                                                || $errors->has('actual_product_name')
                                                                || $errors->has('actual_dose')
                                                                || $errors->has('actual_cost')
                                                                || $errors->has('protocol_rule_id')
                                                            )
                                                        )
                                                            <div class="flash error protocol-inline-error">
                                                                @if ($errors->has('status'))
                                                                    {{ $errors->first('status') }}
                                                                @elseif ($errors->has('executed_date'))
                                                                    {{ $errors->first('executed_date') }}
                                                                @elseif ($errors->has('actual_product_name'))
                                                                    {{ $errors->first('actual_product_name') }}
                                                                @elseif ($errors->has('actual_dose'))
                                                                    {{ $errors->first('actual_dose') }}
                                                                @elseif ($errors->has('actual_cost'))
                                                                    {{ $errors->first('actual_cost') }}
                                                                @elseif ($errors->has('notes'))
                                                                    {{ $errors->first('notes') }}
                                                                @else
                                                                    {{ $errors->first('protocol_rule_id') }}
                                                                @endif
                                                            </div>
                                                        @endif

                                                        <div class="protocol-action-row">
                                                            <div class="form-group">
                                                                <label>Status</label>
                                                                <select name="status">
                                                                    <option value="{{ ProtocolExecution::STATUS_COMPLETED }}" {{ $prefillStatus === ProtocolExecution::STATUS_COMPLETED ? 'selected' : '' }}>Completed</option>
                                                                    <option value="{{ ProtocolExecution::STATUS_SKIPPED }}" {{ $prefillStatus === ProtocolExecution::STATUS_SKIPPED ? 'selected' : '' }}>Skipped</option>
                                                                    <option value="{{ ProtocolExecution::STATUS_DEFERRED }}" {{ $prefillStatus === ProtocolExecution::STATUS_DEFERRED ? 'selected' : '' }}>Deferred</option>
                                                                </select>
                                                            </div>

                                                            <div class="form-group">
                                                                <label>Executed Date</label>
                                                                <input type="date" name="executed_date" value="{{ $prefillExecutedDate }}">
                                                            </div>
                                                        </div>

                                                        @if ($isDetailedAdminType)
                                                            <div class="protocol-action-row">
                                                                <div class="form-group">
                                                                    <label>{{ $actualProductLabel }}</label>
                                                                    <input type="text" name="actual_product_name" value="{{ $prefillActualProductName }}">
                                                                </div>

                                                                <div class="form-group">
                                                                    <label>{{ $actualDoseLabel }}</label>
                                                                    <input type="text" name="actual_dose" value="{{ $prefillActualDose }}">
                                                                </div>
                                                            </div>

                                                            <div class="form-group">
                                                                <label>Actual Cost</label>
                                                                <input type="number" name="actual_cost" step="0.01" min="0" value="{{ $prefillActualCost }}">
                                                            </div>
                                                        @endif

                                                        <div class="form-group">
                                                            <label>Notes</label>
                                                            <textarea name="notes" rows="2" placeholder="{{ $isDetailedAdminType ? 'Optional for completed. Required for skipped or deferred.' : 'Required for skipped or deferred.' }}">{{ $prefillNotes }}</textarea>
                                                        </div>

                                                        <div class="protocol-action-submit">
                                                            <button type="submit" class="btn primary">Save Execution</button>
                                                        </div>
                                                    </form>
                                                @else
                                                    <div class="flash error" style="margin-top: 10px;">
                                                        Protocol execution updates are locked for this pig.
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="panel-card">
            <div class="section-title">
                <div>
                    <h3>Protocol Execution History</h3>
                    <p>Recorded protocol execution outcomes for this pig, including resolved occurrences and linked detailed administration records.</p>
                </div>
                <span class="badge blue">{{ $protocolExecutionHistory->count() }}</span>
            </div>

            @if($protocolExecutionHistory->isEmpty())
                <div class="empty-state">No protocol execution history yet.</div>
            @else
                <div class="protocol-history-panel">
                    <div class="protocol-history-list">
                        @foreach($protocolExecutionHistory as $entry)
                            @php
                                $historyStatusClass = $entry['status'] ?? 'pending';
                                $isMedicationHistory = ($entry['type'] ?? null) === 'medication';
                                $isVaccinationHistory = ($entry['type'] ?? null) === 'vaccination';
                                $isDetailedHistory = $isMedicationHistory || $isVaccinationHistory;
                            @endphp

                            <div class="protocol-history-item">
                                <div class="protocol-history-head">
                                    <div>
                                        <div class="protocol-history-title">{{ $entry['action'] ?: 'Protocol Occurrence' }}</div>
                                        <div class="protocol-history-meta">
                                            <div><strong>Template:</strong> {{ $entry['template_code'] ?: '—' }}</div>
                                            <div><strong>Type:</strong> {{ $entry['type'] ? ucfirst(str_replace('_', ' ', $entry['type'])) : '—' }}</div>
                                            <div><strong>Requirement:</strong> {{ $entry['requirement'] ? ucfirst($entry['requirement']) : '—' }}</div>
                                            <div><strong>Scheduled For:</strong> {{ $entry['scheduled_for_date'] ?: '—' }}</div>
                                            <div><strong>Executed Date:</strong> {{ $entry['executed_date'] ?: '—' }}</div>
                                            <div><strong>Protocol Notes:</strong> {{ $entry['notes'] ?: '—' }}</div>
                                        </div>
                                    </div>

                                    <span class="badge protocol-status-badge {{ $historyStatusClass }}">
                                        {{ $entry['status_label'] ?? ucfirst((string) ($entry['status'] ?? 'pending')) }}
                                    </span>
                                </div>

                                <div class="protocol-history-grid">
                                    <div class="protocol-history-cell">
                                        <strong>Recommended Product</strong>
                                        {{ $entry['product_note'] ?: '—' }}
                                    </div>

                                    <div class="protocol-history-cell">
                                        <strong>Recommended Dosage</strong>
                                        {{ $entry['dosage_note'] ?: '—' }}
                                    </div>

                                    <div class="protocol-history-cell">
                                        <strong>Administration Note</strong>
                                        {{ $entry['administration_note'] ?: '—' }}
                                    </div>

                                    <div class="protocol-history-cell">
                                        <strong>Alternative / Market Note</strong>
                                        {{ $entry['market_note'] ?: '—' }}
                                    </div>

                                    <div class="protocol-history-cell">
                                        <strong>Condition</strong>
                                        {{ $entry['condition_note'] ?: '—' }}
                                    </div>

                                    <div class="protocol-history-cell">
                                        <strong>Linked Detailed Record</strong>
                                        {{ $entry['has_linked_admin_log'] ? 'Yes' : 'No' }}
                                    </div>

                                    @if($isDetailedHistory)
                                        <div class="protocol-history-cell">
                                            <strong>Actual Product Used</strong>
                                            {{ $entry['actual_product_name'] ?: '—' }}
                                        </div>

                                        <div class="protocol-history-cell">
                                            <strong>Actual Dose / Dosage</strong>
                                            {{ $entry['actual_dose'] ?: '—' }}
                                        </div>

                                        <div class="protocol-history-cell">
                                            <strong>Actual Cost</strong>
                                            {{ $entry['actual_cost'] !== null ? '₱ ' . number_format((float) $entry['actual_cost'], 2) : '—' }}
                                        </div>

                                        <div class="protocol-history-cell">
                                            <strong>Actual Notes</strong>
                                            {{ $entry['actual_notes'] ?: '—' }}
                                        </div>
                                    @endif
                                </div>

                                @if(($entry['status'] ?? null) === ProtocolExecution::STATUS_DEFERRED)
                                    <div class="protocol-history-active-note">
                                        This occurrence is deferred and remains unresolved, so it can still appear in the active schedule buckets.
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="profile-grid-half">
            <div class="panel-card">
                <div class="section-title">
                    <div>
                        <h3>Growth Analytics</h3>
                        <p>Latest growth performance based on the two most recent weight logs.</p>
                    </div>
                    <span class="badge {{ $growthBadgeClass }}">{{ ucfirst(str_replace('_', ' ', $growthStatus)) }}</span>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Weight Gain</label>
                        <input type="text" value="{{ $gain !== null ? number_format($gain, 2) . ' kg' : '—' }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Daily Gain</label>
                        <input type="text" value="{{ $daily !== null ? number_format($daily, 2) . ' kg/day' : '—' }}" readonly>
                    </div>

                    <div class="form-group full">
                        <label>Trend</label>
                        <input type="text" value="{{ $trendSymbol . ' ' . $trendText }}" readonly>
                    </div>
                </div>
            </div>

            <div class="panel-card">
                <div class="section-title">
                    <div>
                        <h3>Cost Tracking</h3>
                        <p>Operating cost, breeding exposure, and care liability summary for this pig.</p>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Total Feed Cost</label>
                        <input type="text" value="₱ {{ number_format($totalFeedCost, 2) }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Total Medication Cost</label>
                        <input type="text" value="₱ {{ number_format($totalMedicationCost, 2) }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Total Vaccination Cost</label>
                        <input type="text" value="₱ {{ number_format($totalVaccinationCost, 2) }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Total Breeding Cost</label>
                        <input type="text" value="₱ {{ number_format($totalBreedingCost, 2) }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Care Liability</label>
                        <input type="text" value="₱ {{ number_format($totalCareLiability, 2) }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Total Operating Cost</label>
                        <input type="text" value="₱ {{ number_format($totalOperatingCost, 2) }}" readonly>
                    </div>
                </div>
            </div>
        </div>

        @if($isFemale)
            <div class="panel-card">
                <div class="section-title">
                    <div>
                        <h3>Reproduction Timeline</h3>
                        <p>Breeding, pregnancy, farrowing, and litter outcome history for this sow.</p>
                    </div>

                    @if (!$isOperationalLocked)
                        <div style="display:flex; gap:8px; flex-wrap:wrap;">
                            <a href="{{ route('reproduction-cycles.create', $pig) }}" class="btn primary">Add Breeding Record</a>
                            <a href="{{ route('reproduction-cycles.index', $pig) }}" class="btn">All Breeding Records</a>
                        </div>
                    @endif
                </div>

                @if($reproductionCycles->isEmpty())
                    <div class="empty-state">No reproduction cycles recorded yet for this sow.</div>
                @else
                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Pregnancy Result</th>
                                    <th>Breeding Type</th>
                                    <th>Boar</th>
                                    <th>Service Date</th>
                                    <th>Pregnancy Check</th>
                                    <th>Expected Farrow</th>
                                    <th>Recommended Pen</th>
                                    <th>Litter Outcome</th>
                                    <th>Cost</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reproductionCycles as $cycle)
                                    @php
                                        $outcomeText = '—';

                                        if ($cycle->status === ReproductionCycle::STATUS_FARROWED) {
                                            $parts = [];

                                            if ($cycle->total_born !== null) {
                                                $parts[] = 'Total: ' . $cycle->total_born;
                                            }

                                            if ($cycle->born_alive !== null) {
                                                $parts[] = 'Alive: ' . $cycle->born_alive;
                                            }

                                            if ($cycle->stillborn !== null) {
                                                $parts[] = 'Stillborn: ' . $cycle->stillborn;
                                            }

                                            if ($cycle->mummified !== null) {
                                                $parts[] = 'Mummified: ' . $cycle->mummified;
                                            }

                                            $outcomeText = empty($parts) ? 'Recorded' : implode(' • ', $parts);
                                        }

                                        $recommendedPen = $cycle->recommended_pen_type ?? '—';
                                        $currentPenType = $pig->pen?->type;
                                        $penAligned = $recommendedPen === '—' || $currentPenType === $recommendedPen;
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="badge {{ $cycleBadgeClass($cycle->status) }}">
                                                {{ $cycle->status_label }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $cycle->pregnancy_result === ReproductionCycle::PREGNANCY_RESULT_PREGNANT ? 'green' : ($cycle->pregnancy_result === ReproductionCycle::PREGNANCY_RESULT_NOT_PREGNANT ? 'red' : 'blue') }}">
                                                {{ $cycle->pregnancy_result_label }}
                                            </span>
                                        </td>
                                        <td>{{ $cycle->breeding_type_label }}</td>
                                        <td>{{ $cycle->boar?->ear_tag ?? '—' }}</td>
                                        <td>{{ $cycle->service_date?->format('Y-m-d') ?? '—' }}</td>
                                        <td>{{ $cycle->pregnancy_check_date?->format('Y-m-d') ?? '—' }}</td>
                                        <td>{{ $cycle->expected_farrow_date?->format('Y-m-d') ?? '—' }}</td>
                                        <td>
                                            <span class="pen-advisory">
                                                <span>{{ $recommendedPen }}</span>
                                                @if(!$penAligned)
                                                    <span class="badge orange">Current pen differs</span>
                                                @endif
                                            </span>
                                        </td>
                                        <td>{{ $outcomeText }}</td>
                                        <td>₱ {{ number_format((float) $cycle->breeding_cost, 2) }}</td>
                                        <td>
                                            <a href="{{ route('reproduction-cycles.edit', $cycle) }}" class="btn">Edit</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif

        <div class="panel-card">
            <div class="section-title">
                <div>
                    <h3>Transfer History</h3>
                    <p>Pen movement history for this pig, including structured reasons and notes.</p>
                </div>

                @if (!$isOperationalLocked)
                    <a href="{{ route('pig-transfers.create', $pig) }}" class="btn primary">Transfer Pig</a>
                @endif
            </div>

            @if($transferLogs->isEmpty())
                <div class="empty-state">No transfer history yet.</div>
            @else
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Route</th>
                                <th>Reason</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transferLogs as $transfer)
                                @php
                                    $reasonClass = $transferReasonClass($transfer->reason_code);
                                    $fromPenName = $transfer->fromPen?->name ?? '—';
                                    $toPenName = $transfer->toPen?->name ?? '—';
                                @endphp
                                <tr>
                                    <td>{{ $transfer->transfer_date?->format('Y-m-d') ?? '—' }}</td>
                                    <td>
                                        <span class="transfer-route">
                                            <span>{{ $fromPenName }}</span>
                                            <span class="transfer-arrow">→</span>
                                            <span>{{ $toPenName }}</span>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="reason-badge {{ $reasonClass }}">
                                            {{ $transfer->reason_label }}
                                        </span>
                                    </td>
                                    <td>{{ $transfer->reason_notes ?: '—' }}</td>
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
                    <h3>Weight History</h3>
                    <p>Recorded weight-update logs over time for this pig.</p>
                </div>
            </div>

            @if($weightLogs->isEmpty())
                <div class="empty-state">No weight history yet.</div>
            @else
                @if($weightLogs->count() >= 2)
                    <div class="chart-wrap" style="margin-bottom: 16px;">
                        <div class="chart-meta">
                            <p>Weight progression based on recorded weight-update health logs.</p>
                            <span class="chart-legend">
                                <span class="chart-legend-line"></span>
                                Weight trend
                            </span>
                        </div>
                        <canvas id="weightChart" height="140"></canvas>
                    </div>
                @endif

                <div class="table-wrap">
                    <table class="data-table tight-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Weight</th>
                                <th>Condition / Summary</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($weightLogs as $log)
                                <tr>
                                    <td>{{ $log->log_date }}</td>
                                    <td>
                                        <strong>{{ number_format((float) $log->weight, 2) }} kg</strong>
                                        @if ($loop->first)
                                            <span class="badge blue" style="margin-left: 8px;">Latest</span>
                                        @endif
                                    </td>
                                    <td>{{ $log->condition }}</td>
                                    <td>{{ $log->notes ?: '—' }}</td>
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
                    <h3>Health Logs</h3>
                    <p>Health event history with quick filtering by purpose.</p>
                </div>

                <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                    <select id="healthFilter" class="filter-inline">
                        <option value="all">All Purposes</option>
                        <option value="weight_update">Weight Update</option>
                        <option value="sick">Sick</option>
                        <option value="recovered">Recovered</option>
                        <option value="checkup">Checkup</option>
                        <option value="injury">Injury</option>
                        <option value="observation">Observation</option>
                    </select>

                    @if (!$isOperationalLocked)
                        <a href="{{ route('health-logs.create', $pig) }}" class="btn primary">Add Health Log</a>
                    @endif
                </div>
            </div>

            @if($pig->healthLogs->isEmpty())
                <div class="empty-state">No health logs yet.</div>
            @else
                <div class="table-wrap">
                    <table class="data-table" id="healthTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Purpose</th>
                                <th>Condition / Summary</th>
                                <th>Weight</th>
                                <th>Notes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pig->healthLogs as $log)
                                @php
                                    $purposeBadgeClass = match($log->purpose) {
                                        'weight_update' => 'blue',
                                        'sick' => 'red',
                                        'recovered' => 'green',
                                        'checkup' => 'blue',
                                        'injury' => 'orange',
                                        default => 'orange',
                                    };
                                @endphp
                                <tr data-purpose="{{ $log->purpose }}">
                                    <td>{{ $log->log_date }}</td>
                                    <td>
                                        <span class="badge {{ $purposeBadgeClass }}">
                                            {{ $purposeLabels[$log->purpose] ?? ucfirst(str_replace('_', ' ', $log->purpose)) }}
                                        </span>
                                    </td>
                                    <td>{{ $log->condition }}</td>
                                    <td>{{ $log->weight !== null ? number_format((float) $log->weight, 2) . ' kg' : '—' }}</td>
                                    <td>{{ $log->notes ?: '—' }}</td>
                                    <td>
                                        @if (!$isOperationalLocked)
                                            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                                <a href="{{ route('health-logs.edit', [$pig->id, $log]) }}" class="btn">Edit</a>
                                                <form method="POST" action="{{ route('health-logs.destroy', [$pig->id, $log]) }}" onsubmit="return confirm('Delete this health log?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">Delete</button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-muted">Locked</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="profile-grid-half">
            <div class="panel-card">
                <div class="section-title">
                    <div>
                        <h3>Medication</h3>
                        <p>Treatments and administered medicines for this pig.</p>
                    </div>
                    @if (!$isOperationalLocked)
                        <a href="{{ route('medications.create', $pig) }}" class="btn primary">Add Medication</a>
                    @endif
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
                                    <th>Cost</th>
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
                                        <td>₱ {{ number_format((float) ($med->cost ?? 0), 2) }}</td>
                                        <td>{{ $med->notes ?: '—' }}</td>
                                        <td>
                                            @if (!$isOperationalLocked)
                                                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                                    <a href="{{ route('medications.edit', [$pig, $med]) }}" class="btn">Edit</a>
                                                    <form method="POST" action="{{ route('medications.destroy', [$pig, $med]) }}" onsubmit="return confirm('Delete this medication record?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">Delete</button>
                                                    </form>
                                                </div>
                                            @else
                                                <span class="text-muted">Locked</span>
                                            @endif
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
                        <h3>Vaccination</h3>
                        <p>Vaccination records and immunization history for this pig.</p>
                    </div>
                    @if (!$isOperationalLocked)
                        <a href="{{ route('vaccinations.create', $pig) }}" class="btn primary">Add Vaccination</a>
                    @endif
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
                                    <th>Cost</th>
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
                                        <td>₱ {{ number_format((float) ($vac->cost ?? 0), 2) }}</td>
                                        <td>{{ $vac->notes ?: '—' }}</td>
                                        <td>
                                            @if (!$isOperationalLocked)
                                                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                                    <a href="{{ route('vaccinations.edit', [$pig, $vac]) }}" class="btn">Edit</a>
                                                    <form method="POST" action="{{ route('vaccinations.destroy', [$pig, $vac]) }}" onsubmit="return confirm('Delete this vaccination record?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">Delete</button>
                                                    </form>
                                                </div>
                                            @else
                                                <span class="text-muted">Locked</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="profile-grid-half">
            <div class="panel-card">
                <div class="section-title">
                    <div>
                        <h3>Mortality</h3>
                        <p>Mortality records for this pig.</p>
                    </div>
                    @if(!$isArchived && $pig->sales->isEmpty())
                        <a href="{{ route('mortality.create', $pig) }}" class="btn primary">Record Mortality</a>
                    @endif
                </div>

                @if($pig->sales->isNotEmpty() && !$isArchived)
                    <div class="flash error">
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

            <div class="panel-card">
                <div class="section-title">
                    <div>
                        <h3>Sold Records</h3>
                        <p>Sale records for this pig.</p>
                    </div>
                    @if(!$isArchived && $pig->mortalityLogs->isEmpty())
                        <a href="{{ route('sales.create', $pig) }}" class="btn primary">Record Sale</a>
                    @endif
                </div>

                @if($pig->mortalityLogs->isNotEmpty() && !$isArchived)
                    <div class="flash error">
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
        </div>

        <div class="panel-card">
            <div class="section-title">
                <div>
                    <h3>Feed Logs</h3>
                    <p>Feeding periods and diet tracking.</p>
                </div>
                @if (!$isOperationalLocked)
                    <a href="{{ route('feed-logs.create', $pig) }}" class="btn primary">Add Feed Log</a>
                @endif
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
                                <th>Cost</th>
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
                                    <td>₱ {{ number_format((float) ($feed->cost ?? 0), 2) }}</td>
                                    <td>{{ $feed->unit }}</td>
                                    <td>{{ $feed->feeding_time }}</td>
                                    <td>
                                        <span class="badge {{ $feed->status === 'completed' ? 'green' : 'orange' }}">
                                            {{ ucfirst($feed->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $feed->notes ?: '—' }}</td>
                                    <td>
                                        @if (!$isOperationalLocked)
                                            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                                <a href="{{ route('feed-logs.edit', [$pig, $feed]) }}" class="btn">Edit</a>
                                                <form method="POST" action="{{ route('feed-logs.destroy', [$pig, $feed]) }}" onsubmit="return confirm('Delete this feed log?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">Delete</button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-muted">Locked</span>
                                        @endif
                                    </td>
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
function openPigEditPrompt(url) {
    const code = prompt('Type the edit access code to continue:');
    if (code === null) return;
    if (code !== '12345') {
        alert('Wrong access code.');
        return;
    }
    window.location.href = url + '?code=' + encodeURIComponent(code);
}

function confirmPigPermanentDelete(url) {
    const code = prompt('Permanent delete will erase this pig and its related records forever.\n\nEnter challenge code 12345 to continue:');
    if (code === null) return;

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

document.getElementById('healthFilter')?.addEventListener('change', function () {
    const value = this.value;
    document.querySelectorAll('#healthTable tbody tr').forEach(row => {
        if (value === 'all') {
            row.style.display = '';
        } else {
            row.style.display = row.dataset.purpose === value ? '' : 'none';
        }
    });
});

@if($weightLogs->count() >= 2)
(function () {
    const canvas = document.getElementById('weightChart');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');

    const data = @json(
        $weightLogs->reverse()->map(fn($log) => [
            'date' => $log->log_date,
            'weight' => (float) $log->weight
        ])->values()
    );

    const width = canvas.width = canvas.offsetWidth;
    const height = canvas.height;
    const padding = 30;

    const weights = data.map(point => point.weight);
    const min = Math.min(...weights);
    const max = Math.max(...weights);
    const range = max - min || 1;

    const getX = (index) => {
        if (data.length === 1) {
            return width / 2;
        }

        return padding + (index / (data.length - 1)) * (width - padding * 2);
    };

    const getY = (value) => {
        return height - padding - ((value - min) / range) * (height - padding * 2);
    };

    ctx.clearRect(0, 0, width, height);

    ctx.strokeStyle = '#e5e7eb';
    ctx.lineWidth = 1;

    for (let i = 0; i <= 4; i++) {
        const y = padding + i * ((height - padding * 2) / 4);
        ctx.beginPath();
        ctx.moveTo(padding, y);
        ctx.lineTo(width - padding, y);
        ctx.stroke();
    }

    ctx.beginPath();
    ctx.lineWidth = 3;
    ctx.strokeStyle = '#2563eb';

    data.forEach((point, index) => {
        const x = getX(index);
        const y = getY(point.weight);

        if (index === 0) {
            ctx.moveTo(x, y);
        } else {
            ctx.lineTo(x, y);
        }
    });

    ctx.stroke();

    data.forEach((point, index) => {
        const x = getX(index);
        const y = getY(point.weight);

        ctx.beginPath();
        ctx.arc(x, y, 4, 0, Math.PI * 2);
        ctx.fillStyle = '#2563eb';
        ctx.fill();

        ctx.beginPath();
        ctx.arc(x, y, 2, 0, Math.PI * 2);
        ctx.fillStyle = '#ffffff';
        ctx.fill();
    });

    ctx.fillStyle = '#64748b';
    ctx.font = '12px Inter, Arial, sans-serif';

    ctx.fillText(max.toFixed(2) + ' kg', 6, padding + 4);
    ctx.fillText(min.toFixed(2) + ' kg', 6, height - padding + 4);
})();
@endif
@endsection
