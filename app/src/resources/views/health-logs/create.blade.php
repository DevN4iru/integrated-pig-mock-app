@extends('layouts.app')

@section('title', 'Add Health Log')
@section('page_title', 'Add Health Log')
@section('page_subtitle', 'Record health condition for this pig.')

@section('top_actions')
    <a href="{{ route('pigs.show', $pig) }}" class="btn">Back</a>
@endsection

@section('content')
    <div class="panel-card">
        <h3>Health Log Entry</h3>

        <form method="POST" action="{{ route('health-logs.store', $pig) }}">
            @csrf

            <div class="form-grid">
                <div class="form-group">
                    <label>Condition</label>
                    <input type="text" name="condition" required>
                </div>

                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="log_date" required>
                </div>

                <div class="form-group full">
                    <label>Notes</label>
                    <textarea name="notes"></textarea>
                </div>
            </div>

            <button class="btn primary">Save Log</button>
        </form>
    </div>
@endsection