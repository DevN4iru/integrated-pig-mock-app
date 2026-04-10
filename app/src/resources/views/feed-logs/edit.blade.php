@extends('layouts.app')

@section('title', 'Edit Feed Log')
@section('page_title', 'Edit Feed Log')
@section('page_subtitle', 'Update feeding period for this pig.')

@section('top_actions')
    <a href="{{ route('pigs.show', $pig) }}" class="btn">Back</a>
@endsection

@section('content')
<div class="panel-card">
    <h3>Edit Feed Log</h3>

    <form method="POST" action="{{ route('feed-logs.update', [$pig, $feedLog]) }}">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <div class="form-group">
                <label>Feed Type</label>
                <input type="text" name="feed_type" value="{{ old('feed_type', $feedLog->feed_type) }}" required>
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
                <label>Cost (₱)</label>
                <input type="number" step="0.01" min="0" name="cost" value="{{ old('cost', $feedLog->cost ?? 0) }}" required>
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

            <div class="form-group">
                <label>Feeding Time</label>
                <select name="feeding_time" required>
                    <option value="">Select feeding time</option>
                    <option value="Morning" {{ old('feeding_time', $feedLog->feeding_time) === 'Morning' ? 'selected' : '' }}>Morning</option>
                    <option value="Afternoon" {{ old('feeding_time', $feedLog->feeding_time) === 'Afternoon' ? 'selected' : '' }}>Afternoon</option>
                    <option value="Evening" {{ old('feeding_time', $feedLog->feeding_time) === 'Evening' ? 'selected' : '' }}>Evening</option>
                </select>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status" required>
                    <option value="">Select status</option>
                    <option value="ongoing" {{ old('status', $feedLog->status) === 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                    <option value="completed" {{ old('status', $feedLog->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>

            <div class="form-group full">
                <label>Notes</label>
                <textarea name="notes">{{ old('notes', $feedLog->notes) }}</textarea>
            </div>
        </div>

        <div class="form-actions">
            <button class="btn primary">Save Changes</button>
            <a href="{{ route('pigs.show', $pig) }}" class="btn">Cancel</a>
        </div>
    </form>
</div>
@endsection
