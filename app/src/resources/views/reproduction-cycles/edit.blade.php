@extends('layouts.app')

@section('title', 'Edit Breeding Case')
@section('page_title', 'Edit Breeding Case')
@section('page_subtitle', 'Edit base breeding metadata only. Biological progression stays in timeline events.')

@section('top_actions')
    <a href="{{ route('reproduction-cycles.show', $cycle) }}" class="btn">Back to Case</a>
    @if($pig)
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
    @endphp

    <div class="panel-card" style="margin-bottom: 20px;">
        <div class="section-title">
            <div>
                <h3>Current Case State</h3>
                <p>This page now edits base breeding metadata only. Timeline events control pregnancy, return-to-heat, farrowing, and closure.</p>
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
                <label>Pregnancy Result</label>
                <input type="text" value="{{ $cycle->pregnancy_result_label }}" readonly>
            </div>

            <div class="form-group">
                <label>Expected Farrow Date</label>
                <input type="text" value="{{ optional($cycle->expected_farrow_date)->format('Y-m-d') ?? '—' }}" readonly>
            </div>

            <div class="form-group">
                <label>Actual Farrow Date</label>
                <input type="text" value="{{ optional($cycle->actual_farrow_date)->format('Y-m-d') ?? '—' }}" readonly>
            </div>

            <div class="form-group">
                <label>Born Alive</label>
                <input type="text" value="{{ $cycle->born_alive ?? '—' }}" readonly>
            </div>
        </div>
    </div>

    <div class="panel-card">
        <div class="section-title">
            <div>
                <h3>Editable Base Metadata</h3>
                <p>You can still correct service metadata, breeding source details, and notes here without manually forcing breeding stages.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('reproduction-cycles.update', $cycle) }}">
            @csrf
            @method('PUT')

            <input type="hidden" name="status" value="{{ old('status', $cycle->status) }}">
            <input type="hidden" name="pregnancy_result" value="{{ old('pregnancy_result', $cycle->pregnancy_result) }}">
            <input type="hidden" name="pregnancy_check_date" value="{{ old('pregnancy_check_date', optional($cycle->pregnancy_check_date)->format('Y-m-d')) }}">
            <input type="hidden" name="actual_farrow_date" value="{{ old('actual_farrow_date', optional($cycle->actual_farrow_date)->format('Y-m-d')) }}">
            <input type="hidden" name="total_born" value="{{ old('total_born', $cycle->total_born) }}">
            <input type="hidden" name="born_alive" value="{{ old('born_alive', $cycle->born_alive) }}">
            <input type="hidden" name="stillborn" value="{{ old('stillborn', $cycle->stillborn) }}">
            <input type="hidden" name="mummified" value="{{ old('mummified', $cycle->mummified) }}">
            <input type="hidden" id="expected_farrow_date" name="expected_farrow_date" value="{{ old('expected_farrow_date', optional($cycle->expected_farrow_date)->format('Y-m-d')) }}">

            <div class="form-grid">
                <div class="form-group">
                    <label for="breeding_type">Breeding Type</label>
                    <select id="breeding_type" name="breeding_type" required>
                        <option value="">Select breeding type</option>
                        @foreach($breedingTypeOptions as $value => $label)
                            <option value="{{ $value }}" {{ old('breeding_type', $cycle->breeding_type) === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div id="boar_group" class="form-group">
                    <label for="boar_id">Boar</label>
                    <select id="boar_id" name="boar_id">
                        <option value="">Select boar</option>
                        @foreach($boars as $boar)
                            <option value="{{ $boar->id }}" {{ (string) old('boar_id', $cycle->boar_id) === (string) $boar->id ? 'selected' : '' }}>
                                {{ $boar->ear_tag }} — {{ $boar->breed }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div id="semen_source_type_group" class="form-group">
                    <label for="semen_source_type">Semen Source Type</label>
                    <select id="semen_source_type" name="semen_source_type">
                        <option value="">Select source type</option>
                        @foreach($semenSourceOptions as $value => $label)
                            <option value="{{ $value }}" {{ old('semen_source_type', $cycle->semen_source_type) === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div id="semen_source_name_group" class="form-group">
                    <label for="semen_source_name">Semen Source Name</label>
                    <input id="semen_source_name" name="semen_source_name" type="text" value="{{ old('semen_source_name', $cycle->semen_source_name) }}">
                </div>

                <div id="semen_cost_group" class="form-group">
                    <label for="semen_cost">Semen Cost</label>
                    <input id="semen_cost" name="semen_cost" type="number" step="0.01" min="0" value="{{ old('semen_cost', $cycle->semen_cost) }}">
                </div>

                <div class="form-group">
                    <label for="service_date">Service Date</label>
                    <input id="service_date" name="service_date" type="date" value="{{ old('service_date', optional($cycle->service_date)->format('Y-m-d')) }}" required>
                </div>

                <div class="form-group">
                    <label>Expected Farrow Date (Auto)</label>
                    <input id="expected_farrow_preview" type="text" value="{{ old('expected_farrow_date', optional($cycle->expected_farrow_date)->format('Y-m-d')) }}" readonly>
                </div>

                <div class="form-group">
                    <label for="breeding_cost">Base Breeding Cost</label>
                    <input id="breeding_cost" name="breeding_cost" type="number" step="0.01" min="0" value="{{ old('breeding_cost', $cycle->breeding_cost) }}">
                </div>

                <div class="form-group full">
                    <label for="notes">Case Notes</label>
                    <textarea id="notes" name="notes" rows="4">{{ old('notes', $cycle->notes) }}</textarea>
                </div>
            </div>

            <div class="flash" style="margin-top: 16px;">
                The expected farrow date is auto-derived from <strong>service date + 114 days</strong>. Pregnancy confirmation and farrowing should be recorded from the case timeline, not forced here.
            </div>

            <div class="form-actions">
                <button type="submit" class="btn primary">Save Metadata</button>
                <a href="{{ route('reproduction-cycles.show', $cycle) }}" class="btn">Cancel</a>
            </div>
        </form>

        <div style="margin-top:16px;">
            <form method="POST" action="{{ route('reproduction-cycles.destroy', $cycle) }}">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger">Delete Record</button>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
function setGroupVisibility(elementId, visible) {
    const element = document.getElementById(elementId);
    if (!element) return;
    element.style.display = visible ? '' : 'none';
}

function updateBreedingEditState() {
    const breedingType = document.getElementById('breeding_type')?.value || '';
    const semenSourceType = document.getElementById('semen_source_type')?.value || '';

    setGroupVisibility('boar_group', breedingType === 'natural_mating' || breedingType === '');
    setGroupVisibility('semen_source_type_group', breedingType === 'artificial_insemination');
    setGroupVisibility('semen_source_name_group', breedingType === 'artificial_insemination');
    setGroupVisibility('semen_cost_group', breedingType === 'artificial_insemination' && semenSourceType === 'purchased');
}

function autofillExpectedFarrowDate() {
    const serviceDateInput = document.getElementById('service_date');
    const hiddenExpected = document.getElementById('expected_farrow_date');
    const previewExpected = document.getElementById('expected_farrow_preview');

    if (!serviceDateInput || !hiddenExpected || !previewExpected) {
        return;
    }

    if (!serviceDateInput.value) {
        hiddenExpected.value = '';
        previewExpected.value = '';
        return;
    }

    const baseDate = new Date(serviceDateInput.value + 'T00:00:00');
    if (Number.isNaN(baseDate.getTime())) {
        hiddenExpected.value = '';
        previewExpected.value = '';
        return;
    }

    baseDate.setDate(baseDate.getDate() + 114);

    const yyyy = baseDate.getFullYear();
    const mm = String(baseDate.getMonth() + 1).padStart(2, '0');
    const dd = String(baseDate.getDate()).padStart(2, '0');
    const formatted = `${yyyy}-${mm}-${dd}`;

    hiddenExpected.value = formatted;
    previewExpected.value = formatted;
}

document.getElementById('breeding_type')?.addEventListener('change', updateBreedingEditState);
document.getElementById('semen_source_type')?.addEventListener('change', updateBreedingEditState);
document.getElementById('service_date')?.addEventListener('change', autofillExpectedFarrowDate);

updateBreedingEditState();
autofillExpectedFarrowDate();
@endsection
