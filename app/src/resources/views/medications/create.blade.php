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
                <input type="text" name="medication_name" required>
            </div>

            <div class="form-group">
                <label>Dosage</label>
                <input type="text" name="dosage" required>
            </div>

            <div class="form-group">
                <label>Date Administered</label>
                <input type="date" name="administered_at" required>
            </div>

            <div class="form-group" style="grid-column: span 2;">
                <label>Notes</label>
                <textarea name="notes"></textarea>
            </div>
        </div>

        <button class="btn primary">Save Medication</button>
    </form>
</div>
@endsection