@extends('layouts.app')

@section('title', 'New Breeding Record')
@section('page_title', 'New Breeding Record')
@section('page_subtitle', 'Create a reproduction cycle for this sow.')

@section('top_actions')
    <a href="{{ route('pigs.show', $pig) }}" class="btn">Back to Pig Profile</a>
    <a href="{{ route('reproduction-cycles.index') }}" class="btn">Breeding Records</a>
@endsection

@section('content')
    <div class="panel-card" style="margin-bottom: 20px;">
        <div class="section-title">
            <div>
                <h3>Sow Information</h3>
                <p>This reproduction cycle will be attached to the sow below.</p>
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
                <input type="text" value="{{ $pig->pen?->name ?? ($pig->pen_location ?? '—') }}" readonly>
            </div>
        </div>
    </div>

    <div class="panel-card">
        <div class="section-title">
            <div>
                <h3>Cycle Details</h3>
                <p>Track natural mating or artificial insemination, pregnancy, farrowing, and breeding cost.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('reproduction-cycles.store', $pig) }}">
            @csrf

            <div class="form-grid">
                <div class="form-group">
                    <label for="breeding_type">Breeding Type</label>
                    <select id="breeding_type" name="breeding_type" required>
                        <option value="">Select breeding type</option>
                        @foreach($breedingTypeOptions as $value => $label)
                            <option value="{{ $value }}" {{ old('breeding_type') === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="status">Cycle Status</label>
                    <select id="status" name="status" required>
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" {{ old('status', 'open') === $value ? 'selected' : '' }}>
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
                        value="{{ old('service_date', now()->toDateString()) }}"
                        max="{{ now()->toDateString() }}"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="pregnancy_check_date">Pregnancy Check Date</label>
                    <input
                        id="pregnancy_check_date"
                        name="pregnancy_check_date"
                        type="date"
                        value="{{ old('pregnancy_check_date') }}"
                    >
                </div>

                <div class="form-group">
                    <label for="expected_farrow_date">Expected Farrowing Date</label>
                    <input
                        id="expected_farrow_date"
                        name="expected_farrow_date"
                        type="date"
                        value="{{ old('expected_farrow_date') }}"
                    >
                </div>

                <div class="form-group">
                    <label for="actual_farrow_date">Actual Farrowing Date</label>
                    <input
                        id="actual_farrow_date"
                        name="actual_farrow_date"
                        type="date"
                        value="{{ old('actual_farrow_date') }}"
                    >
                </div>

                <div class="form-group" id="boar_group">
                    <label for="boar_id">Boar</label>
                    <select id="boar_id" name="boar_id">
                        <option value="">Select boar</option>
                        @foreach($boars as $boar)
                            <option value="{{ $boar->id }}" {{ (string) old('boar_id') === (string) $boar->id ? 'selected' : '' }}>
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
                            <option value="{{ $value }}" {{ old('semen_source_type') === $value ? 'selected' : '' }}>
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
                        value="{{ old('semen_source_name') }}"
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
                        value="{{ old('semen_cost', '0.00') }}"
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
                        value="{{ old('breeding_cost', old('semen_cost', '0.00')) }}"
                    >
                </div>

                <div class="form-group">
                    <label for="total_born">Total Born</label>
                    <input
                        id="total_born"
                        name="total_born"
                        type="number"
                        min="0"
                        step="1"
                        value="{{ old('total_born') }}"
                    >
                </div>

                <div class="form-group">
                    <label for="born_alive">Born Alive</label>
                    <input
                        id="born_alive"
                        name="born_alive"
                        type="number"
                        min="0"
                        step="1"
                        value="{{ old('born_alive') }}"
                    >
                </div>

                <div class="form-group">
                    <label for="stillborn">Stillborn</label>
                    <input
                        id="stillborn"
                        name="stillborn"
                        type="number"
                        min="0"
                        step="1"
                        value="{{ old('stillborn') }}"
                    >
                </div>

                <div class="form-group">
                    <label for="mummified">Mummified</label>
                    <input
                        id="mummified"
                        name="mummified"
                        type="number"
                        min="0"
                        step="1"
                        value="{{ old('mummified') }}"
                    >
                </div>

                <div class="form-group full">
                    <label for="notes">Notes</label>
                    <textarea
                        id="notes"
                        name="notes"
                        placeholder="Optional breeding notes, observations, or cycle remarks"
                    >{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn primary">Save Breeding Record</button>
                <a href="{{ route('pigs.show', $pig) }}" class="btn">Cancel</a>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
function updateBreedingFormState() {
    const breedingType = document.getElementById('breeding_type')?.value || '';
    const semenSourceType = document.getElementById('semen_source_type')?.value || '';

    const boarGroup = document.getElementById('boar_group');
    const semenSourceTypeGroup = document.getElementById('semen_source_type_group');
    const semenSourceNameGroup = document.getElementById('semen_source_name_group');
    const semenCostGroup = document.getElementById('semen_cost_group');

    if (boarGroup) {
        boarGroup.style.display = '';
    }

    if (semenSourceTypeGroup) {
        semenSourceTypeGroup.style.display = breedingType === 'artificial_insemination' ? '' : 'none';
    }

    if (semenSourceNameGroup) {
        semenSourceNameGroup.style.display = (breedingType === 'artificial_insemination' && semenSourceType === 'purchased') ? '' : 'none';
    }

    if (semenCostGroup) {
        semenCostGroup.style.display = (breedingType === 'artificial_insemination' && semenSourceType === 'purchased') ? '' : 'none';
    }
}

function autofillExpectedFarrowDate() {
    const serviceDateInput = document.getElementById('service_date');
    const expectedInput = document.getElementById('expected_farrow_date');

    if (!serviceDateInput || !expectedInput) {
        return;
    }

    if (expectedInput.value) {
        return;
    }

    if (!serviceDateInput.value) {
        return;
    }

    const baseDate = new Date(serviceDateInput.value + 'T00:00:00');
    if (Number.isNaN(baseDate.getTime())) {
        return;
    }

    baseDate.setDate(baseDate.getDate() + 114);

    const yyyy = baseDate.getFullYear();
    const mm = String(baseDate.getMonth() + 1).padStart(2, '0');
    const dd = String(baseDate.getDate()).padStart(2, '0');

    expectedInput.value = `${yyyy}-${mm}-${dd}`;
}

document.getElementById('breeding_type')?.addEventListener('change', updateBreedingFormState);
document.getElementById('semen_source_type')?.addEventListener('change', updateBreedingFormState);
document.getElementById('service_date')?.addEventListener('change', autofillExpectedFarrowDate);

updateBreedingFormState();
autofillExpectedFarrowDate();
@endsection
