@extends('layouts.app')

@section('title', 'Add Pig')
@section('page_title', 'Add Pig')
@section('page_subtitle', 'Add a new pig record.')

@section('top_actions')
    <a href="{{ route('pigs.index') }}" class="btn">Back to Pig List</a>
@endsection


@section('styles')
.pig-create-layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(300px, 0.34fr);
    gap: 20px;
    align-items: start;
}

.pig-create-layout > .panel-card {
    border-color: #dbe4f0;
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.055);
    position: relative;
    overflow: hidden;
}

.pig-create-layout > .panel-card::before {
    content: "";
    position: absolute;
    inset: 0 0 auto 0;
    height: 3px;
    background: linear-gradient(90deg, var(--accent), rgba(37, 99, 235, 0.18), transparent);
}

.pig-create-head {
    display: flex;
    justify-content: space-between;
    gap: 18px;
    align-items: flex-start;
    padding-bottom: 14px;
    border-bottom: 1px solid #e2e8f0;
    margin-bottom: 18px;
}

.pig-create-head h3 {
    font-size: 18px;
    letter-spacing: -0.02em;
    margin-bottom: 4px;
}

.pig-create-head p {
    color: var(--muted);
    font-size: 13px;
    line-height: 1.45;
}

.pig-create-pill {
    flex: 0 0 auto;
    border-radius: 999px;
    padding: 7px 10px;
    background: #f8fbff;
    border: 1px solid #dbe4f0;
    color: var(--muted);
    font-size: 12px;
    font-weight: 800;
}

.pig-create-layout input,
.pig-create-layout select {
    border-color: #dbe4f0;
    min-height: 44px;
}

.pig-create-layout input[readonly] {
    background: linear-gradient(180deg, #f8fbff 0%, #f6f9fd 100%);
}

.pig-create-age-grid {
    display: grid;
    grid-template-columns: 1fr 160px;
    gap: 10px;
}

.pig-create-guide {
    display: grid;
    gap: 14px;
}

.pig-guide-title {
    padding-bottom: 14px;
    border-bottom: 1px solid #e2e8f0;
}

.pig-guide-title h3 {
    font-size: 18px;
    letter-spacing: -0.02em;
    margin-bottom: 4px;
}

.pig-guide-title p {
    color: var(--muted);
    font-size: 13px;
    line-height: 1.45;
}

.pig-guide-list {
    display: grid;
    gap: 10px;
}

.pig-guide-item {
    border: 1px solid #dbe4f0;
    border-radius: 14px;
    background: linear-gradient(180deg, #f8fbff 0%, #f6f9fd 100%);
    padding: 12px;
}

.pig-guide-item strong {
    display: block;
    margin-bottom: 4px;
    color: var(--text);
}

.pig-guide-item span {
    color: var(--muted);
    font-size: 13px;
    line-height: 1.4;
}

.pig-create-actions {
    border-top: 1px solid #e2e8f0;
    margin-top: 18px;
    padding-top: 18px;
}

@media (max-width: 1100px) {
    .pig-create-layout {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 640px) {
    .pig-create-age-grid {
        grid-template-columns: 1fr;
    }

    .pig-create-head {
        display: grid;
        grid-template-columns: 1fr;
    }

    .pig-create-pill {
        width: fit-content;
    }

    .pig-create-actions .btn,
    .pig-create-layout .btn {
        width: 100%;
    }
}
@endsection

@section('content')
    <div class="pig-create-layout">
        <div class="panel-card">
            <div class="pig-create-head">
                <div>
                    <h3>Add Pig Record</h3>
                    <p>Fill in the details below to add a pig into the system.</p>
                </div>

                <span class="pig-create-pill">New Record</span>
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
                    <div class="pig-create-age-grid">
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
                    <label for="asset_value">Farm Value</label>
                    <input id="asset_value" name="asset_value" type="number" step="0.01" min="0" value="{{ old('asset_value', 0) }}" required>
                    <small class="metric-note">Enter the value manually. Weight does not auto-compute the farm value.</small>
                </div>
            </div>

            <div class="form-actions pig-create-actions">
                <button type="submit" class="btn primary">Add Pig</button>
                <a href="{{ route('pigs.index') }}" class="btn">Cancel</a>
            </div>
        </form>
        </div>

        <aside class="panel-card pig-create-guide">
            <div class="pig-guide-title">
                <h3>Add Pig Guide</h3>
                <p>Use this for manual pig records. Piglets from farrowing should still be registered from the breeding case.</p>
            </div>

            <div class="pig-guide-list">
                <div class="pig-guide-item">
                    <strong>Manual Birthed</strong>
                    <span>Simple source label only. It does not start the Medication Program.</span>
                </div>

                <div class="pig-guide-item">
                    <strong>Purchased</strong>
                    <span>Use for pigs bought from outside the farm.</span>
                </div>

                <div class="pig-guide-item">
                    <strong>Date Added</strong>
                    <span>Must be today or a past date. Future dates are blocked for clean farm records.</span>
                </div>

                <div class="pig-guide-item">
                    <strong>Asset Value</strong>
                    <span>Enter this manually based on the farm's actual valuation.</span>
                </div>

                <div class="pig-guide-item">
                    <strong>Medication Program</strong>
                    <span>Only actual farrowing-linked piglets and lactating sows receive protocol schedules.</span>
                </div>
            </div>
        </aside>
    </div>
@endsection

@section('scripts')
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

document.getElementById('age_value')?.addEventListener('input', updateAgePreview);
document.getElementById('age_unit')?.addEventListener('change', updateAgePreview);

updateAgePreview();
@endsection
