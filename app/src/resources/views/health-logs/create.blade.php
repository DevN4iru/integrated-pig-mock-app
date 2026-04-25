@extends('layouts.app')

@section('title', 'Update Weight')
@section('page_title', 'Update Weight')
@section('page_subtitle', 'Record the latest weight for this pig.')

@section('top_actions')
    <a href="{{ route('pigs.show', $pig) }}" class="btn">Back</a>
@endsection

@section('content')
    @php
        $maxDate = now()->toDateString();
        $existingWeightDates = $pig->healthLogs()
            ->where('purpose', 'weight_update')
            ->whereNotNull('weight')
            ->pluck('log_date')
            ->map(fn ($date) => substr((string) $date, 0, 10))
            ->unique()
            ->values()
            ->all();
    @endphp

    <div class="panel-card">
        <h3>Weight Update</h3>
        <p class="text-muted mb-3">Add a dated weight record. This becomes part of the pig's weight history.</p>

        <form method="POST" action="{{ route('health-logs.store', $pig) }}">
            @csrf

            <input type="hidden" name="purpose" value="weight_update">
            <input type="hidden" name="condition" value="Weight update">

            <div class="form-grid">
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="log_date" id="log_date" value="{{ old('log_date', $maxDate) }}" max="{{ $maxDate }}" required>
                </div>

                <div class="form-group">
                    <label>Weight (kg)</label>
                    <input type="number" step="0.01" min="0.01" name="weight" id="weight" value="{{ old('weight') }}" required autofocus>
                </div>

                <div class="form-group full" id="same-day-weight-warning" style="display:none;">
                    <div class="flash error" style="margin: 0;">
                        A weight update already exists for this date. Multiple same-day weight records are allowed, and the latest saved entry will be used first.
                    </div>
                </div>

                <div class="form-group full">
                    <label>Notes</label>
                    <textarea name="notes" placeholder="Optional notes about this weight record.">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="form-actions">
                <button class="btn primary" type="submit">Save Weight</button>
                <a href="{{ route('pigs.show', $pig) }}" class="btn">Cancel</a>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
const existingWeightDates = @json($existingWeightDates);
const logDate = document.getElementById('log_date');
const warning = document.getElementById('same-day-weight-warning');

function toggleWeightDateWarning() {
    if (!logDate || !warning) return;

    const selectedDate = logDate.value;
    warning.style.display = selectedDate !== '' && existingWeightDates.includes(selectedDate) ? '' : 'none';
}

logDate?.addEventListener('change', toggleWeightDateWarning);
toggleWeightDateWarning();
@endsection
