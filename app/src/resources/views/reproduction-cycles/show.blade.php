@extends('layouts.app')

@section('title', 'Breeding Case')
@section('page_title', 'Breeding Case')
@section('page_subtitle', 'Parent case summary, append-only progress updates, and timeline history.')

@section('top_actions')
    @php
        $registeredBornPigletsCount = (int) ($cycle->born_piglets_count ?? 0);
        $canRegisterBornPigletsTop = $cycle->actual_farrow_date && (int) ($cycle->born_alive ?? 0) > 0 && $registeredBornPigletsCount === 0;
    @endphp

    <a href="{{ route('reproduction-cycles.index') }}" class="btn">Back to Breeding</a>

    @if($cycle->sow)
        <a href="{{ route('pigs.show', $cycle->sow) }}" class="btn">Open Sow Profile</a>
    @endif

    @if($canRegisterBornPigletsTop)
        <a href="{{ route('pigs.create-born-batch', $cycle) }}" class="btn primary">Register Born Piglets</a>
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
    grid-template-columns: repeat(4, minmax(0, 1fr));
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

@media (max-width: 1200px) {
    .case-grid-two,
    .timeline-fields {
        grid-template-columns: 1fr;
    }
}
@endsection

@section('content')
    @php
        $cycle->loadMissing(['sow.pen', 'boar', 'updates']);

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
    @endphp

    <div class="case-grid">
        <div class="panel-card">
            <div class="section-title">
                <div>
                    <h3>Current Case Snapshot</h3>
                    <p>This parent record stores the latest current state of the breeding case.</p>
                </div>

                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    <span class="badge {{ $statusBadgeClass }}">{{ $cycle->status_label }}</span>
                    <span class="badge {{ $pregnancyBadgeClass }}">{{ $cycle->pregnancy_result_label }}</span>
                </div>
            </div>

            @if(in_array($displayStatus, [
                \App\Models\ReproductionCycle::STATUS_PREGNANT,
                \App\Models\ReproductionCycle::STATUS_DUE_SOON,
            ], true))
                <div class="flash success" style="margin-bottom: 16px;">
                    Expected farrow date is automatically tracked from <strong>service date + 114 days</strong>. Current expected farrow date:
                    <strong>{{ $cycle->expected_farrow_date?->format('Y-m-d') ?? '—' }}</strong>.
                </div>
            @endif

            @if($canRegisterBornPiglets)
                <div class="flash success" style="margin-bottom: 16px;">
                    Farrowing is recorded with <strong>{{ (int) $cycle->born_alive }}</strong> born-alive piglet(s). Continue the workflow by clicking <strong>Register Born Piglets</strong>.
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
                        <label>Boar</label>
                        <input type="text" value="{{ $cycle->boar?->ear_tag ?? '—' }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Breeding Type</label>
                        <input type="text" value="{{ $cycle->breeding_type_label }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Service Date</label>
                        <input type="text" value="{{ $cycle->service_date?->format('Y-m-d') ?? '—' }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Pregnancy Check Date</label>
                        <input type="text" value="{{ $cycle->pregnancy_check_date?->format('Y-m-d') ?? '—' }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Expected Farrow Date</label>
                        <input type="text" value="{{ $cycle->expected_farrow_date?->format('Y-m-d') ?? '—' }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Actual Farrow Date</label>
                        <input type="text" value="{{ $cycle->actual_farrow_date?->format('Y-m-d') ?? '—' }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Recommended Pen</label>
                        <input type="text" value="{{ $recommendedPenLabel }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Total Breeding Cost</label>
                        <input type="text" value="₱ {{ number_format((float) $cycle->breeding_cost, 2) }}" readonly>
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

                <div class="panel-card" style="height: fit-content;">
                    <div class="section-title">
                        <div>
                            <h3>Workflow Note</h3>
                            <p>Flow A is enforced in this breeding case.</p>
                        </div>
                    </div>

                    <div class="summary-note">
                        Pregnancy check records the diagnosis. Returned to heat is a separate later event. Due soon is date-derived, not manually submitted. Farrowing records only farrowing-specific fields. Cycle closure is only for ended cases.
                    </div>

                    <div class="flow-note">
                        Base breeding metadata can still be corrected from the edit page, but biological progression stays in append-only timeline events.
                    </div>
                </div>
            </div>
        </div>

        <div class="case-grid-two">
            <div class="panel-card">
                <div class="section-title">
                    <div>
                        <h3>Add Progress Update</h3>
                        <p>Append a new event to the breeding case timeline. Each event has its own allowed fields.</p>
                    </div>
                </div>

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
                        <p>Quick case totals based on the append-only timeline history.</p>
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
                            <span class="label">Current Status</span>
                            <span class="badge {{ $statusBadgeClass }}">{{ $cycle->status_label }}</span>
                        </div>
                        <div class="stat-sub">Current latest parent-case state.</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-top">
                            <span class="label">Current Pregnancy Result</span>
                            <span class="badge {{ $pregnancyBadgeClass }}">{{ $cycle->pregnancy_result_label }}</span>
                        </div>
                        <div class="stat-sub">Current latest decision state for this case.</div>
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
                    <p>Append-only breeding case events in reverse chronological order.</p>
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
                                    <label>Added Cost</label>
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
