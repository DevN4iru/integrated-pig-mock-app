@extends('layouts.app')

@section('title', 'Assign Feed')
@section('page_title', 'Assign Feed')
@section('page_subtitle', 'Record the feed assigned to this pig for a period.')

@section('top_actions')
    <a href="{{ route('pigs.show', $pig) }}" class="btn">Back</a>
@endsection

@section('content')
<div class="panel-card">
    <h3>Assign Feed</h3>
    <p class="text-muted mb-3">Use this to track what feed is assigned from a start date to an optional end date. No feed cost is calculated in the client view.</p>

    <form method="POST" action="{{ route('feed-logs.store', $pig) }}">
        @csrf

        <input type="hidden" name="cost" value="0">
        <input type="hidden" name="feeding_time" value="Assigned period">
        <input type="hidden" name="status" value="ongoing">

        <div class="form-grid">
            <div class="form-group">
                <label>Feed Name / Type</label>
                <input type="text" name="feed_type" value="{{ old('feed_type') }}" placeholder="Example: Starter, Grower, Lactating Sow Feed" required autofocus>
            </div>

            <div class="form-group">
                <label>Start Date</label>
                <input type="date" name="start_feed_date" value="{{ old('start_feed_date') }}" required>
            </div>

            <div class="form-group">
                <label>End Date</label>
                <input type="date" name="end_feed_date" value="{{ old('end_feed_date') }}">
            </div>

            <div class="form-group">
                <label>Quantity</label>
                <input type="number" step="0.01" min="0" name="quantity" value="{{ old('quantity') }}" required>
            </div>

            <div class="form-group">
                <label>Unit</label>
                <select name="unit" required>
                    <option value="">Select unit</option>
                    <option value="kg" {{ old('unit') === 'kg' ? 'selected' : '' }}>kg</option>
                    <option value="grams" {{ old('unit') === 'grams' ? 'selected' : '' }}>grams</option>
                    <option value="sacks" {{ old('unit') === 'sacks' ? 'selected' : '' }}>sacks</option>
                    <option value="bags" {{ old('unit') === 'bags' ? 'selected' : '' }}>bags</option>
                </select>
            </div>

            <div class="form-group full">
                <label>Notes</label>
                <textarea name="notes" placeholder="Optional remarks about this feed assignment.">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn primary">Save Assigned Feed</button>
            <a href="{{ route('pigs.show', $pig) }}" class="btn">Cancel</a>
        </div>
    </form>
</div>
@endsection
