@extends('layouts.app')

@section('title', 'Add Medication')
@section('page_title', 'Add Medication')
@section('page_subtitle', 'Record medication for this pig.')

@section('top_actions')
    <a href="{{ route('pigs.show', $pig) }}" class="btn">Back</a>
@endsection

@section('content')
<div class="panel-card">
    <h3>Add Medication</h3>

    <form method="POST" action="{{ route('medications.store', $pig) }}">
        @csrf

        <div class="form-grid">
            <div class="form-group">
                <label>Medication Name</label>
                <input type="text" name="medication_name" value="{{ old('medication_name') }}" required>
            </div>

            <div class="form-group">
                <label>Dosage</label>
                <input type="text" name="dosage" value="{{ old('dosage') }}" required>
            </div>

            <div class="form-group">
                <label>Cost (₱)</label>
                <input type="number" step="0.01" min="0" name="cost" value="{{ old('cost', 0) }}" required>
            </div>

            <div class="form-group">
                <label>Date Administered</label>
                <input type="date" name="administered_at" value="{{ old('administered_at') }}" required>
            </div>

            <div class="form-group full">
                <label>Notes</label>
                <textarea name="notes">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="form-actions">
            <button class="btn primary">Save Medication</button>
            <a href="{{ route('pigs.show', $pig) }}" class="btn">Cancel</a>
        </div>
    </form>
</div>
@endsection
