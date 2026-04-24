@extends('layouts.app')

@php
    $boarRiskMap = $boarRiskMap ?? [];
    $initialSelectedBoarId = (string) ($initialSelectedBoarId ?? old('boar_id', $cycle->boar_id));
@endphp

@section('title', 'Edit Breeding Case')
@section('page_title', 'Edit Breeding Case')
@section('page_subtitle', 'Edit only the current attempt metadata. Biological progression stays in timeline events.')

@section('top_actions')
    <a href="{{ route('reproduction-cycles.show', $cycle) }}" class="btn">Back to Case</a>
    @if(!empty($pig))
        <a href="{{ route('pigs.show', $pig) }}" class="btn">Open Sow Profile</a>
    @endif
@endsection

@section('content')
    @php
        $displayStatus = $cycle->display_status ?? $cycle->status;
        $statusBadgeClass = match($displayStatus) {
            \App\Models\ReproductionCycle::STATUS_PREGNANT => 'green',
            \App\Models\ReproductionCycle::STATUS_DUE_SOON => 'blue',
            \App\Models\ReproductionCycle::STATUS_FARROWED => 'blue',
            \App\Models\ReproductionCycle::STATUS_NOT_PREGNANT => 'red',
            \App\Models\ReproductionCycle::STATUS_RETURNED_TO_HEAT => 'orange',
            \App\Models\ReproductionCycle::STATUS_CLOSED => 'orange',
            default => 'orange',
        };

        $showExpectedSummary = $cycle->pregnancy_result === \App\Models\ReproductionCycle::PREGNANCY_RESULT_PREGNANT || $cycle->actual_farrow_date;
    @endphp

    <div class="panel-card" style="margin-bottom: 20px;">
        <div class="section-title">
            <div>
                <h3>Current Case State</h3>
                <p>This page edits current attempt metadata only. Timeline events still control pregnancy, return to heat, farrowing, retries, and closure.</p>
            </div>
            <span class="badge {{ $statusBadgeClass }}">{{ $cycle->status_label }}</span>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label>Sow Ear Tag</label>
                <input type="text" value="{{ $pig->ear_tag ?? '—' }}" readonly>
            </div>

            <div class="form-group">
                <label>Sow Breed</label>
                <input type="text" value="{{ $pig->breed ?? '—' }}" readonly>
            </div>

            <div class="form-group">
                <label>Current Attempt</label>
                <input type="text" value="Attempt {{ $cycle->current_attempt_number }}" readonly>
            </div>

            <div class="form-group">
                <label>Pregnancy Result</label>
                <input type="text" value="{{ $cycle->pregnancy_result_label }}" readonly>
            </div>

            <div class="form-group">
                <label>Expected Farrow Date</label>
                <input type="text" value="{{ $showExpectedSummary ? (optional($cycle->expected_farrow_date)->format('Y-m-d') ?? '—') : 'Hidden until pregnant' }}" readonly>
            </div>

            <div class="form-group">
                <label>Actual Farrow Date</label>
                <input type="text" value="{{ optional($cycle->actual_farrow_date)->format('Y-m-d') ?? '—' }}" readonly>
            </div>
        </div>
    </div>

    <div class="panel-card">
        <div class="section-title">
            <div>
                <h3>Editable Current Attempt Metadata</h3>
                <p>You can still correct service date, donor boar, AI source details, cumulative service cost, semen cost, and notes here.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('reproduction-cycles.update', $cycle) }}">
            @csrf
            @method('PUT')

            <div class="form-grid">
                <div class="form-group">
                    <label for="breeding_type">Breeding Type</label>
                    <select id="breeding_type" name="breeding_type" required>
                        <option value="">Select breeding type</option>
                        @foreach(($breedingTypeOptions ?? []) as $value => $label)
                            <option value="{{ $value }}" {{ old('breeding_type', $cycle->breeding_type) === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('breeding_type')
                        <div class="error-text">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="service_date">Service Date</label>
                    <input
                        id="service_date"
                        name="service_date"
                        type="date"
                        value="{{ old('service_date', optional($cycle->service_date)->format('Y-m-d')) }}"
                        max="{{ now()->toDateString() }}"
                        required
                    >
                    @error('service_date')
                        <div class="error-text">{{ $message }}</div>
                    @enderror
                </div>

                <div id="boar_group" class="form-group">
                    <label for="boar_id" id="boar_label">Boar</label>
                    <select id="boar_id" name="boar_id">
                        <option value="">Select boar</option>
                        @foreach(($boars ?? []) as $boar)
                            <option value="{{ $boar->id }}" {{ (string) old('boar_id', $cycle->boar_id) === (string) $boar->id ? 'selected' : '' }}>
                                {{ $boar->ear_tag }} — {{ $boar->breed }}
                            </option>
                        @endforeach
                    </select>

                    <div id="boar_risk_context" class="flash" style="display:none; margin-top:10px;">
                        <div style="display:flex; justify-content:space-between; gap:10px; align-items:center; flex-wrap:wrap;">
                            <strong>Selected Boar Context</strong>
                            <span id="boar_risk_status_badge" class="badge blue">—</span>
                        </div>

                        <div class="form-grid" style="margin-top: 12px;">
                            <div class="form-group">
                                <label>Boar</label>
                                <input id="boar_risk_boar" type="text" value="" readonly>
                            </div>

                            <div class="form-group">
                                <label>Risk Result</label>
                                <input id="boar_risk_reason" type="text" value="" readonly>
                            </div>

                            <div class="form-group">
                                <label>Boar Dam</label>
                                <input id="boar_risk_dam" type="text" value="" readonly>
                            </div>

                            <div class="form-group">
                                <label>Boar Sire</label>
                                <input id="boar_risk_sire" type="text" value="" readonly>
                            </div>
                        </div>

                        <div class="metric-note" id="boar_risk_message"></div>
                    </div>

                    <small class="metric-note" id="boar_note" style="display:none;">Required for natural mating and locally sourced AI donor boar selection.</small>

                    @error('boar_id')
                        <div class="error-text">{{ $message }}</div>
                    @enderror
                </div>

                <div id="semen_source_type_group" class="form-group">
                    <label for="semen_source_type">AI Semen Source Type</label>
                    <select id="semen_source_type" name="semen_source_type">
                        <option value="">Select source type</option>
                        @foreach(($semenSourceOptions ?? []) as $value => $label)
                            <option value="{{ $value }}" {{ old('semen_source_type', $cycle->semen_source_type) === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('semen_source_type')
                        <div class="error-text">{{ $message }}</div>
                    @enderror
                </div>

                <div id="semen_source_name_group" class="form-group">
                    <label for="semen_source_name" id="semen_source_name_label">AI Semen Source / Supplier</label>
                    <input
                        id="semen_source_name"
                        name="semen_source_name"
                        type="text"
                        value="{{ old('semen_source_name', $cycle->semen_source_name) }}"
                    >
                    <small class="metric-note" id="semen_source_name_note">Required for purchased AI. Optional for locally sourced AI notes.</small>
                    @error('semen_source_name')
                        <div class="error-text">{{ $message }}</div>
                    @enderror
                </div>

                <div id="semen_cost_group" class="form-group">
                    <label for="semen_cost">Current Attempt Semen Cost</label>
                    <input
                        id="semen_cost"
                        name="semen_cost"
                        type="number"
                        step="0.01"
                        min="0"
                        value="{{ old('semen_cost', number_format((float) $cycle->semen_cost, 2, '.', '')) }}"
                    >
                    <small class="metric-note">Tracked separately from cumulative service / handling cost.</small>
                    @error('semen_cost')
                        <div class="error-text">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="breeding_cost">Cumulative Service / Handling Cost</label>
                    <input
                        id="breeding_cost"
                        name="breeding_cost"
                        type="number"
                        step="0.01"
                        min="0"
                        value="{{ old('breeding_cost', number_format((float) $cycle->breeding_cost, 2, '.', '')) }}"
                    >
                    <small class="metric-note">This excludes semen. Total breeding exposure is calculated from cumulative service / handling cost plus cumulative semen cost.</small>
                    @error('breeding_cost')
                        <div class="error-text">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group full">
                    <label for="notes">Case Notes</label>
                    <textarea id="notes" name="notes" rows="4">{{ old('notes', $cycle->notes) }}</textarea>
                    @error('notes')
                        <div class="error-text">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="flash" style="margin-top: 16px;">
                The projected farrow date remains hidden until the case is on the pregnant path. If this case is already pregnant or farrowed, the saved expected farrow date still stays derived from <strong>service date + 114 days</strong>. Accounting uses <strong>service / handling cost + semen cost</strong>.
            </div>

            <div class="form-actions">
                <button type="submit" class="btn primary">Save Metadata</button>
                <a href="{{ route('reproduction-cycles.show', $cycle) }}" class="btn">Cancel</a>
            </div>
        </form>

        <div style="margin-top:16px;">
            <form method="POST" action="{{ route('reproduction-cycles.destroy', $cycle) }}" onsubmit="return confirm('Delete this breeding case? This removes the parent case and its timeline.');">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger">Delete Record</button>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
document.addEventListener('DOMContentLoaded', function () {
    const boarRiskMap = @json($boarRiskMap);
    const initialSelectedBoarId = @json($initialSelectedBoarId);

    function setGroupVisibility(elementId, visible) {
        const element = document.getElementById(elementId);
        if (!element) return;
        element.style.display = visible ? '' : 'none';
    }

    function renderSelectedBoarContext() {
        const boarId = document.getElementById('boar_id')?.value || '';
        const context = document.getElementById('boar_risk_context');

        if (!context || !boarId || !boarRiskMap[boarId]) {
            if (context) context.style.display = 'none';
            return;
        }

        const risk = boarRiskMap[boarId];

        context.style.display = '';
        context.className = risk.blocked ? 'flash error' : 'flash success';

        const statusBadge = document.getElementById('boar_risk_status_badge');
        const boarField = document.getElementById('boar_risk_boar');
        const reasonField = document.getElementById('boar_risk_reason');
        const damField = document.getElementById('boar_risk_dam');
        const sireField = document.getElementById('boar_risk_sire');
        const messageField = document.getElementById('boar_risk_message');

        if (statusBadge) {
            statusBadge.textContent = risk.status_label || '—';
            statusBadge.className = 'badge ' + (risk.status_badge_class || 'blue');
        }

        if (boarField) {
            boarField.value = [risk.boar_ear_tag, risk.boar_breed].filter(Boolean).join(' — ');
        }

        if (reasonField) {
            reasonField.value = risk.reason_label || '—';
        }

        if (damField) {
            damField.value = risk.dam_ear_tag || 'Unknown';
        }

        if (sireField) {
            sireField.value = risk.sire_ear_tag || 'Unknown';
        }

        if (messageField) {
            messageField.textContent = risk.message || '';
        }
    }

    function updateBreedingEditState() {
        const breedingType = document.getElementById('breeding_type')?.value || '';
        const semenSourceType = document.getElementById('semen_source_type')?.value || '';
        const boarLabel = document.getElementById('boar_label');
        const boarNote = document.getElementById('boar_note');
        const sourceNameLabel = document.getElementById('semen_source_name_label');
        const sourceNameNote = document.getElementById('semen_source_name_note');
        const boarRiskContext = document.getElementById('boar_risk_context');

        const showBoar = breedingType === 'natural_mating' || (breedingType === 'artificial_insemination' && semenSourceType === 'local');
        const showAiFields = breedingType === 'artificial_insemination';
        const showSemenCost = showAiFields && semenSourceType === 'purchased';

        setGroupVisibility('boar_group', showBoar);
        setGroupVisibility('semen_source_type_group', showAiFields);
        setGroupVisibility('semen_source_name_group', showAiFields);
        setGroupVisibility('semen_cost_group', showSemenCost);

        if (boarLabel) {
            boarLabel.textContent = breedingType === 'artificial_insemination' ? 'Donor Boar' : 'Boar';
        }

        if (boarNote) {
            boarNote.style.display = showBoar ? '' : 'none';
        }

        if (sourceNameLabel) {
            sourceNameLabel.textContent = semenSourceType === 'local'
                ? 'Local Source Notes (Optional)'
                : 'AI Semen Source / Supplier';
        }

        if (sourceNameNote) {
            sourceNameNote.textContent = semenSourceType === 'local'
                ? 'Optional notes about the local source. Donor boar selection is required.'
                : 'Required for purchased AI. Optional for locally sourced AI notes.';
        }

        if (!showBoar && boarRiskContext) {
            boarRiskContext.style.display = 'none';
        } else {
            renderSelectedBoarContext();
        }
    }

    const boarSelect = document.getElementById('boar_id');
    if (boarSelect && initialSelectedBoarId && boarSelect.value === '') {
        boarSelect.value = initialSelectedBoarId;
    }

    document.getElementById('breeding_type')?.addEventListener('change', updateBreedingEditState);
    document.getElementById('semen_source_type')?.addEventListener('change', updateBreedingEditState);
    document.getElementById('boar_id')?.addEventListener('change', renderSelectedBoarContext);

    updateBreedingEditState();
    renderSelectedBoarContext();
});
@endsection
