@extends('layouts.app')

@section('title', 'Breeding Case')
@section('page_title', 'Breeding Case')
@section('page_subtitle', 'Breeding case, next step, and timeline.')

@section('top_actions')
    <a href="{{ route('reproduction-cycles.index') }}" class="btn">Back to Breeding</a>

    @if($cycle->sow)
        <a href="{{ route('pigs.show', $cycle->sow) }}" class="btn">Open Sow Profile</a>
    @endif

    <a href="{{ route('reproduction-cycles.edit', $cycle) }}" class="btn">Edit Metadata</a>
@endsection

@section('styles')
.case-grid {
    display: grid;
    gap: 20px;
}

.case-grid-two {
    display: grid;
    grid-template-columns: 1.15fr 0.85fr;
    gap: 20px;
}

.timeline-stack {
    display: grid;
    gap: 16px;
}

.timeline-item {
    border: 1px solid var(--line);
    border-radius: 16px;
    background: #fff;
    padding: 16px;
}

.timeline-item-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 10px;
}

.timeline-item-title {
    display: grid;
    gap: 4px;
}

.timeline-meta {
    color: var(--muted);
    font-size: 13px;
}

.timeline-fields {
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: 12px;
}

.timeline-field {
    border: 1px solid var(--line);
    border-radius: 12px;
    padding: 10px 12px;
    background: #fafcff;
}

.timeline-field label {
    display: block;
    margin-bottom: 4px;
    color: var(--muted);
    font-size: 12px;
}

.timeline-field div {
    font-weight: 600;
}

.summary-note {
    color: var(--muted);
    font-size: 13px;
}

.flow-note {
    margin-top: 10px;
    color: var(--muted);
    font-size: 13px;
}

.action-card {
    border: 1px dashed var(--line);
    border-radius: 16px;
    padding: 16px;
    background: #fbfdff;
}

.case-grid > .panel-card,
.case-grid-two > .panel-card {
    border-color: #dbe4f0;
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.055);
    position: relative;
    overflow: hidden;
}

.case-grid > .panel-card::before,
.case-grid-two > .panel-card::before {
    content: "";
    position: absolute;
    inset: 0 0 auto 0;
    height: 3px;
    background: linear-gradient(90deg, var(--accent), rgba(37, 99, 235, 0.18), transparent);
}

.case-grid .section-title {
    padding-bottom: 14px;
    border-bottom: 1px solid #e2e8f0;
    margin-bottom: 18px;
}

