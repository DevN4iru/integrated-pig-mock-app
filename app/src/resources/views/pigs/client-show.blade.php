@extends('layouts.app')

@section('title', 'Pig Profile')
@section('page_title', 'Pig Profile')
@section('page_subtitle', 'Simple view for daily farm handling.')

@section('top_actions')
    @php
        $isArchivedTop = $pig->is_archived_lifecycle;
        $isOperationalLockedTop = $pig->isOperationallyLocked();
        $isFemaleTop = strtolower((string) $pig->sex) === 'female';
    @endphp

    <a href="{{ route('pigs.index') }}" class="btn">Back to Pig List</a>

    @if (!$isArchivedTop)
        <button type="button" class="btn" onclick="openPigEditPrompt('{{ route('pigs.edit', $pig) }}')">Edit Pig</button>

        @if (!$isOperationalLockedTop)
            <a href="{{ route('health-logs.create', $pig) }}" class="btn primary">Update Weight</a>
            <a href="{{ route('feed-logs.create', $pig) }}" class="btn">Assign Feed</a>
            <a href="{{ route('pig-transfers.create', $pig) }}" class="btn">Transfer Pig</a>
            <a href="{{ route('sales.create', $pig) }}" class="btn btn-warning">Record Sale</a>
            <a href="{{ route('mortality.create', $pig) }}" class="btn btn-danger">Record Mortality</a>

            @if ($isFemaleTop)
                <a href="{{ route('reproduction-cycles.create', $pig) }}" class="btn">Add Breeding Record</a>
            @endif
        @endif
    @else
        <form method="POST" action="{{ route('pigs.restore', $pig->id) }}" style="display:inline-block;" onsubmit="return confirm('Restore this pig back to the active list?');">
            @csrf
            <button type="submit" class="btn">Restore</button>
        </form>
    @endif
@endsection

@section('styles')
.client-profile-stack { display: grid; gap: 20px; }
.client-profile-stack > .panel-card,
.client-profile-stack > .client-grid-two > .panel-card {
    border-color: #dbe4f0;
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.055);
    position: relative;
    overflow: hidden;
}

.client-profile-stack > .panel-card::before,
.client-profile-stack > .client-grid-two > .panel-card::before {
    content: "";
    position: absolute;
    inset: 0 0 auto 0;
    height: 3px;
    background: linear-gradient(90deg, var(--accent), rgba(37, 99, 235, 0.18), transparent);
}

.client-grid-two { display: grid; grid-template-columns: minmax(0, 1fr) minmax(320px, 0.8fr); gap: 20px; align-items: start; }
.client-info-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
.client-field {
    border: 1px solid #dbe4f0;
    border-radius: 14px;
    background: linear-gradient(180deg, #f8fbff 0%, #f6f9fd 100%);
    padding: 13px;
}
.client-field label { display: block; color: var(--muted); font-size: 12px; margin-bottom: 4px; }
.client-field strong { display: block; color: var(--text); font-size: 15px; }
.client-section-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 14px;
    padding-bottom: 13px;
    border-bottom: 1px solid #e2e8f0;
}
.client-section-head h3 { margin: 0 0 4px; }
.client-section-head p { color: var(--muted); font-size: 13px; }
.client-list { display: grid; gap: 10px; }
.client-row {
    display: grid;
    grid-template-columns: minmax(120px, 0.35fr) minmax(0, 1fr) auto;
    gap: 12px;
    align-items: center;
    border: 1px solid #dbe4f0;
    border-radius: 14px;
    background: #fff;
    padding: 12px;
    position: relative;
}

.client-row::before {
    content: "";
    position: absolute;
    left: 0;
    top: 12px;
    bottom: 12px;
    width: 3px;
    border-radius: 999px;
    background: transparent;
}

.client-row:hover {
    background: #fbfdff;
}

