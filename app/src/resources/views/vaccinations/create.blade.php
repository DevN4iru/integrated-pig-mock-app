@extends('layouts.app')

@section('title', 'Add Manual Vaccination')
@section('page_title', 'Add Manual Vaccination')
@section('page_subtitle', 'Record unscheduled or ad hoc vaccination for this pig.')

@section('top_actions')
    <a href="{{ route('pigs.show', $pig) }}" class="btn">Back</a>
@endsection

@section('content')
<div class="panel-card">
    <h3>Add Manual Vaccination</h3>

    <div class="flash" style="margin: 14px 0 18px;">
        Use this page only for <strong>manual non-protocol care</strong>. If this vaccination belongs to a scheduled protocol item, complete it from <strong>Protocol Schedule</strong> on the pig profile instead.
    </div>

    <form method="POST" action="{{ route('vaccinations.store', $pig) }}">
        @csrf

        <div class="form-grid">
            <div class="form-group">
                <label>Vaccine Name</label>
                <input type="text" name="vaccine_name" value="{{ old('vaccine_name') }}" required>
            </div>

            <div class="form-group">
                <label>Dose</label>
                <input type="text" name="dose" value="{{ old('dose') }}" required>
            </div>

            <div class="form-group">
                <label>Cost (₱)</label>
                <input type="number" step="0.01" min="0" name="cost" value="{{ old('cost', 0) }}" required>
            </div>

            <div class="form-group">
                <label>Date Vaccinated</label>
                <input type="date" name="vaccinated_at" value="{{ old('vaccinated_at') }}" required>
            </div>

            <div class="form-group full">
                <label>Notes</label>
                <textarea name="notes">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="form-actions">
            <button class="btn primary">Save Manual Vaccination</button>
            <a href="{{ route('pigs.show', $pig) }}" class="btn">Cancel</a>
        </div>
    </form>
</div>
@endsection
