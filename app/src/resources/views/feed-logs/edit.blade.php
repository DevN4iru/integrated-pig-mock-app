@extends('layouts.app')

@section('title', 'Edit Assigned Feed')
@section('page_title', 'Edit Assigned Feed')
@section('page_subtitle', 'Update this feed assignment period.')

@section('top_actions')
    <a href="{{ route('pigs.show', $pig) }}" class="btn">Back</a>
@endsection

@section('content')
<div class="panel-card">
    <h3>Edit Assigned Feed</h3>
    <p class="text-muted mb-3">Update the assigned feed period. Feed cost is not calculated in the client view.</p>

    <form method="POST" action="{{ route('feed-logs.update', [$pig, $feedLog]) }}">
        @csrf
        @method('PUT')

        <input type="hidden" name="cost" value="0">
        <input type="hidden" name="feeding_time" value="{{ old('feeding_time', $feedLog->feeding_time ?: 'Assigned period') }}">
        <input type="hidden" name="status" value="{{ old('status', $feedLog->status ?: 'ongoing') }}">

        <div class="form-grid">
            <div class="form-group">
                <label>Feed Name / Type</label>
                <input type="text" name="feed_type" value="{{ old('feed_type', $feedLog->feed_type) }}" placeholder="Example: Starter, Grower, Lactating Sow Feed" required autofocus>
            </div>

            <div class="form-group">
                <label>Start Date</label>
                <input type="date" name="start_feed_date" value="{{ old('start_feed_date', $feedLog->start_feed_date) }}" required>
            </div>

            <div class="form-group">
                <label>End Date</label>
                <input type="date" name="end_feed_date" value="{{ old('end_feed_date', $feedLog->end_feed_date) }}">
            </div>

            <div class="form-group">
                <label>Quantity</label>
                <input type="number" step="0.01" min="0" name="quantity" value="{{ old('quantity', $feedLog->quantity) }}" required>
            </div>

            <div class="form-group">
                <label>Unit</label>
                <select name="unit" required>
                    <option value="">Select unit</option>
                    <option value="kg" {{ old('unit', $feedLog->unit) === 'kg' ? 'selected' : '' }}>kg</option>
                    <option value="grams" {{ old('unit', $feedLog->unit) === 'grams' ? 'selected' : '' }}>grams</option>
                    <option value="sacks" {{ old('unit', $feedLog->unit) === 'sacks' ? 'selected' : '' }}>sacks</option>
                    <option value="bags" {{ old('unit', $feedLog->unit) === 'bags' ? 'selected' : '' }}>bags</option>
                </select>
            </div>

            <div class="form-group full">
                <label>Notes</label>
                <textarea name="notes" placeholder="Optional remarks about this feed assignment.">{{ old('notes', $feedLog->notes) }}</textarea>
            </div>
        </div>

        <div class="form-actions">
            <button class="btn primary">Save Assigned Feed</button>
            <a href="{{ route('pigs.show', $pig) }}" class="btn">Cancel</a>
        </div>
    </form>
</div>
@endsection
