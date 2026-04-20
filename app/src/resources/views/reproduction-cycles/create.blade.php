@extends('layouts.app')

@php
    $formMode = $formMode ?? 'create';
    $submitLabel = $submitLabel ?? 'Save Breeding Record';
    $attemptNumber = $attemptNumber ?? 1;
    $defaults = array_merge([
        'breeding_type' => '',
        'service_date' => now()->toDateString(),
        'boar_id' => '',
        'semen_source_type' => '',
        'semen_source_name' => '',
        'semen_cost' => '0.00',
        'breeding_cost' => '0.00',
        'notes' => '',
    ], $defaults ?? []);
@endphp

@section('title', $formMode === 'retry' ? 'Start Next Attempt' : 'New Breeding Record')
@section('page_title', $formMode === 'retry' ? 'Start Next Attempt' : 'New Breeding Record')
@section('page_subtitle', $formMode === 'retry'
    ? 'Reuse the same parent case and start a new service attempt after return to heat.'
    : 'Create an ongoing reproduction cycle for this sow.')

@section('top_actions')
    @if($formMode === 'retry' && !empty($cycle))
        <a href="{{ route('reproduction-cycles.show', $cycle) }}" class="btn">Back to Case</a>
    @elseif(!empty($pig))
        <a href="{{ route('pigs.show', $pig) }}" class="btn">Back to Pig Profile</a>
    @endif

    <a href="{{ route('reproduction-cycles.index') }}" class="btn">Breeding Records</a>
@endsection

