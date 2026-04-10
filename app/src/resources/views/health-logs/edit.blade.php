@extends('layouts.app')

@section('title', 'Edit Health Log')
@section('page_title', 'Edit Health Log')
@section('page_subtitle', 'Update health event for this pig.')

@section('top_actions')
    <a href="{{ route('pigs.show', $pig) }}" class="btn">Back</a>
@endsection

@section('content')
    @php
        $maxDate = now()->toDateString();
        $existingWeightDates = $pig->healthLogs()
            ->where('purpose', 'weight_update')
            ->whereNotNull('weight')
            ->where('id', '!=', $healthLog->id)
            ->pluck('log_date')
            ->map(fn ($date) => substr((string) $date, 0, 10))
            ->unique()
            ->values()
            ->all();
    @endphp

<div class="panel-card">
    <h3>Edit Health Log</h3>

    <form method="POST" action="{{ route('health-logs.update', [$pig, $healthLog]) }}">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <div class="form-group">
                <label>Purpose</label>
                <select name="purpose" id="purpose" required>
                    <option value="">Select purpose</option>
                    <option value="weight_update" {{ old('purpose', $healthLog->purpose) === 'weight_update' ? 'selected' : '' }}>Weight Update</option>
                    <option value="sick" {{ old('purpose', $healthLog->purpose) === 'sick' ? 'selected' : '' }}>Sick</option>
                    <option value="recovered" {{ old('purpose', $healthLog->purpose) === 'recovered' ? 'selected' : '' }}>Recovered</option>
                    <option value="checkup" {{ old('purpose', $healthLog->purpose) === 'checkup' ? 'selected' : '' }}>Checkup</option>
                    <option value="injury" {{ old('purpose', $healthLog->purpose) === 'injury' ? 'selected' : '' }}>Injury</option>
                    <option value="observation" {{ old('purpose', $healthLog->purpose) === 'observation' ? 'selected' : '' }}>Observation</option>
                </select>
            </div>

            <div class="form-group">
                <label>Date</label>
                <input type="date" name="log_date" id="log_date" value="{{ old('log_date', $healthLog->log_date) }}" max="{{ $maxDate }}" required>
            </div>

            <div class="form-group">
                <label>Condition / Summary</label>
                <input type="text" name="condition" value="{{ old('condition', $healthLog->condition) }}" required>
            </div>

            <div class="form-group" id="weight-group">
                <label>Weight (kg)</label>
                <input type="number" step="0.01" min="0.01" name="weight" id="weight" value="{{ old('weight', $healthLog->weight) }}">
            </div>

            <div class="form-group full" id="same-day-weight-warning" style="display:none;">
                <div class="flash error" style="margin: 0;">
                    Another weight update already exists for this date. Multiple same-day weight logs are allowed, and the latest saved entry will be used first in trend calculations.
                </div>
            </div>

            <div class="form-group full">
                <label>Notes</label>
                <textarea name="notes">{{ old('notes', $healthLog->notes) }}</textarea>
            </div>
        </div>

        <div class="form-actions">
            <button class="btn primary">Save Changes</button>
            <a href="{{ route('pigs.show', $pig) }}" class="btn">Cancel</a>
        </div>
    </form>
</div>
@endsection

@section('scripts')
const existingWeightDates = @json($existingWeightDates);

function toggleWeightField() {
    const purpose = document.getElementById('purpose');
    const logDate = document.getElementById('log_date');
    const weightGroup = document.getElementById('weight-group');
    const weightInput = document.getElementById('weight');
    const warning = document.getElementById('same-day-weight-warning');

    if (!purpose || !logDate || !weightGroup || !weightInput || !warning) return;

    const showWeight = purpose.value === 'weight_update';
    const selectedDate = logDate.value;

    weightGroup.style.display = showWeight ? '' : 'none';
    weightInput.required = showWeight;

    const showWarning = showWeight && selectedDate !== '' && existingWeightDates.includes(selectedDate);
    warning.style.display = showWarning ? '' : 'none';

    if (!showWeight) {
        weightInput.value = '';
    }
}

document.getElementById('purpose')?.addEventListener('change', toggleWeightField);
document.getElementById('log_date')?.addEventListener('change', toggleWeightField);
toggleWeightField();
@endsection
