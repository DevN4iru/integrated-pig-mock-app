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
                <input type="date" name="log_date" value="{{ old('log_date', $healthLog->log_date) }}" max="{{ $maxDate }}" required>
            </div>

            <div class="form-group">
                <label>Condition / Summary</label>
                <input type="text" name="condition" value="{{ old('condition', $healthLog->condition) }}" required>
            </div>

            <div class="form-group" id="weight-group">
                <label>Weight (kg)</label>
                <input type="number" step="0.01" min="0.01" name="weight" id="weight" value="{{ old('weight', $healthLog->weight) }}">
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
