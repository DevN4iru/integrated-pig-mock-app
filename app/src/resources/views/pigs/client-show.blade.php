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
.client-grid-two { display: grid; grid-template-columns: minmax(0, 1fr) minmax(320px, 0.8fr); gap: 20px; align-items: start; }
.client-info-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
.client-field { border: 1px solid var(--line); border-radius: 14px; background: var(--panel-2); padding: 13px; }
.client-field label { display: block; color: var(--muted); font-size: 12px; margin-bottom: 4px; }
.client-field strong { display: block; color: var(--text); font-size: 15px; }
.client-section-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 14px; }
.client-section-head h3 { margin: 0 0 4px; }
.client-section-head p { color: var(--muted); font-size: 13px; }
.client-list { display: grid; gap: 10px; }
.client-row { display: grid; grid-template-columns: minmax(120px, 0.35fr) minmax(0, 1fr) auto; gap: 12px; align-items: center; border: 1px solid var(--line); border-radius: 14px; background: #fff; padding: 12px; }
.client-row-main strong { display: block; margin-bottom: 3px; }
.client-row-main span, .client-row-date, .client-muted { color: var(--muted); font-size: 13px; }
.client-protocol-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
.client-protocol-box { border: 1px solid var(--line); border-radius: 14px; background: var(--panel-2); padding: 14px; }
.client-protocol-box label { color: var(--muted); font-size: 12px; display: block; margin-bottom: 4px; }
.client-protocol-box strong { font-size: 24px; }
.client-protocol-actions { display: flex; justify-content: flex-end; align-items: center; gap: 8px; }
.client-protocol-actions form { margin: 0; }
.client-value-toggle { margin-top: 14px; border: 1px solid var(--line); border-radius: 14px; background: #fff; padding: 14px; display: flex; justify-content: space-between; gap: 14px; align-items: center; }
.client-value-toggle h4 { margin: 0 0 4px; }
.client-value-toggle p { color: var(--muted); font-size: 13px; margin: 0; }
.client-value-form { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; justify-content: flex-end; }
.client-value-check { display: flex; gap: 8px; align-items: center; font-weight: 700; color: var(--text); }
.client-value-check input { width: auto; }
@media (max-width: 980px) { .client-grid-two, .client-info-grid, .client-protocol-grid, .client-row, .client-value-toggle { grid-template-columns: 1fr; } .client-protocol-actions, .client-value-form { justify-content: flex-start; } .client-value-toggle { display: grid; } }
@endsection

@section('content')
    @php
        $pig->loadMissing([
            'pen',
            'healthLogs',
            'feedLogs',
            'reproductionCyclesAsSow.boar',
            'protocolExecutions.rule.template',
            'protocolExecutions.medication',
            'protocolExecutions.vaccination',
        ]);

        $dateAdded = $pig->date_added ? substr((string) $pig->date_added, 0, 10) : '—';
        $weight = is_numeric($pig->computed_weight) ? number_format((float) $pig->computed_weight, 2) . ' kg' : ($pig->computed_weight ?: '—');
        $penName = $pig->pen?->name ?? 'Unassigned';
        $statusLabel = ucfirst((string) $pig->lifecycle_state);
        $statusClass = match ($pig->lifecycle_state) {
            'dead' => 'red',
            'sold' => 'orange',
            'archived' => 'blue',
            default => 'green',
        };
        $isValueExcluded = (bool) ($pig->exclude_from_value_computation ?? false);
        $assetValueDisplay = $isValueExcluded ? 'Not counted' : '₱ ' . number_format((float) ($pig->asset_value ?? 0), 2);

        $weightLogs = $pig->healthLogs
            ->filter(fn ($log) => $log->purpose === 'weight_update' && $log->weight !== null)
            ->sortByDesc(fn ($log) => sprintf('%s-%010d', (string) ($log->log_date ?? ''), (int) $log->id))
            ->values();

        $assignedFeeds = $pig->feedLogs
            ->sortByDesc(fn ($feed) => sprintf('%s-%010d', (string) ($feed->start_feed_date ?? ''), (int) $feed->id))
            ->values();

        $breedingRecords = $pig->reproductionCyclesAsSow
            ->sortByDesc(fn ($cycle) => sprintf('%s-%010d', optional($cycle->service_date)->format('Y-m-d') ?? (string) $cycle->service_date, (int) $cycle->id))
            ->values();

        $latestActualFarrowingCycle = $breedingRecords
            ->filter(fn ($cycle) => $cycle->actual_farrow_date !== null)
            ->sortByDesc(fn ($cycle) => sprintf('%s-%010d', optional($cycle->actual_farrow_date)->format('Y-m-d') ?? '', (int) $cycle->id))
            ->first();

        $isBornPigletProtocolCandidate = strtolower((string) $pig->pig_source) === 'birthed'
            && $pig->reproduction_cycle_id !== null
            && $breedingRecords->isEmpty();
        $isLactatingSowProtocolCandidate = strtolower((string) $pig->sex) === 'female'
            && $latestActualFarrowingCycle !== null;

        $protocol = ($isBornPigletProtocolCandidate || $isLactatingSowProtocolCandidate)
            ? $pig->protocol_summary
            : null;

        $protocolDueToday = collect($protocol['due_today'] ?? []);
        $protocolUpcoming = collect($protocol['upcoming'] ?? []);
        $protocolOverdue = collect($protocol['overdue'] ?? []);
        $protocolOverdueIds = $protocolOverdue->pluck('rule_id')->map(fn ($id) => (string) $id)->all();
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
                <div class="client-field"><label>Breed</label><strong>{{ $pig->breed ?: '—' }}</strong></div>
                <div class="client-field"><label>Sex</label><strong>{{ ucfirst((string) $pig->sex) ?: '—' }}</strong></div>
                <div class="client-field"><label>Pen</label><strong>{{ $penName }}</strong></div>
                <div class="client-field"><label>Age</label><strong>{{ $pig->age_display }}</strong></div>
                <div class="client-field"><label>Date Added</label><strong>{{ $dateAdded }}</strong></div>
                <div class="client-field"><label>Current Weight</label><strong>{{ $weight }}</strong></div>
                <div class="client-field"><label>Farm Value</label><strong>{{ $assetValueDisplay }}</strong></div>
                <div class="client-field"><label>Value Status</label><strong>{{ $isValueExcluded ? 'Excluded from totals' : 'Included in totals' }}</strong></div>
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

        @if ($protocol)
            <div class="panel-card">
                <div class="client-section-head">
                    <div>
                        <h3>Medication Program</h3>
                        <p>
                            @if ($isBornPigletProtocolCandidate)
                                This program started from this registered piglet's birth date.
                            @else
                                This program started from the sow's recorded farrowing date.
                            @endif
                            Click <strong>Mark Done</strong> after the item is performed.
                        </p>
                    </div>
                </div>

                <div class="client-protocol-grid">
                    <div class="client-protocol-box"><label>Due Today</label><strong>{{ $protocolDueToday->count() }}</strong></div>
                    <div class="client-protocol-box"><label>Upcoming</label><strong>{{ $protocolUpcoming->count() }}</strong></div>
                    <div class="client-protocol-box"><label>Overdue</label><strong>{{ $protocolOverdue->count() }}</strong></div>
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
                                <div class="client-row-date">{{ $log->log_date ? substr((string) $log->log_date, 0, 10) : '—' }}</div>
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
                                    <span>{{ number_format((float) $feed->quantity, 2) }} {{ $feed->unit }} · {{ ucfirst((string) $feed->status) }}</span>
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
                                <div class="client-row-date">{{ $cycle->service_date ? $cycle->service_date->toDateString() : '—' }}</div>
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
