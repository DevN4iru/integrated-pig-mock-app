@extends('layouts.app')

@section('title', 'New Breeding Record')
@section('page_title', 'New Breeding Record')
@section('page_subtitle', 'Create an ongoing reproduction cycle for this sow.')

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
                <input type="text" value="{{ $pig->pen?->name ?? '—' }}" readonly>
            </div>
        </div>
    </div>

    <div class="panel-card">
        <div class="section-title">
            <div>
                <h3>Cycle Details</h3>
                <p>Start only with the service details. Pregnancy check, return to heat, due soon, and farrowing results will be updated later in this same record.</p>
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
                    <label for="semen_source_name">AI Semen Source / Supplier</label>
                    <input
                        id="semen_source_name"
                        name="semen_source_name"
                        type="text"
                        value="{{ old('semen_source_name') }}"
                        placeholder="Where the semen came from"
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
                        value="{{ old('breeding_cost', '0.00') }}"
                    >
                    <small class="metric-note">Use the full breeding case cost here, such as semen, labor, transport, vet, or technician fees.</small>
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

            <div class="flash" style="margin-top: 16px;">
                This new record will begin as a <strong>serviced</strong> cycle. Update the same record later for pregnancy check and farrowing outcome.
            </div>

            <div class="form-actions">
                <button type="submit" class="btn primary">Save Breeding Record</button>
                <a href="{{ route('pigs.show', $pig) }}" class="btn">Cancel</a>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
function setGroupVisibility(elementId, visible) {
    const element = document.getElementById(elementId);
    if (!element) return;
    element.style.display = visible ? '' : 'none';
}

function updateBreedingFormState() {
    const breedingType = document.getElementById('breeding_type')?.value || '';
    const semenSourceType = document.getElementById('semen_source_type')?.value || '';

    setGroupVisibility('boar_group', breedingType === 'natural_mating' || breedingType === '');
    setGroupVisibility('semen_source_type_group', breedingType === 'artificial_insemination');
    setGroupVisibility('semen_source_name_group', breedingType === 'artificial_insemination');
    setGroupVisibility('semen_cost_group', breedingType === 'artificial_insemination' && semenSourceType === 'purchased');
}

document.getElementById('breeding_type')?.addEventListener('change', updateBreedingFormState);
document.getElementById('semen_source_type')?.addEventListener('change', updateBreedingFormState);

updateBreedingFormState();
@endsection
