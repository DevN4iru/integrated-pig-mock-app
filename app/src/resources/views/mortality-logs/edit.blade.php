@extends('layouts.app')

@section('title', 'Edit Mortality')
@section('page_title', 'Edit Mortality')
@section('page_subtitle', 'Update mortality record for this pig.')

@section('top_actions')
    <a href="{{ route('pigs.show', $pig) }}" class="btn">Back</a>
@endsection

@section('content')
<div class="panel-card">
    <h3>Edit Mortality Record</h3>

    <form method="POST" action="{{ route('mortality.update', [$pig, $mortalityLog]) }}">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <div class="form-group">
                <label>Date of Death</label>
                <input type="date" name="death_date" value="{{ old('death_date', $mortalityLog->death_date) }}" required>
            </div>

            <div class="form-group">
                <label>Cause</label>
                <input type="text" name="cause" value="{{ old('cause', $mortalityLog->cause) }}" required>
            </div>

            <div class="form-group full">
                <label>Notes</label>
                <textarea name="notes">{{ old('notes', $mortalityLog->notes) }}</textarea>
            </div>
        </div>

        <div class="form-actions">
            <button class="btn primary">Save Changes</button>
            <a href="{{ route('pigs.show', $pig) }}" class="btn">Cancel</a>
        </div>
    </form>
</div>
@endsection