.case-status-badges {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.case-next-step {
    border: 1px solid #bfdbfe;
    border-radius: 16px;
    padding: 16px;
    background: #eff6ff;
    margin-bottom: 16px;
}

.case-next-step strong {
    display: block;
    color: #1e3a8a;
    margin-bottom: 6px;
}

.case-next-step p {
    color: #1e40af;
    font-size: 13px;
    line-height: 1.45;
    margin-bottom: 12px;
}

.case-action-row {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.case-guide-toggle {
    border: 1px solid #dbe4f0;
    border-radius: 16px;
    background: #fff;
    overflow: hidden;
}

.case-guide-toggle summary {
    list-style: none;
    cursor: pointer;
    padding: 14px 16px;
    display: flex;
    justify-content: space-between;
    gap: 14px;
    align-items: center;
    font-weight: 800;
    background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
}

.case-guide-toggle summary::-webkit-details-marker {
    display: none;
}

.case-guide-toggle summary small {
    display: block;
    color: var(--muted);
    font-size: 12px;
    font-weight: 500;
    margin-top: 3px;
}

.case-guide-toggle summary::after {
    content: "View";
    border: 1px solid var(--line);
    border-radius: 999px;
    padding: 7px 12px;
    font-size: 12px;
    color: var(--primary);
    background: #f8fbff;
}

.case-guide-toggle[open] summary::after {
    content: "Hide";
}

.case-guide-body {
    display: grid;
    gap: 10px;
    padding: 14px;
    border-top: 1px solid #e2e8f0;
    background: #fbfdff;
}

.case-guide-row {
    border: 1px solid #dbe4f0;
    border-radius: 14px;
    background: #fff;
    padding: 12px;
}

.case-guide-row strong {
    display: block;
    margin-bottom: 4px;
}

.case-guide-row span {
    color: var(--muted);
    font-size: 13px;
    line-height: 1.45;
}

.timeline-item {
    border-color: #dbe4f0;
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.035);
}

.timeline-field {
    border-color: #dbe4f0;
    background: linear-gradient(180deg, #f8fbff 0%, #f6f9fd 100%);
}


@media (max-width: 1200px) {
    .case-grid-two,
    .timeline-fields {
        grid-template-columns: 1fr;
    }
}
@endsection

@section('content')
    @php
        $cycle->loadMissing(['sow.pen', 'boar', 'updates.donorBoar']);

        $displayStatus = $cycle->display_status;

        $statusBadgeClass = match($displayStatus) {
            \App\Models\ReproductionCycle::STATUS_PREGNANT => 'green',
            \App\Models\ReproductionCycle::STATUS_DUE_SOON => 'blue',
            \App\Models\ReproductionCycle::STATUS_FARROWED => 'blue',
            \App\Models\ReproductionCycle::STATUS_NOT_PREGNANT => 'red',
            \App\Models\ReproductionCycle::STATUS_RETURNED_TO_HEAT => 'orange',
            \App\Models\ReproductionCycle::STATUS_CLOSED => 'orange',
            default => 'orange',
        };

        $pregnancyBadgeClass = match($cycle->pregnancy_result) {
            \App\Models\ReproductionCycle::PREGNANCY_RESULT_PREGNANT => 'green',
            \App\Models\ReproductionCycle::PREGNANCY_RESULT_NOT_PREGNANT => 'red',
            default => 'blue',
        };

        $recommendedPenLabel = $cycle->recommended_pen_type
            ? ucfirst(str_replace('_', ' ', (string) $cycle->recommended_pen_type))
            : '—';

        $timeline = $cycle->updates
            ->sortByDesc(fn ($update) => sprintf(
                '%s-%010d',
                optional($update->event_date)->format('Y-m-d') ?? (string) $update->event_date,
                (int) $update->id
            ))
            ->values();

        $canAddProgress = !empty($availableUpdateEvents);
        $registeredBornPigletsCount = (int) ($cycle->born_piglets_count ?? 0);
        $canRegisterBornPiglets = $cycle->actual_farrow_date && (int) ($cycle->born_alive ?? 0) > 0 && $registeredBornPigletsCount === 0;
        $hasRegisteredBornPiglets = $registeredBornPigletsCount > 0;
        $showExpectedFarrow = $cycle->pregnancy_result === \App\Models\ReproductionCycle::PREGNANCY_RESULT_PREGNANT || $cycle->actual_farrow_date;
        $canStartNextAttempt = $displayStatus === \App\Models\ReproductionCycle::STATUS_RETURNED_TO_HEAT;
        $nextAttemptNumber = $cycle->current_attempt_number + 1;
        $pregnancyCheckDueDate = $cycle->pregnancy_check_due_date;
        $returnToHeatWindowEndDate = $cycle->return_to_heat_window_end_date;
        $pregnancyCheckIsDue = $pregnancyCheckDueDate && $pregnancyCheckDueDate->copy()->startOfDay()->lessThanOrEqualTo(now()->startOfDay());

        $preFarrowRows = collect();

        if ($cycle->expected_farrow_date && !$cycle->actual_farrow_date && $cycle->pregnancy_result === \App\Models\ReproductionCycle::PREGNANCY_RESULT_PREGNANT) {
            $preFarrowRows = collect(\App\Services\PreFarrowReminderSchedule::items())->map(function ($row) use ($cycle) {
                $dueDate = $cycle->expected_farrow_date->copy()->subDays((int) $row['days_before'])->startOfDay();
                $today = now()->startOfDay();

                $row['due_date'] = $dueDate;
                $row['status'] = $dueDate->lt($today) ? 'Overdue' : ($dueDate->isSameDay($today) ? 'Due Today' : 'Upcoming');
                $row['badge'] = $dueDate->lt($today) ? 'red' : ($dueDate->isSameDay($today) ? 'orange' : 'blue');

                return $row;
            })->values();
        }
    @endphp

    <div class="case-grid">
        <div class="panel-card">
            <div class="section-title">
                <div>
                    <h3>Current Case Snapshot</h3>
                    <p>Latest status, important dates, and the next action for this sow.</p>
                </div>

                <div class="case-status-badges">
                    <span class="badge {{ $statusBadgeClass }}">{{ $cycle->status_label }}</span>
                    <span class="badge {{ $pregnancyBadgeClass }}">{{ $cycle->pregnancy_result_label }}</span>
                    <span class="badge blue">Attempt {{ $cycle->current_attempt_number }}</span>
                </div>
            </div>

            @if($displayStatus === \App\Models\ReproductionCycle::STATUS_SERVICED && !$pregnancyCheckIsDue)
                <div class="flash" style="margin-bottom: 16px;">
                    Pregnancy / heat check is usually due on
                    <strong>{{ $pregnancyCheckDueDate?->format('Y-m-d') ?? '—' }}</strong>
                    — Day {{ \App\Models\ReproductionCycle::pregnancyCheckStartDays() }} after service.
                    Early entry is allowed if you are encoding farm history.
                </div>
            @endif

            @if($displayStatus === \App\Models\ReproductionCycle::STATUS_SERVICED && $pregnancyCheckIsDue)
                <div class="flash success" style="margin-bottom: 16px;">
                    Pregnancy / heat check is due now. Watch for heat signs from
                    <strong>{{ $pregnancyCheckDueDate?->format('Y-m-d') ?? '—' }}</strong>
                    to <strong>{{ $returnToHeatWindowEndDate?->format('Y-m-d') ?? '—' }}</strong>.
                </div>
            @endif

            @if($showExpectedFarrow && in_array($displayStatus, [
                \App\Models\ReproductionCycle::STATUS_PREGNANT,
                \App\Models\ReproductionCycle::STATUS_DUE_SOON,
                \App\Models\ReproductionCycle::STATUS_FARROWED,
                \App\Models\ReproductionCycle::STATUS_CLOSED,
            ], true))
                <div class="flash success" style="margin-bottom: 16px;">
                    Expected farrow date:
                    <strong>{{ $cycle->expected_farrow_date?->format('Y-m-d') ?? '—' }}</strong>.
                </div>
            @endif

            @if($displayStatus === \App\Models\ReproductionCycle::STATUS_NOT_PREGNANT)
                <div class="flash" style="margin-bottom: 16px;">
                    This attempt is marked <strong>not pregnant</strong>. Record <strong>Returned to Heat</strong> if the sow cycled back and you want to unlock the next-attempt workflow.
                </div>
            @endif

            @if($canStartNextAttempt)
                <div class="flash success" style="margin-bottom: 16px;">
                    The sow has been marked <strong>returned to heat</strong>. You can now either <strong>close this case</strong> or <strong>start Attempt {{ $nextAttemptNumber }}</strong> using the copied setup as a default.
                </div>
            @endif

            @if($preFarrowRows->isNotEmpty())
                <div class="flash" style="margin-bottom: 16px;">
                    <strong>Pre-farrow checklist is active.</strong>
                    Follow the farm/vet medication plan before farrowing.
                    Do not auto-medicate without farm/vet/product-label guidance.
                </div>
            @endif

            @if($canRegisterBornPiglets)
                <div class="flash success" style="margin-bottom: 16px;">
                    Farrowing is recorded with <strong>{{ (int) $cycle->born_alive }}</strong> born-alive piglet(s). Use the <strong>Available Next Step</strong> box below to register the piglets.
                </div>
            @endif

            @if($hasRegisteredBornPiglets)
                <div class="flash success" style="margin-bottom: 16px;">
                    This litter already has <strong>{{ $registeredBornPigletsCount }}</strong> registered piglet record(s). Duplicate litter registration is now blocked.
                </div>
            @endif

            <div class="case-grid-two">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Sow</label>
                        <input type="text" value="{{ $cycle->sow?->ear_tag ?? '—' }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Current Pen</label>
                        <input type="text" value="{{ $cycle->sow?->pen?->name ?? '—' }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Breeding Type</label>
                        <input type="text" value="{{ $cycle->breeding_type_label }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>{{ $cycle->breeding_type === \App\Models\ReproductionCycle::BREEDING_TYPE_ARTIFICIAL_INSEMINATION ? 'Donor Boar' : 'Boar' }}</label>
                        <input type="text" value="{{ $cycle->boar?->ear_tag ?? '—' }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Service Date</label>
                        <input type="text" value="{{ $cycle->service_date?->format('Y-m-d') ?? '—' }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Pregnancy / Heat Check Due</label>
                        <input type="text" value="{{ $pregnancyCheckDueDate?->format('Y-m-d') ?? '—' }} — Day {{ \App\Models\ReproductionCycle::pregnancyCheckStartDays() }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Return-to-Heat Watch Window</label>
                        <input type="text" value="{{ $pregnancyCheckDueDate?->format('Y-m-d') ?? '—' }} to {{ $returnToHeatWindowEndDate?->format('Y-m-d') ?? '—' }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Pregnancy Check Recorded</label>
                        <input type="text" value="{{ $cycle->pregnancy_check_date?->format('Y-m-d') ?? '—' }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Expected Farrow Date</label>
                        <input type="text" value="{{ $showExpectedFarrow ? ($cycle->expected_farrow_date?->format('Y-m-d') ?? '—') : 'Hidden until pregnant' }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Actual Farrow Date</label>
                        <input type="text" value="{{ $cycle->actual_farrow_date?->format('Y-m-d') ?? '—' }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>AI Source Type</label>
                        <input type="text" value="{{ $cycle->semen_source_type ? (\App\Models\ReproductionCycle::semenSourceOptions()[$cycle->semen_source_type] ?? ucfirst(str_replace('_', ' ', (string) $cycle->semen_source_type))) : '—' }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>AI Source Notes / Supplier</label>
                        <input type="text" value="{{ $cycle->semen_source_name ?: '—' }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Recommended Pen</label>
                        <input type="text" value="{{ $recommendedPenLabel }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Total Semen Cost</label>
                        <input type="text" value="₱ {{ number_format((float) $cycle->total_semen_cost, 2) }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Total Service / Handling Cost</label>
                        <input type="text" value="₱ {{ number_format((float) $cycle->breeding_cost, 2) }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Total Breeding Exposure</label>
                        <input type="text" value="₱ {{ number_format((float) $cycle->total_breeding_exposure, 2) }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Total Born</label>
                        <input type="text" value="{{ $cycle->total_born ?? '—' }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Born Alive / Stillborn / Mummified</label>
                        <input type="text" value="{{ ($cycle->born_alive ?? '—') . ' / ' . ($cycle->stillborn ?? '—') . ' / ' . ($cycle->mummified ?? '—') }}" readonly>
                    </div>

                    <div class="form-group full">
                        <label>Notes</label>
                        <textarea rows="4" readonly>{{ $cycle->notes ?: '—' }}</textarea>
                    </div>
                </div>

                @if($preFarrowRows->isNotEmpty())
                    <div class="panel-card" style="height: fit-content;">
                        <div class="section-title">
                            <div>
                                <h3>Pre-Farrow Checklist</h3>
                                <p>Dates are counted backward from expected farrowing.</p>
                            </div>
                        </div>

                        <div class="timeline-stack">
                            @foreach($preFarrowRows as $row)
                                <div class="timeline-item">
                                    <div class="timeline-item-top">
                                        <div class="timeline-item-title">
                                            <strong>{{ $row['label'] }}</strong>
                                            <span class="timeline-meta">{{ $row['due_date']->format('Y-m-d') }} • {{ $row['days_before'] }} day(s) before farrow</span>
                                        </div>

                                        <span class="badge {{ $row['badge'] }}">{{ $row['status'] }}</span>
                                    </div>

                                    <div class="summary-note">{{ $row['note'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="panel-card" style="height: fit-content;">
                    <div class="section-title">
                        <div>
                            <h3>Quick Guide</h3>
                            <p>Short help for this breeding record.</p>
                        </div>
                    </div>

                    <details class="case-guide-toggle">
                        <summary>
                            <span>
                                View Case Guide
                                <small>Explains attempts, pregnancy updates, farrowing, and piglet registration.</small>
                            </span>
                        </summary>

                        <div class="case-guide-body">
                            <div class="case-guide-row">
                                <strong>What is an attempt?</strong>
                                <span>An attempt is one breeding service inside this same parent case. Attempt 1 is the first service. If the sow returns to heat, the next service becomes Attempt 2 inside the same case.</span>
                            </div>

                            <div class="case-guide-row">
                                <strong>Why keep attempts together?</strong>
                                <span>This keeps the sow’s breeding story clean: service, pregnancy check, return-to-heat, retry, farrowing, and piglets all stay under one parent record instead of scattered duplicate records.</span>
                            </div>

                            <div class="case-guide-row">
                                <strong>Pregnancy and return to heat</strong>
                                <span>Pregnancy / heat check usually starts on Day {{ \App\Models\ReproductionCycle::pregnancyCheckStartDays() }} after service. Watch for return-to-heat signs until Day {{ \App\Models\ReproductionCycle::returnToHeatWindowEndDays() }}. If pregnant, expected farrow date is computed from service date + {{ \App\Models\ReproductionCycle::gestationDays() }} days.</span>
                            </div>

                            <div class="case-guide-row">
                                <strong>Farrowing</strong>
                                <span>Actual farrow date starts the sow and piglet medication schedule. Future farrow dates are blocked to protect protocol timing.</span>
                            </div>

                            <div class="case-guide-row">
                                <strong>Registering piglets</strong>
                                <span>Born piglets should be registered from this farrowed case. That links each piglet to the dam, birth case, lineage, and medication program.</span>
                            </div>
                        </div>
                    </details>
                </div>
            </div>
        </div>

        <div class="case-grid-two">
            <div class="panel-card" id="progress-update-card">
                <div class="section-title">
                    <div>
                        <h3>Add Progress Update</h3>
                        <p>Add the next real event for this breeding case.</p>
                    </div>
                </div>

                @if($canRegisterBornPiglets || $canStartNextAttempt)
                    <div class="case-next-step">
                        @if($canRegisterBornPiglets)
                            <strong>Available Next Step: Register Born Piglets</strong>
                            <p>This farrowing has {{ (int) $cycle->born_alive }} born-alive piglet(s). Register them here so lineage, birth case, and medication program tracking are connected correctly.</p>
                            <div class="case-action-row">
                                <a href="{{ route('pigs.create-born-batch', $cycle) }}" class="btn primary">Register Born Piglets</a>
                            </div>
                        @elseif($canStartNextAttempt)
                            <strong>Available Next Step: Start Attempt {{ $nextAttemptNumber }}</strong>
                            <p>The sow returned to heat. Continue this same parent case with the next attempt, or close the case if breeding will stop here.</p>
                            <div class="case-action-row">
                                <a href="{{ route('reproduction-cycles.attempts.create', $cycle) }}" class="btn primary">Start Attempt {{ $nextAttemptNumber }}</a>
                                <a href="{{ route('reproduction-cycles.show', ['reproductionCycle' => $cycle, 'event_type' => \App\Models\ReproductionCycleUpdate::EVENT_CYCLE_CLOSED]) }}#progress-update-card" class="btn">Quick Close Cycle</a>
                            </div>
                        @endif
                    </div>
                @endif

                @if($canAddProgress)
                    @include('reproduction-cycle-updates._form', [
                        'cycle' => $cycle,
                        'availableUpdateEvents' => $availableUpdateEvents,
                        'pregnancyResultOptions' => $pregnancyResultOptions,
                    ])
                @else
                    <div class="empty-state">No more progress updates are available for this case in its current state.</div>
                @endif
            </div>

            <div class="panel-card">
                <div class="section-title">
                    <div>
                        <h3>Timeline Summary</h3>
                        <p>Quick totals for this breeding case.</p>
                    </div>
                </div>

                <div class="grid stats">
                    <div class="stat-card">
                        <div class="stat-top">
                            <span class="label">Timeline Events</span>
                            <span class="badge blue">Count</span>
                        </div>
                        <div class="stat-value">{{ $cycle->updates->count() }}</div>
                        <div class="stat-sub">All events already appended to this breeding case.</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-top">
                            <span class="label">Current Attempt</span>
                            <span class="badge blue">Live</span>
                        </div>
                        <div class="stat-value">{{ $cycle->current_attempt_number }}</div>
                        <div class="stat-sub">Unlimited retries are tracked inside the same parent case.</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-top">
                            <span class="label">Current Status</span>
                            <span class="badge {{ $statusBadgeClass }}">{{ $cycle->status_label }}</span>
                        </div>
                        <div class="stat-sub">Current latest parent-case state.</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-top">
                            <span class="label">Piglets Registered</span>
                            <span class="badge {{ $hasRegisteredBornPiglets ? 'green' : 'blue' }}">{{ $registeredBornPigletsCount }}</span>
                        </div>
                        <div class="stat-sub">Live piglet records linked to this farrowing case.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel-card">
            <div class="section-title">
                <div>
                    <h3>Timeline History</h3>
                    <p>Newest breeding events first.</p>
                </div>
            </div>

            @if($timeline->isEmpty())
                <div class="empty-state">No progress updates recorded yet.</div>
            @else
                <div class="timeline-stack">
                    @foreach($timeline as $update)
                        @php
                            $updateStatusBadgeClass = match($update->status_after_event) {
                                \App\Models\ReproductionCycle::STATUS_PREGNANT => 'green',
                                \App\Models\ReproductionCycle::STATUS_DUE_SOON => 'blue',
                                \App\Models\ReproductionCycle::STATUS_FARROWED => 'blue',
                                \App\Models\ReproductionCycle::STATUS_NOT_PREGNANT => 'red',
                                \App\Models\ReproductionCycle::STATUS_RETURNED_TO_HEAT => 'orange',
                                \App\Models\ReproductionCycle::STATUS_CLOSED => 'orange',
                                default => 'orange',
                            };

                            $updatePregnancyBadgeClass = match($update->pregnancy_result) {
                                \App\Models\ReproductionCycle::PREGNANCY_RESULT_PREGNANT => 'green',
                                \App\Models\ReproductionCycle::PREGNANCY_RESULT_NOT_PREGNANT => 'red',
                                default => 'blue',
                            };

                            $outcomeText = '—';

                            if ($update->total_recorded_outcome !== null || $update->actual_farrow_date) {
                                $parts = [];

                                if ($update->total_born !== null) {
                                    $parts[] = 'Total: ' . $update->total_born;
                                }

                                if ($update->born_alive !== null) {
                                    $parts[] = 'Alive: ' . $update->born_alive;
                                }

                                if ($update->stillborn !== null) {
                                    $parts[] = 'Stillborn: ' . $update->stillborn;
                                }

                                if ($update->mummified !== null) {
                                    $parts[] = 'Mummified: ' . $update->mummified;
                                }

                                $outcomeText = empty($parts) ? 'Recorded' : implode(' • ', $parts);
                            }
                        @endphp

                        <div class="timeline-item">
                            <div class="timeline-item-top">
                                <div class="timeline-item-title">
                                    <strong>{{ $update->event_type_label }}</strong>
                                    <span class="timeline-meta">{{ $update->event_date?->format('Y-m-d') ?? '—' }}</span>
                                </div>

                                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                    <span class="badge blue">{{ $update->attempt_label }}</span>

                                    @if($update->status_after_event)
                                        <span class="badge {{ $updateStatusBadgeClass }}">{{ $update->status_after_event_label }}</span>
                                    @endif

                                    @if($update->pregnancy_result)
                                        <span class="badge {{ $updatePregnancyBadgeClass }}">{{ $update->pregnancy_result_label }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="timeline-fields">
                                <div class="timeline-field">
                                    <label>Attempt</label>
                                    <div>{{ $update->attempt_label }}</div>
                                </div>

                                <div class="timeline-field">
                                    <label>Service Setup</label>
                                    <div>{{ $update->service_setup_label }}</div>
                                </div>

                                <div class="timeline-field">
                                    <label>Added Service Cost</label>
                                    <div>₱ {{ number_format((float) ($update->added_cost ?? 0), 2) }}</div>
                                </div>

                                <div class="timeline-field">
                                    <label>Actual Farrow Date</label>
                                    <div>{{ $update->actual_farrow_date?->format('Y-m-d') ?? '—' }}</div>
                                </div>

                                <div class="timeline-field">
                                    <label>Litter Outcome</label>
                                    <div>{{ $outcomeText }}</div>
                                </div>

                                <div class="timeline-field">
                                    <label>Notes</label>
                                    <div>{{ $update->notes ?: '—' }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
