@extends('layouts.app')

@section('title', 'Add Manual Medication')
@section('page_title', 'Add Manual Medication')
@section('page_subtitle', 'Record unscheduled or ad hoc medication for this pig.')

@section('top_actions')
    <a href="{{ route('pigs.show', $pig) }}" class="btn">Back</a>
@endsection

@section('content')
<div class="panel-card">
    <h3>Add Manual Medication</h3>

    <div class="flash" style="margin: 14px 0 18px;">
        Use this page only for <strong>manual non-protocol care</strong>. If this medication belongs to a scheduled protocol item, complete it from <strong>Protocol Schedule</strong> on the pig profile instead.
    </div>

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
            <button class="btn primary">Save Manual Medication</button>
            <a href="{{ route('pigs.show', $pig) }}" class="btn">Cancel</a>
        </div>
    </form>
</div>
@endsection
