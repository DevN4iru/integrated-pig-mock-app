@extends('layouts.app')

@section('title', 'Edit Health Log')
@section('page_title', 'Edit Health Log')
@section('page_subtitle', 'Update health condition for this pig.')

@section('top_actions')
    <a href="{{ route('pigs.show', $pig) }}" class="btn">Back</a>
@endsection

@section('content')
<div class="panel-card">
    <h3>Edit Health Log</h3>

    <form method="POST" action="{{ route('health-logs.update', [$pig, $healthLog]) }}">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <div class="form-group">
                <label>Condition</label>
                <input type="text" name="condition" value="{{ old('condition', $healthLog->condition) }}" required>
            </div>

            <div class="form-group">
                <label>Date</label>
                <input type="date" name="log_date" value="{{ old('log_date', $healthLog->log_date) }}" required>
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
