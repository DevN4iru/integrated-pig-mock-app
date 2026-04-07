@extends('layouts.app')

@section('title', 'Add Feed Log')
@section('page_title', 'Add Feed Log')
@section('page_subtitle', 'Record feeding period for this pig.')

@section('content')
<div class="panel-card">

<form method="POST" action="{{ route('feed-logs.store', $pig) }}">
    @csrf

    <div class="form-grid">

        <div class="form-group">
            <label>Feed Type</label>
            <input type="text" name="feed_type" required>
        </div>

        <div class="form-group">
            <label>Start Date</label>
            <input type="date" name="start_feed_date" required>
        </div>

        <div class="form-group">
            <label>End Date</label>
            <input type="date" name="end_feed_date">
        </div>

        <div class="form-group">
            <label>Quantity</label>
            <input type="number" step="0.01" name="quantity" required>
        </div>

        <div class="form-group">
            <label>Unit</label>
            <input type="text" name="unit" required>
        </div>

        <div class="form-group">
            <label>Feeding Time</label>
            <select name="feeding_time" required>
                <option>Morning</option>
                <option>Afternoon</option>
                <option>Evening</option>
            </select>
        </div>

        <div class="form-group">
            <label>Status</label>
            <select name="status" required>
                <option value="ongoing">Ongoing</option>
                <option value="completed">Completed</option>
            </select>
        </div>

        <div class="form-group full">
            <label>Notes</label>
            <textarea name="notes"></textarea>
        </div>

    </div>

    <div class="form-actions">
        <button type="submit" class="btn primary">Save</button>
    </div>

</form>

</div>
@endsection