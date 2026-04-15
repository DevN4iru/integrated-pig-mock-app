@extends('layouts.app')

@section('title', 'Edit Breeding Record')
@section('page_title', 'Edit Breeding Record')
@section('page_subtitle', 'Update this ongoing or completed reproduction cycle for the sow.')

@section('top_actions')
    <a href="{{ route('pigs.show', $pig) }}" class="btn">Back to Pig Profile</a>
    <a href="{{ route('reproduction-cycles.index') }}" class="btn">Breeding Records</a>
@endsection

@section('content')
    <div class="panel-card" style="margin-bottom: 20px;">
        <div class="section-title">
            <div>
                <h3>Sow Information</h3>
                <p>This reproduction cycle belongs to the sow below.</p>
            </div>
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
                <input type="text" value="{{ $pig->pen?->name ?? '—' }}" readonly>
            </div>
        </div>
    </div>

    <div class="panel-card">
        <div class="section-title">
            <div>
                <h3>Cycle Details</h3>
                <p>Update the same breeding case as results happen, instead of treating every stage like a separate finished record.</p>
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
                        @foreach($breedingTypeOptions as $value => $label)
                            <option value="{{ $value }}" {{ old('breeding_type', $cycle->breeding_type) === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="status">Cycle Status</label>
                    <select id="status" name="status" required>
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" {{ old('status', $cycle->status) === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
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
                </div>

                <div class="form-group" id="pregnancy_result_group">
                    <label for="pregnancy_result">Pregnancy Result</label>
                    <select id="pregnancy_result" name="pregnancy_result">
                        <option value="">Auto by status</option>
                        @foreach($pregnancyResultOptions as $value => $label)
                            <option value="{{ $value }}" {{ old('pregnancy_result', $cycle->pregnancy_result) === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" id="pregnancy_check_date_group">
                    <label for="pregnancy_check_date">Pregnancy Check Date</label>
                    <input
                        id="pregnancy_check_date"
                        name="pregnancy_check_date"
                        type="date"
                        value="{{ old('pregnancy_check_date', optional($cycle->pregnancy_check_date)->format('Y-m-d')) }}"
                    >
                </div>

                <div class="form-group">
                    <label for="expected_farrow_date">Expected Farrowing Date</label>
                    <input
                        id="expected_farrow_date"
                        name="expected_farrow_date"
                        type="date"
                        value="{{ old('expected_farrow_date', optional($cycle->expected_farrow_date)->format('Y-m-d')) }}"
                    >
                </div>

                <div class="form-group" id="actual_farrow_date_group">
                    <label for="actual_farrow_date">Actual Farrowing Date</label>
                    <input
                        id="actual_farrow_date"
                        name="actual_farrow_date"
                        type="date"
                        value="{{ old('actual_farrow_date', optional($cycle->actual_farrow_date)->format('Y-m-d')) }}"
                        max="{{ now()->toDateString() }}"
                    >
                </div>

                <div class="form-group" id="boar_group">
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

                <div class="form-group" id="semen_source_type_group">
                    <label for="semen_source_type">AI Semen Source Type</label>
                    <select id="semen_source_type" name="semen_source_type">
                        <option value="">Select semen source type</option>
                        @foreach($semenSourceOptions as $value => $label)
                            <option value="{{ $value }}" {{ old('semen_source_type', $cycle->semen_source_type) === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" id="semen_source_name_group">
                    <label for="semen_source_name">Purchased Semen Source / Supplier</label>
                    <input
                        id="semen_source_name"
                        name="semen_source_name"
                        type="text"
                        value="{{ old('semen_source_name', $cycle->semen_source_name) }}"
                        placeholder="Where the semen was bought"
                    >
                </div>

                <div class="form-group" id="semen_cost_group">
                    <label for="semen_cost">Purchased Semen Cost</label>
                    <input
                        id="semen_cost"
                        name="semen_cost"
                        type="number"
                        min="0"
                        step="0.01"
                        value="{{ old('semen_cost', number_format((float) $cycle->semen_cost, 2, '.', '')) }}"
                    >
                </div>

                <div class="form-group">
                    <label for="breeding_cost">Total Breeding Cost</label>
                    <input
                        id="breeding_cost"
                        name="breeding_cost"
                        type="number"
                        min="0"
                        step="0.01"
                        value="{{ old('breeding_cost', number_format((float) $cycle->breeding_cost, 2, '.', '')) }}"
                    >
                </div>

                <div class="form-group outcome-field">
                    <label for="total_born">Total Born</label>
                    <input
                        id="total_born"
                        name="total_born"
                        type="number"
                        min="0"
                        step="1"
                        value="{{ old('total_born', $cycle->total_born) }}"
                    >
                </div>

                <div class="form-group outcome-field">
                    <label for="born_alive">Born Alive</label>
                    <input
                        id="born_alive"
                        name="born_alive"
                        type="number"
                        min="0"
                        step="1"
                        value="{{ old('born_alive', $cycle->born_alive) }}"
                    >
                </div>

                <div class="form-group outcome-field">
                    <label for="stillborn">Stillborn</label>
                    <input
                        id="stillborn"
                        name="stillborn"
                        type="number"
                        min="0"
                        step="1"
                        value="{{ old('stillborn', $cycle->stillborn) }}"
                    >
                </div>

                <div class="form-group outcome-field">
                    <label for="mummified">Mummified</label>
                    <input
                        id="mummified"
                        name="mummified"
                        type="number"
                        min="0"
                        step="1"
                        value="{{ old('mummified', $cycle->mummified) }}"
                    >
                </div>

                <div class="form-group full">
                    <label for="notes">Notes</label>
                    <textarea
                        id="notes"
                        name="notes"
                        placeholder="Optional breeding notes, observations, or cycle remarks"
                    >{{ old('notes', $cycle->notes) }}</textarea>
                </div>
            </div>

            <div class="form-actions" style="display:flex; gap:10px; flex-wrap:wrap;">
                <button type="submit" class="btn primary">Update Breeding Record</button>
                <a href="{{ route('pigs.show', $pig) }}" class="btn">Cancel</a>
            </div>
        </form>

        <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--line);">
            <form method="POST" action="{{ route('reproduction-cycles.destroy', $cycle) }}" onsubmit="return confirm('Delete this breeding record permanently?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Delete Breeding Record</button>
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

function setOutcomeFieldsVisibility(visible) {
    document.querySelectorAll('.outcome-field').forEach((element) => {
        element.style.display = visible ? '' : 'none';
    });
}

function updateBreedingFormState() {
    const breedingType = document.getElementById('breeding_type')?.value || '';
    const semenSourceType = document.getElementById('semen_source_type')?.value || '';
    const status = document.getElementById('status')?.value || '';

    const needsPregnancyCheck = ['pregnant', 'not_pregnant', 'returned_to_heat', 'due_soon', 'farrowed'].includes(status);
    const isFarrowed = status === 'farrowed';

    setGroupVisibility('boar_group', breedingType === 'natural_mating' || breedingType === '');
    setGroupVisibility('semen_source_type_group', breedingType === 'artificial_insemination');
    setGroupVisibility('semen_source_name_group', breedingType === 'artificial_insemination' && semenSourceType === 'purchased');
    setGroupVisibility('semen_cost_group', breedingType === 'artificial_insemination' && semenSourceType === 'purchased');

    setGroupVisibility('pregnancy_result_group', needsPregnancyCheck);
    setGroupVisibility('pregnancy_check_date_group', needsPregnancyCheck);
    setGroupVisibility('actual_farrow_date_group', isFarrowed);
    setOutcomeFieldsVisibility(isFarrowed);
}

document.getElementById('breeding_type')?.addEventListener('change', updateBreedingFormState);
document.getElementById('semen_source_type')?.addEventListener('change', updateBreedingFormState);
document.getElementById('status')?.addEventListener('change', updateBreedingFormState);

updateBreedingFormState();
@endsection