.client-row:hover::before {
    background: rgba(37, 99, 235, 0.28);
}
.client-row-main strong { display: block; margin-bottom: 3px; }
.client-row-main span, .client-row-date, .client-muted { color: var(--muted); font-size: 13px; }
.client-protocol-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
.client-protocol-box {
    border: 1px solid #dbe4f0;
    border-radius: 14px;
    background: linear-gradient(180deg, #f8fbff 0%, #f6f9fd 100%);
    padding: 14px;
}
.client-protocol-box label { color: var(--muted); font-size: 12px; display: block; margin-bottom: 4px; }
.client-protocol-box strong { font-size: 24px; }
.client-protocol-actions { display: flex; justify-content: flex-end; align-items: center; gap: 8px; }
.client-protocol-actions form { margin: 0; }
.client-protocol-history { margin-top: 14px; border: 1px solid #dbe4f0; border-radius: 16px; background: #fff; overflow: hidden; box-shadow: 0 10px 24px rgba(15, 23, 42, 0.045); }
.client-protocol-history summary { list-style: none; cursor: pointer; padding: 14px 16px; display: flex; align-items: center; justify-content: space-between; gap: 12px; font-weight: 800; color: var(--text); background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%); }
.client-protocol-history summary::-webkit-details-marker { display: none; }
.client-protocol-history summary small { display: block; margin-top: 3px; color: var(--muted); font-size: 12px; font-weight: 500; }
.client-protocol-history summary::after { content: "View"; flex: 0 0 auto; border: 1px solid var(--line); border-radius: 999px; padding: 7px 12px; font-size: 12px; color: var(--primary); background: #f8fbff; }
.client-protocol-history[open] summary::after { content: "Hide"; }
.client-protocol-history-list { display: grid; gap: 10px; padding: 14px; border-top: 1px solid #e2e8f0; background: #fbfdff; }
.client-history-row { display: grid; grid-template-columns: minmax(120px, 0.25fr) minmax(0, 1fr) auto; gap: 12px; align-items: center; border: 1px solid #dbe4f0; border-radius: 14px; background: #fff; padding: 12px; }
.client-history-row strong { display: block; color: var(--text); margin-bottom: 3px; }
.client-history-row span { display: block; color: var(--muted); font-size: 13px; line-height: 1.35; }
.client-history-date { color: var(--muted); font-size: 13px; }
.client-value-toggle {
    margin-top: 14px;
    border: 1px solid #dbe4f0;
    border-radius: 14px;
    background: #fff;
    padding: 14px;
    display: flex;
    justify-content: space-between;
    gap: 14px;
    align-items: center;
}
.client-value-toggle h4 { margin: 0 0 4px; }
.client-value-toggle p { color: var(--muted); font-size: 13px; margin: 0; }
.client-value-form { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; justify-content: flex-end; }
.client-value-check { display: flex; gap: 8px; align-items: center; font-weight: 700; color: var(--text); }
.client-value-check input { width: auto; }

@media (max-width: 980px) {
    .client-grid-two,
    .client-info-grid,
    .client-protocol-grid,
    .client-row,
    .client-value-toggle {
        grid-template-columns: 1fr;
    }

    .client-protocol-actions,
    .client-value-form {
        justify-content: flex-start;
    }

    .client-history-row {
        grid-template-columns: 1fr;
        gap: 6px;
    }

    .client-protocol-history summary {
        align-items: flex-start;
    }

    .client-value-toggle {
        display: grid;
    }
}
@endsection

@section('content')
    @php
        $pig->loadMissing([
            'pen',
            'healthLogs',
            'feedLogs',
            'sales',
            'mortalityLogs',
            'motherSow',
            'sireBoar',
            'birthCycle:id,actual_farrow_date',
            'reproductionCyclesAsSow.boar',
            'protocolExecutions.rule.template',
            'protocolExecutions.medication',
            'protocolExecutions.vaccination',
        ]);

        $pigValueService = app(\App\Services\PigValueService::class);
        $protocolEligibility = app(\App\Services\ProtocolEligibilityService::class);

        $dateAdded = $pig->date_added
            ? \Carbon\Carbon::parse($pig->date_added)->toDateString()
            : '—';

        $weight = is_numeric($pig->computed_weight)
            ? number_format((float) $pig->computed_weight, 2) . ' kg'
            : ($pig->computed_weight ?: '—');

        $penName = $pig->pen?->name ?? 'Unassigned';
        $statusLabel = ucfirst((string) $pig->lifecycle_state);

        $statusClass = match ($pig->lifecycle_state) {
            'dead' => 'red',
            'sold' => 'orange',
            'archived' => 'blue',
            default => 'green',
        };

        $isValueExcluded = $pigValueService->isExcludedFromFarmValue($pig);
        $assetValueDisplay = $pigValueService->valueDisplay($pig);
        $valueStatusLabel = $pigValueService->valueStatusLabel($pig);

        $weightLogs = $pig->healthLogs
            ->filter(fn ($log) => $log->purpose === 'weight_update' && $log->weight !== null)
            ->sortByDesc(fn ($log) => sprintf('%s-%010d', (string) ($log->log_date ?? ''), (int) $log->id))
            ->values();

        $assignedFeeds = $pig->feedLogs
            ->sortByDesc(fn ($feed) => sprintf('%s-%010d', (string) ($feed->start_feed_date ?? ''), (int) $feed->id))
            ->values();

        $latestSale = $pig->sales
            ->sortByDesc(fn ($sale) => sprintf(
                '%s-%010d',
                optional($sale->sold_date)->format('Y-m-d') ?? (string) $sale->sold_date,
                (int) $sale->id
            ))
            ->first();

        $latestMortality = $pig->mortalityLogs
            ->sortByDesc(fn ($mortality) => sprintf(
                '%s-%010d',
                optional($mortality->death_date)->format('Y-m-d') ?? (string) $mortality->death_date,
                (int) $mortality->id
            ))
            ->first();

        $breedingRecords = $pig->reproductionCyclesAsSow
            ->sortByDesc(fn ($cycle) => sprintf(
                '%s-%010d',
                optional($cycle->service_date)->format('Y-m-d') ?? (string) $cycle->service_date,
                (int) $cycle->id
            ))
            ->values();

        $isBornPigletProtocolCandidate = $protocolEligibility->qualifiesForRegisteredPigletProtocol($pig);
        $isLactatingSowProtocolCandidate = $protocolEligibility->qualifiesForLactatingSowProtocol($pig);

        $protocol = $protocolEligibility->qualifiesForAnyClientProtocol($pig)
            ? $pig->protocol_summary
            : null;

        $protocolDueToday = collect($protocol['due_today'] ?? []);
        $protocolUpcoming = collect($protocol['upcoming'] ?? []);
        $protocolOverdue = collect($protocol['overdue'] ?? []);
        $protocolOverdueIds = $protocolOverdue->pluck('rule_id')->map(fn ($id) => (string) $id)->all();

        $protocolHistory = $pig->protocolExecutions
            ->filter(fn ($execution) => strtolower((string) $execution->status) === 'completed')
            ->sortByDesc(fn ($execution) => sprintf(
                '%s-%010d',
                optional($execution->executed_date)->format('Y-m-d') ?? (string) ($execution->executed_date ?? $execution->scheduled_for_date ?? ''),
                (int) $execution->id
            ))
            ->values();
    @endphp

    <div class="client-profile-stack">
        @if ($pig->isOperationallyLocked())
            <div class="flash error">{{ $pig->operationalLockMessage('records') }}</div>
        @endif

        <div class="panel-card">
            <div class="client-section-head">
                <div>
                    <h3>{{ $pig->ear_tag }}</h3>
                    <p>Basic pig record and current handling information.</p>
                </div>
                <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
            </div>

            <div class="client-info-grid">
                <div class="client-field">
                    <label>Breed</label>
                    <strong>{{ $pig->breed ?: '—' }}</strong>
                </div>

                <div class="client-field">
                    <label>Sex</label>
                    <strong>{{ ucfirst((string) $pig->sex) ?: '—' }}</strong>
                </div>

                <div class="client-field">
                    <label>Pen</label>
                    <strong>{{ $penName }}</strong>
                </div>

                <div class="client-field">
                    <label>Age</label>
                    <strong>{{ $pig->age_display }}</strong>
                </div>

                <div class="client-field">
                    <label>Date Added</label>
                    <strong>{{ $dateAdded }}</strong>
                </div>

                <div class="client-field">
                    <label>Current Weight</label>
                    <strong>{{ $weight }}</strong>
                </div>

                <div class="client-field">
                    <label>Farm Value</label>
                    <strong>{{ $assetValueDisplay }}</strong>
                </div>

                <div class="client-field">
                    <label>Value Status</label>
                    <strong>{{ $valueStatusLabel }}</strong>
                </div>
            </div>

            @if (!$pig->trashed())
                <div class="client-value-toggle">
                    <div>
                        <h4>Breeding Stock Value</h4>
                        <p>Use this for breeder boars or sows that should stay as breeding stock, not saleable inventory.</p>
                    </div>

                    @if (!$pig->isOperationallyLocked())
                        <form method="POST" action="{{ route('pigs.breeding-stock-value.update', $pig) }}" class="client-value-form">
                            @csrf
                            @method('PUT')

                            <input type="hidden" name="exclude_from_value_computation" value="0">

                            <label class="client-value-check">
                                <input type="checkbox" name="exclude_from_value_computation" value="1" {{ $isValueExcluded ? 'checked' : '' }}>
                                Do not include in farm value totals
                            </label>

                            <button type="submit" class="btn primary">Save</button>
                        </form>
                    @else
                        <span class="badge blue">Locked</span>
                    @endif
                </div>
            @endif
        </div>

        <div class="panel-card">
            <div class="client-section-head">
                <div>
                    <h3>Lineage</h3>
                    <p>Basic parent and birth record information.</p>
                </div>
            </div>

            <div class="client-info-grid">
                <div class="client-field">
                    <label>Dam / Mother Sow</label>
                    <strong>{{ $pig->motherSow?->ear_tag ?? '—' }}</strong>
                </div>

                <div class="client-field">
                    <label>Sire / Boar</label>
                    <strong>{{ $pig->sireBoar?->ear_tag ?? '—' }}</strong>
                </div>

                <div class="client-field">
                    <label>Birth Case</label>
                    <strong>{{ $pig->birthCycle?->actual_farrow_date ? $pig->birthCycle->actual_farrow_date->format('Y-m-d') : '—' }}</strong>
                </div>
            </div>
        </div>

        <div class="panel-card">
            <div class="client-section-head">
                <div>
                    <h3>Sale / Mortality Records</h3>
                    <p>Lifecycle records are kept visible for sold or dead pigs.</p>
                </div>

                @if (!$latestSale && !$latestMortality && !$pig->isOperationallyLocked())
                    <div class="client-protocol-actions">
                        <a href="{{ route('sales.create', $pig) }}" class="btn btn-warning">Record Sale</a>
                        <a href="{{ route('mortality.create', $pig) }}" class="btn btn-danger">Record Mortality</a>
                    </div>
                @endif
            </div>

            @if ($latestSale)
                <div class="client-info-grid">
                    <div class="client-field">
                        <label>Sale Date</label>
                        <strong>{{ $latestSale->sold_date ? $latestSale->sold_date->toDateString() : '—' }}</strong>
                    </div>

                    <div class="client-field">
                        <label>Final Sale Price</label>
                        <strong>₱ {{ number_format((float) $latestSale->price, 2) }}</strong>
                    </div>

                    <div class="client-field">
                        <label>Buyer</label>
                        <strong>{{ $latestSale->buyer ?: '—' }}</strong>
                    </div>

                    <div class="client-field">
                        <label>Weight at Sale</label>
                        <strong>{{ $latestSale->weight_at_sale !== null ? number_format((float) $latestSale->weight_at_sale, 2) . ' kg' : '—' }}</strong>
                    </div>

                    <div class="client-field">
                        <label>Price/kg Snapshot</label>
                        <strong>{{ $latestSale->price_per_kg_at_sale !== null ? '₱ ' . number_format((float) $latestSale->price_per_kg_at_sale, 2) : '—' }}</strong>
                    </div>

                    <div class="client-field">
                        <label>Recommended Price Snapshot</label>
                        <strong>{{ $latestSale->recommended_price !== null ? '₱ ' . number_format((float) $latestSale->recommended_price, 2) : '—' }}</strong>
                    </div>
                </div>

                @if (!$pig->trashed())
                    <div class="form-actions" style="margin-top: 14px;">
                        <a href="{{ route('sales.edit', [$pig, $latestSale]) }}" class="btn">Edit Sale Record</a>
                    </div>
                @endif
            @endif

            @if ($latestMortality)
                <div class="client-info-grid" style="{{ $latestSale ? 'margin-top: 14px;' : '' }}">
                    <div class="client-field">
                        <label>Date of Death</label>
                        <strong>{{ $latestMortality->death_date ? $latestMortality->death_date->toDateString() : '—' }}</strong>
                    </div>

                    <div class="client-field">
                        <label>Cause</label>
                        <strong>{{ $latestMortality->cause ?: '—' }}</strong>
                    </div>

                    <div class="client-field">
                        <label>Loss Value Snapshot</label>
                        <strong>{{ $latestMortality->loss_value !== null ? '₱ ' . number_format((float) $latestMortality->loss_value, 2) : '—' }}</strong>
                    </div>

                    <div class="client-field">
                        <label>Weight at Death</label>
                        <strong>{{ $latestMortality->weight_at_death !== null ? number_format((float) $latestMortality->weight_at_death, 2) . ' kg' : '—' }}</strong>
                    </div>

                    <div class="client-field">
                        <label>Price/kg Snapshot</label>
                        <strong>{{ $latestMortality->price_per_kg_at_death !== null ? '₱ ' . number_format((float) $latestMortality->price_per_kg_at_death, 2) : '—' }}</strong>
                    </div>

                    <div class="client-field">
                        <label>Notes</label>
                        <strong>{{ $latestMortality->notes ?: '—' }}</strong>
                    </div>
                </div>

                @if (!$pig->trashed())
                    <div class="form-actions" style="margin-top: 14px;">
                        <a href="{{ route('mortality.edit', [$pig, $latestMortality]) }}" class="btn">Edit Mortality Record</a>
                    </div>
                @endif
            @endif

            @if (!$latestSale && !$latestMortality)
                <div class="empty-state">No sale or mortality record yet.</div>
            @endif
        </div>

        @if ($protocol)
            <div class="panel-card">
                <div class="client-section-head">
                    <div>
                        <h3>Medication Program</h3>
                        <p>
                            @if ($isBornPigletProtocolCandidate)
                                This program started from this registered piglet's actual farrowing date.
                            @elseif ($isLactatingSowProtocolCandidate)
                                This program started from the sow's recorded farrowing date.
                            @else
                                This medication program is available for eligible piglets and lactating sows only.
                            @endif
                            Click <strong>Mark Done</strong> after the item is performed.
                        </p>
                    </div>
                </div>

                <div class="client-protocol-grid">
                    <div class="client-protocol-box">
                        <label>Due Today</label>
                        <strong>{{ $protocolDueToday->count() }}</strong>
                    </div>

                    <div class="client-protocol-box">
                        <label>Upcoming</label>
                        <strong>{{ $protocolUpcoming->count() }}</strong>
                    </div>

                    <div class="client-protocol-box">
                        <label>Overdue</label>
                        <strong>{{ $protocolOverdue->count() }}</strong>
                    </div>
                </div>

                @php
                    $programItems = collect([$protocolDueToday, $protocolUpcoming, $protocolOverdue])->flatten(1)->values();
                @endphp

                @if ($programItems->isNotEmpty())
                    <div class="client-list" style="margin-top: 14px;">
                        @foreach ($programItems as $item)
                            @php
                                $itemRuleId = (string) ($item['rule_id'] ?? '');
                                $isOverdueItem = $itemRuleId !== '' && in_array($itemRuleId, $protocolOverdueIds, true);
                                $executionStatus = $item['execution_status'] ?? null;

                                $productForCompletion = trim((string) ($item['product_note'] ?? '')) !== ''
                                    ? $item['product_note']
                                    : ($item['action'] ?? 'Protocol item');

                                $doseForCompletion = trim((string) ($item['dosage_note'] ?? '')) !== ''
                                    ? $item['dosage_note']
                                    : 'Recorded';
                            @endphp

                            <div class="client-row">
                                <div class="client-row-date">
                                    {{ $item['due_start'] ?? '—' }}
                                    @if (($item['due_end'] ?? null) && ($item['due_end'] ?? null) !== ($item['due_start'] ?? null))
                                        to {{ $item['due_end'] }}
                                    @endif
                                </div>

                                <div class="client-row-main">
                                    <strong>{{ $item['action'] ?? 'Medication item' }}</strong>
                                    <span>{{ $item['product_note'] ?? $item['dosage_note'] ?? 'Follow program guide.' }}</span>
                                </div>

                                <div class="client-protocol-actions">
                                    <span class="badge {{ $isOverdueItem ? 'orange' : 'blue' }}">
                                        {{ $executionStatus ? ucfirst((string) $executionStatus) : 'Pending' }}
                                    </span>

                                    @if (!$pig->isOperationallyLocked())
                                        <form method="POST" action="{{ route('protocol-executions.upsert', $pig) }}">
                                            @csrf

                                            <input type="hidden" name="protocol_rule_id" value="{{ $item['rule_id'] }}">
                                            <input type="hidden" name="scheduled_for_date" value="{{ $item['due_start'] }}">
                                            <input type="hidden" name="status" value="completed">
                                            <input type="hidden" name="executed_date" value="{{ now()->toDateString() }}">
                                            <input type="hidden" name="actual_product_name" value="{{ $productForCompletion }}">
                                            <input type="hidden" name="actual_dose" value="{{ $doseForCompletion }}">
                                            <input type="hidden" name="actual_cost" value="0">
                                            <input type="hidden" name="notes" value="Completed from client pig profile.">

                                            <button type="submit" class="btn primary">Mark Done</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state" style="margin-top: 14px;">No pending medication program items.</div>
                @endif

                <details class="client-protocol-history">
                    <summary>
                        <span>
                            Protocol History
                            <small>Completed medication program items for this pig.</small>
                        </span>
                    </summary>

                    @if ($protocolHistory->isEmpty())
                        <div class="client-protocol-history-list">
                            <div class="empty-state">No completed protocol items yet.</div>
                        </div>
                    @else
                        <div class="client-protocol-history-list">
                            @foreach ($protocolHistory as $historyItem)
                                <div class="client-history-row">
                                    <div class="client-history-date">
                                        {{ $historyItem->executed_date ? $historyItem->executed_date->toDateString() : ($historyItem->scheduled_for_date ? $historyItem->scheduled_for_date->toDateString() : '—') }}
                                    </div>

                                    <div>
                                        <strong>{{ $historyItem->rule?->action ?? 'Protocol item' }}</strong>
                                        <span>
                                            {{ $historyItem->actual_product_name ?: ($historyItem->rule?->product_note ?? 'No product recorded') }}
                                            @if ($historyItem->actual_dose)
                                                · {{ $historyItem->actual_dose }}
                                            @endif
                                            @if ($historyItem->notes)
                                                · {{ $historyItem->notes }}
                                            @endif
                                        </span>
                                    </div>

                                    <span class="badge green">Done</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </details>
            </div>
        @endif

        <div class="client-grid-two">
            <div class="panel-card">
                <div class="client-section-head">
                    <div>
                        <h3>Weight History</h3>
                        <p>Dated weight records only.</p>
                    </div>

                    @if (!$pig->isOperationallyLocked())
                        <a href="{{ route('health-logs.create', $pig) }}" class="btn primary">Update Weight</a>
                    @endif
                </div>

                @if ($weightLogs->isEmpty())
                    <div class="empty-state">No weight records yet.</div>
                @else
                    <div class="client-list">
                        @foreach ($weightLogs as $log)
                            <div class="client-row">
                                <div class="client-row-date">
                                    {{ $log->log_date ? substr((string) $log->log_date, 0, 10) : '—' }}
                                </div>

                                <div class="client-row-main">
                                    <strong>{{ number_format((float) $log->weight, 2) }} kg</strong>
                                    <span>{{ $log->notes ?: 'No notes.' }}</span>
                                </div>

                                @if (!$pig->isOperationallyLocked())
                                    <a href="{{ route('health-logs.edit', [$pig, $log]) }}" class="btn">Edit</a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="panel-card">
                <div class="client-section-head">
                    <div>
                        <h3>Assigned Feeds</h3>
                        <p>Current or past feed assignment periods.</p>
                    </div>

                    @if (!$pig->isOperationallyLocked())
                        <a href="{{ route('feed-logs.create', $pig) }}" class="btn">Assign Feed</a>
                    @endif
                </div>

                @if ($assignedFeeds->isEmpty())
                    <div class="empty-state">No assigned feeds yet.</div>
                @else
                    <div class="client-list">
                        @foreach ($assignedFeeds as $feed)
                            <div class="client-row">
                                <div class="client-row-date">
                                    {{ $feed->start_feed_date ? substr((string) $feed->start_feed_date, 0, 10) : '—' }}
                                    @if ($feed->end_feed_date)
                                        to {{ substr((string) $feed->end_feed_date, 0, 10) }}
                                    @endif
                                </div>

                                <div class="client-row-main">
                                    <strong>{{ $feed->feed_type }}</strong>
                                    <span>
                                        {{ number_format((float) $feed->quantity, 2) }} {{ $feed->unit }}
                                        @if (trim((string) $feed->status) !== '')
                                            · {{ ucfirst((string) $feed->status) }}
                                        @endif
                                    </span>
                                </div>

                                @if (!$pig->isOperationallyLocked())
                                    <a href="{{ route('feed-logs.edit', [$pig, $feed]) }}" class="btn">Edit</a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        @if (strtolower((string) $pig->sex) === 'female')
            <div class="panel-card">
                <div class="client-section-head">
                    <div>
                        <h3>Breeding Records</h3>
                        <p>Breeding and farrowing records for this sow.</p>
                    </div>

                    @if (!$pig->isOperationallyLocked())
                        <a href="{{ route('reproduction-cycles.create', $pig) }}" class="btn">Add Breeding Record</a>
                    @endif
                </div>

                @if ($breedingRecords->isEmpty())
                    <div class="empty-state">No breeding records yet.</div>
                @else
                    <div class="client-list">
                        @foreach ($breedingRecords as $cycle)
                            <div class="client-row">
                                <div class="client-row-date">
                                    {{ $cycle->service_date ? $cycle->service_date->toDateString() : '—' }}
                                </div>

                                <div class="client-row-main">
                                    <strong>{{ $cycle->status_label ?? ucfirst((string) $cycle->status) }}</strong>
                                    <span>Boar: {{ $cycle->boar?->ear_tag ?? 'Not recorded' }}</span>
                                </div>

                                <a href="{{ route('reproduction-cycles.show', $cycle) }}" class="btn">Open</a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    </div>

    <script>
        function openPigEditPrompt(url) {
            const code = prompt('Enter edit code to continue:');
            if (code === null) return;

            window.location.href = `${url}?code=${encodeURIComponent(code)}`;
        }
    </script>
@endsection
