@extends('layouts.app')

@section('title', 'Add Health Log')
@section('page_title', 'Add Health Log')
@section('page_subtitle', 'Record a health event for this pig.')

@section('top_actions')
    <a href="{{ route('pigs.show', $pig) }}" class="btn">Back</a>
@endsection

@section('content')
    @php
        $maxDate = now()->toDateString();
    @endphp

    <div class="panel-card">
        <h3>Health Log Entry</h3>

        <form method="POST" action="{{ route('health-logs.store', $pig) }}">
            @csrf

            <div class="form-grid">
                <div class="form-group">
                    <label>Purpose</label>
                    <select name="purpose" id="purpose" required>
                        <option value="">Select purpose</option>
                        <option value="weight_update" {{ old('purpose') === 'weight_update' ? 'selected' : '' }}>Weight Update</option>
                        <option value="sick" {{ old('purpose') === 'sick' ? 'selected' : '' }}>Sick</option>
                        <option value="recovered" {{ old('purpose') === 'recovered' ? 'selected' : '' }}>Recovered</option>
                        <option value="checkup" {{ old('purpose') === 'checkup' ? 'selected' : '' }}>Checkup</option>
                        <option value="injury" {{ old('purpose') === 'injury' ? 'selected' : '' }}>Injury</option>
                        <option value="observation" {{ old('purpose') === 'observation' ? 'selected' : '' }}>Observation</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="log_date" value="{{ old('log_date') }}" max="{{ $maxDate }}" required>
                </div>

                <div class="form-group">
                    <label>Condition / Summary</label>
                    <input type="text" name="condition" value="{{ old('condition') }}" required>
                </div>

                <div class="form-group" id="weight-group">
                    <label>Weight (kg)</label>
                    <input type="number" step="0.01" min="0.01" name="weight" id="weight" value="{{ old('weight') }}">
                </div>

                <div class="form-group full">
                    <label>Notes</label>
                    <textarea name="notes">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="form-actions">
                <button class="btn primary" type="submit">Save Log</button>
                <a href="{{ route('pigs.show', $pig) }}" class="btn">Cancel</a>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
function toggleWeightField() {
    const purpose = document.getElementById('purpose');
    const weightGroup = document.getElementById('weight-group');
    const weightInput = document.getElementById('weight');

    if (!purpose || !weightGroup || !weightInput) return;

    const showWeight = purpose.value === 'weight_update';
    weightGroup.style.display = showWeight ? '' : 'none';
    weightInput.required = showWeight;

    if (!showWeight) {
        weightInput.value = '';
    }
}

document.getElementById('purpose')?.addEventListener('change', toggleWeightField);
toggleWeightField();
@endsection
