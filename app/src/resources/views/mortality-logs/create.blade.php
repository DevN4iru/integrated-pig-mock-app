@extends('layouts.app')

@section('title', 'Record Mortality')
@section('page_title', 'Record Mortality')
@section('page_subtitle', 'Record mortality for this pig.')

@section('top_actions')
    <a href="{{ route('pigs.show', $pig) }}" class="btn">Back</a>
@endsection

@section('content')
<div class="panel-card">
    <h3>Record Mortality</h3>

    <form method="POST" action="{{ route('mortality.store', $pig) }}">
        @csrf

        <div class="form-grid">
            <div class="form-group">
                <label>Date of Death</label>
                <input type="date" name="death_date" value="{{ old('death_date') }}" required>
            </div>

            <div class="form-group">
                <label>Cause</label>
                <input type="text" name="cause" value="{{ old('cause') }}" required>
            </div>

            <div class="form-group full">
                <label>Notes</label>
                <textarea name="notes">{{ old('notes') }}</textarea>
            </div>
        </div>

        <button class="btn primary">Save Mortality Record</button>
    </form>
</div>
@endsection