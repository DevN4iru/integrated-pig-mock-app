@extends('layouts.app')

@section('title', 'Add Pig')
@section('page_title', 'Add Pig')
@section('page_subtitle', 'Add a new pig record.')

@section('top_actions')
    <a href="{{ route('pigs.index') }}" class="btn">Back to Pig List</a>
@endsection

@section('content')
    <div class="panel-card">
        <div class="section-title">
            <div>
                <h3>Add Pig Record</h3>
                <p>Fill in the details below to add a pig into the system.</p>
                <p>Current global price per kg: <strong>₱ {{ number_format((float) $pricePerKg, 2) }}</strong></p>
            </div>
        </div>

        <form method="POST" action="{{ route('pigs.store') }}">
            @csrf

            <div class="form-grid">
                <div class="form-group">
                    <label for="ear_tag">Ear Tag</label>
                    <input id="ear_tag" name="ear_tag" type="text" value="{{ old('ear_tag') }}" required>
                </div>

                <div class="form-group">
                    <label for="breed">Breed</label>
                    <input id="breed" name="breed" type="text" value="{{ old('breed') }}" required>
                </div>

                <div class="form-group">
                    <label for="sex">Sex</label>
                    <select id="sex" name="sex" required>
                        <option value="">Select sex</option>
                        <option value="male" {{ old('sex') === 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('sex') === 'female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="pen_id">Assigned Pen</label>
                    <select id="pen_id" name="pen_id" required>
                        <option value="">Select pen</option>
                        @foreach ($pens as $pen)
                            @php
                                $remaining = max((int) $pen->capacity - (int) $pen->pigs_count, 0);
                                $isFull = $remaining <= 0;
                            @endphp
                            <option value="{{ $pen->id }}" {{ old('pen_id') == $pen->id ? 'selected' : '' }} {{ $isFull ? 'disabled' : '' }}>
                                {{ $pen->name }} — {{ $pen->type }} ({{ $pen->pigs_count }}/{{ $pen->capacity }}){{ $isFull ? ' - FULL' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="pig_source">Pig Source</label>
                    <select id="pig_source" name="pig_source" required>
                        <option value="">Select source</option>
                        <option value="birthed" {{ old('pig_source') === 'birthed' ? 'selected' : '' }}>Birthed</option>
                        <option value="purchased" {{ old('pig_source') === 'purchased' ? 'selected' : '' }}>Purchased</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="age_value">Age Input</label>
                    <div style="display:grid; grid-template-columns: 1fr 180px; gap:10px;">
                        <input id="age_value" name="age_value" type="number" min="0" step="0.1" value="{{ old('age_value') }}" required>
                        <select id="age_unit" name="age_unit" required>
                            <option value="days" {{ old('age_unit', 'days') === 'days' ? 'selected' : '' }}>Days</option>
                            <option value="weeks" {{ old('age_unit') === 'weeks' ? 'selected' : '' }}>Weeks</option>
                            <option value="months" {{ old('age_unit') === 'months' ? 'selected' : '' }}>Months</option>
                        </select>
                    </div>
                    <small class="metric-note">System stores age in days for protocol alerts and scheduling.</small>
                </div>

                <div class="form-group">
                    <label for="age_days_preview">Stored Age (Days)</label>
                    <input id="age_days_preview" type="text" readonly value="0 days">
                    <input id="age" name="age" type="hidden" value="0">
                </div>

                <div class="form-group">
                    <label for="date_added">Date Added</label>
                    <input id="date_added" name="date_added" type="date" value="{{ old('date_added') }}" required>
                </div>

                <div class="form-group">
                    <label for="latest_weight">Weight Upon Entry (kg)</label>
                    <input id="latest_weight" name="latest_weight" type="number" step="0.01" min="0" value="{{ old('latest_weight') }}" required>
                </div>

                <div class="form-group">
                    <label for="asset_value_preview">Asset Value (Auto)</label>
                    <input id="asset_value_preview" type="number" step="0.01" value="{{ old('asset_value') }}" readonly>
                    <input id="asset_value" name="asset_value" type="hidden" value="{{ old('asset_value', 0) }}">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn primary">Add Pig</button>
                <a href="{{ route('pigs.index') }}" class="btn">Cancel</a>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
const PRICE_PER_KG = {{ json_encode((float) $pricePerKg) }};

function updateAssetValue() {
    const weightInput = document.getElementById('latest_weight');
    const hiddenAssetInput = document.getElementById('asset_value');
    const previewInput = document.getElementById('asset_value_preview');

    if (!weightInput || !hiddenAssetInput || !previewInput) return;

    const weight = parseFloat(weightInput.value || '0');
    const asset = isNaN(weight) ? 0 : (weight * PRICE_PER_KG);

    hiddenAssetInput.value = asset.toFixed(2);
    previewInput.value = asset.toFixed(2);
}

function convertAgeToDays(value, unit) {
    const numeric = parseFloat(value || '0');
    if (isNaN(numeric) || numeric < 0) return 0;

    if (unit === 'weeks') return Math.round(numeric * 7);
    if (unit === 'months') return Math.round(numeric * 30);
    return Math.round(numeric);
}

function updateAgePreview() {
    const valueInput = document.getElementById('age_value');
    const unitInput = document.getElementById('age_unit');
    const previewInput = document.getElementById('age_days_preview');
    const hiddenAgeInput = document.getElementById('age');

    if (!valueInput || !unitInput || !previewInput || !hiddenAgeInput) return;

    const days = convertAgeToDays(valueInput.value, unitInput.value);

    hiddenAgeInput.value = days;
    previewInput.value = `${days} day${days === 1 ? '' : 's'}`;
}

document.getElementById('latest_weight')?.addEventListener('input', updateAssetValue);
document.getElementById('age_value')?.addEventListener('input', updateAgePreview);
document.getElementById('age_unit')?.addEventListener('change', updateAgePreview);

updateAssetValue();
updateAgePreview();
@endsection