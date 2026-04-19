@extends('layouts.app')

@section('title', 'Edit Pig')
@section('page_title', 'Edit Pig')
@section('page_subtitle', 'Update an existing pig record.')

@section('top_actions')
    <a href="{{ route('pigs.show', $pig) }}" class="btn">Back to Pig Profile</a>
@endsection

@section('content')
    <div class="panel-card" style="margin-bottom: 20px;">
        <div class="flash error" style="margin-bottom: 0;">
            Pen reassignment is no longer handled from the edit form. Use the dedicated transfer action from the pig profile so movement history stays complete.
        </div>
    </div>

    <div class="panel-card">
        <div class="section-title">
            <div>
                <h3>Edit Pig Record</h3>
                <p>Update the master pig record only. Pen changes must go through transfer history.</p>
                <p>Current global price per kg: <strong>₱ {{ number_format((float) $pricePerKg, 2) }}</strong></p>
            </div>
        </div>

        @php
            $dateValue = old('date_added', $pig->date_added ? substr((string) $pig->date_added, 0, 10) : '');
            $defaultAgeValue = old('age_value', (int) ($pig->age ?? 0));
            $defaultAgeUnit = old('age_unit', 'days');
        @endphp

        <form method="POST" action="{{ route('pigs.update', $pig) }}">
            @csrf
            @method('PUT')

            <div class="form-grid">
                <div class="form-group">
                    <label for="ear_tag">Ear Tag</label>
                    <input id="ear_tag" name="ear_tag" type="text" value="{{ old('ear_tag', $pig->ear_tag) }}" required>
                </div>

                <div class="form-group">
                    <label for="breed">Breed</label>
                    <input id="breed" name="breed" type="text" value="{{ old('breed', $pig->breed) }}" required>
                </div>

                <div class="form-group">
                    <label for="sex">Sex</label>
                    <select id="sex" name="sex" required>
                        <option value="">Select sex</option>
                        <option value="male" {{ old('sex', $pig->sex) === 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('sex', $pig->sex) === 'female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Current Assigned Pen</label>
                    <input type="text" value="{{ optional($pig->pen)->name ?: ($pig->pen_location ?? '—') }}" readonly>
                </div>

                <div class="form-group">
                    <label for="pig_source">Pig Source</label>
                    <select id="pig_source" name="pig_source" required>
                        <option value="">Select source</option>
                        <option value="birthed" {{ old('pig_source', $pig->pig_source) === 'birthed' ? 'selected' : '' }}>Birthed</option>
                        <option value="purchased" {{ old('pig_source', $pig->pig_source) === 'purchased' ? 'selected' : '' }}>Purchased</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="age_value">Age Input</label>
                    <div style="display:grid; grid-template-columns: 1fr 180px; gap:10px;">
                        <input id="age_value" name="age_value" type="number" min="0" step="0.1" value="{{ $defaultAgeValue }}" required>
                        <select id="age_unit" name="age_unit" required>
                            <option value="days" {{ $defaultAgeUnit === 'days' ? 'selected' : '' }}>Days</option>
                            <option value="weeks" {{ $defaultAgeUnit === 'weeks' ? 'selected' : '' }}>Weeks</option>
                            <option value="months" {{ $defaultAgeUnit === 'months' ? 'selected' : '' }}>Months</option>
                        </select>
                    </div>
                    <small class="metric-note">System stores age in days for protocol alerts and scheduling.</small>
                </div>

                <div class="form-group">
                    <label for="age_days_preview">Stored Age (Days)</label>
                    <input id="age_days_preview" type="text" readonly value="{{ (int) ($pig->age ?? 0) }} days">
                    <input id="age" name="age" type="hidden" value="{{ (int) ($pig->age ?? 0) }}">
                </div>

                <div class="form-group">
                    <label for="date_added">Date Added</label>
                    <input id="date_added" name="date_added" type="date" value="{{ $dateValue }}" required>
                </div>

                <div class="form-group">
                    <label for="latest_weight">Weight Upon Entry</label>
                    <input id="latest_weight" name="latest_weight" type="number" step="0.01" min="0" value="{{ old('latest_weight', $pig->latest_weight) }}" required>
                </div>

                <div class="form-group">
                    <label for="asset_value_preview">Asset Value (Auto)</label>
                    <input id="asset_value_preview" type="number" step="0.01" value="{{ old('asset_value', $pig->asset_value) }}" readonly>
                    <input id="asset_value" name="asset_value" type="hidden" value="{{ old('asset_value', $pig->asset_value) }}">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn primary">Save Changes</button>
                <a href="{{ route('pigs.show', $pig) }}" class="btn">Cancel</a>
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