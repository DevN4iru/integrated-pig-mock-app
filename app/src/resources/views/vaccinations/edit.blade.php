@extends('layouts.app')

@section('title', 'Edit Vaccination')
@section('page_title', 'Edit Vaccination')
@section('page_subtitle', 'Update vaccination record for this pig.')

@section('top_actions')
    <a href="{{ route('pigs.show', $pig) }}" class="btn">Back</a>
@endsection

@section('content')
<div class="panel-card">
    <h3>Edit Vaccination</h3>

    <form method="POST" action="{{ route('vaccinations.update', [$pig, $vaccination]) }}">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <div class="form-group">
                <label>Vaccine Name</label>
                <input type="text" name="vaccine_name" value="{{ old('vaccine_name', $vaccination->vaccine_name) }}" required>
            </div>

            <div class="form-group">
                <label>Dose</label>
                <input type="text" name="dose" value="{{ old('dose', $vaccination->dose) }}" required>
            </div>

            <div class="form-group">
                <label>Date Vaccinated</label>
                <input type="date" name="vaccinated_at" value="{{ old('vaccinated_at', $vaccination->vaccinated_at) }}" required>
            </div>

            <div class="form-group full">
                <label>Notes</label>
                <textarea name="notes">{{ old('notes', $vaccination->notes) }}</textarea>
            </div>
        </div>

        <div class="form-actions">
            <button class="btn primary">Save Changes</button>
            <a href="{{ route('pigs.show', $pig) }}" class="btn">Cancel</a>
        </div>
    </form>
</div>
@endsection