@section('content')
    <div class="panel-card" style="margin-bottom: 20px;">
        <div class="section-title">
            <div>
                <h3>Sow Information</h3>
                <p>
                    @if($formMode === 'retry')
                        You are starting <strong>Attempt {{ $attemptNumber }}</strong> inside the same parent breeding case.
                    @else
                        This reproduction cycle will be attached to the sow below.
                    @endif
                </p>
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label>Ear Tag</label>
                <input type="text" value="{{ $pig->ear_tag ?? '—' }}" readonly>
            </div>

            <div class="form-group">
                <label>Breed</label>
                <input type="text" value="{{ $pig->breed ?? '—' }}" readonly>
            </div>

            <div class="form-group">
                <label>Sex</label>
                <input type="text" value="{{ !empty($pig?->sex) ? ucfirst($pig->sex) : '—' }}" readonly>
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
                <h3>{{ $formMode === 'retry' ? 'Attempt Setup' : 'Cycle Details' }}</h3>
                <p>
                    @if($formMode === 'retry')
                        Previous attempt details are copied below by default, but you can change them before saving the next attempt.
                    @else
                        Start only with the service details. Pregnancy check, return to heat, farrowing, and closure will be updated later in the same case.
                    @endif
                </p>
            </div>
        </div>

        <form method="POST" action="{{ $submitRoute ?? '#' }}">
            @csrf

            <div class="form-grid">
                <div class="form-group">
                    <label for="breeding_type">Breeding Type</label>
                    <select id="breeding_type" name="breeding_type" required>
                        <option value="">Select breeding type</option>
                        @foreach(($breedingTypeOptions ?? []) as $value => $label)
                            <option value="{{ $value }}" {{ $defaults['breeding_type'] === $value ? 'selected' : '' }}>
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
                        value="{{ $defaults['service_date'] }}"
                        max="{{ now()->toDateString() }}"
                        required
                    >
                    @error('service_date')
                        <div class="error-text">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group" id="boar_group">
                    <label for="boar_id" id="boar_label">Boar</label>
                    <select id="boar_id" name="boar_id">
                        <option value="">Select boar</option>
                        @foreach(($boars ?? []) as $boar)
                            <option value="{{ $boar->id }}" {{ (string) $defaults['boar_id'] === (string) $boar->id ? 'selected' : '' }}>
                                {{ $boar->ear_tag }} — {{ $boar->breed }}
                            </option>
                        @endforeach
                    </select>
                    <small class="metric-note" id="boar_note" style="display:none;">Required for natural mating and locally sourced AI donor boar selection.</small>
                    @error('boar_id')
                        <div class="error-text">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group" id="semen_source_type_group">
                    <label for="semen_source_type">AI Semen Source Type</label>
                    <select id="semen_source_type" name="semen_source_type">
                        <option value="">Select semen source type</option>
                        @foreach(($semenSourceOptions ?? []) as $value => $label)
                            <option value="{{ $value }}" {{ $defaults['semen_source_type'] === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('semen_source_type')
                        <div class="error-text">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group" id="semen_source_name_group">
                    <label for="semen_source_name" id="semen_source_name_label">AI Semen Source / Supplier</label>
                    <input
                        id="semen_source_name"
                        name="semen_source_name"
                        type="text"
                        value="{{ $defaults['semen_source_name'] }}"
                        placeholder="Where the semen came from"
                    >
                    <small class="metric-note" id="semen_source_name_note">Required for purchased AI. Optional for locally sourced AI notes.</small>
                    @error('semen_source_name')
                        <div class="error-text">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group" id="semen_cost_group">
                    <label for="semen_cost">Purchased Semen Cost</label>
                    <input
                        id="semen_cost"
                        name="semen_cost"
                        type="number"
                        min="0"
                        step="0.01"
                        value="{{ $defaults['semen_cost'] }}"
                    >
                    <small class="metric-note">Tracked separately from service / handling cost.</small>
                    @error('semen_cost')
                        <div class="error-text">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="breeding_cost">{{ $formMode === 'retry' ? 'Attempt Service / Handling Cost to Add' : 'Initial Service / Handling Cost' }}</label>
                    <input
                        id="breeding_cost"
                        name="breeding_cost"
                        type="number"
                        min="0"
                        step="0.01"
                        value="{{ $defaults['breeding_cost'] }}"
                    >
                    <small class="metric-note">This excludes purchased semen cost. Total breeding exposure = semen cost + service / handling cost.</small>
                    @error('breeding_cost')
                        <div class="error-text">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group full">
                    <label for="notes">Attempt Notes</label>
                    <textarea
                        id="notes"
                        name="notes"
                        placeholder="Optional breeding notes, observations, or service remarks"
                    >{{ $defaults['notes'] }}</textarea>
                    @error('notes')
                        <div class="error-text">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="flash" style="margin-top: 16px;">
                The projected farrow date is still computed from <strong>service date + 114 days</strong>, but it stays hidden until the pregnancy check is recorded as <strong>pregnant</strong>. Breeding accounting follows <strong>service / handling cost + semen cost</strong>.
            </div>

            <div class="form-actions">
                <button type="submit" class="btn primary">{{ $submitLabel }}</button>

                @if($formMode === 'retry' && !empty($cycle))
                    <a href="{{ route('reproduction-cycles.show', $cycle) }}" class="btn">Cancel</a>
                @elseif(!empty($pig))
                    <a href="{{ route('pigs.show', $pig) }}" class="btn">Cancel</a>
                @else
                    <a href="{{ route('reproduction-cycles.index') }}" class="btn">Cancel</a>
                @endif
            </div>
        </form>
    </div>
@endsection

@section('scripts')
document.addEventListener('DOMContentLoaded', function () {
    function setGroupVisibility(elementId, visible) {
        const element = document.getElementById(elementId);
        if (!element) return;
        element.style.display = visible ? '' : 'none';
    }

    function updateBreedingFormState() {
        const breedingType = document.getElementById('breeding_type')?.value || '';
        const semenSourceType = document.getElementById('semen_source_type')?.value || '';
        const boarLabel = document.getElementById('boar_label');
        const boarNote = document.getElementById('boar_note');
        const sourceNameLabel = document.getElementById('semen_source_name_label');
        const sourceNameNote = document.getElementById('semen_source_name_note');

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
    }

    document.getElementById('breeding_type')?.addEventListener('change', updateBreedingFormState);
    document.getElementById('semen_source_type')?.addEventListener('change', updateBreedingFormState);

    updateBreedingFormState();
});
@endsection
