@extends('layouts.app')

@section('title', 'Add Pen')
@section('page_title', 'Add Pen')
@section('page_subtitle', 'Create a new housing pen record.')

@section('top_actions')
    <a href="{{ route('pens.index') }}" class="btn">Back to Pen List</a>
@endsection

@section('content')
    <div class="panel-card">
        <div class="section-title">
            <div>
                <h3>New Pen Record</h3>
                <p>Fill in the details below to add a pen into the system.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('pens.store') }}">
            @csrf

            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Pen Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Pen A1" required>
                </div>

                <div class="form-group">
                    <label for="type">Pen Type</label>
                    <select id="type" name="type" required>
                        <option value="">Select type</option>
                        <option value="Grower" {{ old('type') === 'Grower' ? 'selected' : '' }}>Grower</option>
                        <option value="Finisher" {{ old('type') === 'Finisher' ? 'selected' : '' }}>Finisher</option>
                        <option value="Nursery" {{ old('type') === 'Nursery' ? 'selected' : '' }}>Nursery</option>
                        <option value="Sow" {{ old('type') === 'Sow' ? 'selected' : '' }}>Sow</option>
                        <option value="Quarantine" {{ old('type') === 'Quarantine' ? 'selected' : '' }}>Quarantine</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="capacity">Capacity</label>
                    <input id="capacity" name="capacity" type="number" min="1" value="{{ old('capacity') }}" required>
                </div>

                <div class="form-group full">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" placeholder="Optional notes about this pen">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn primary">Add Pen</button>
                <a href="{{ route('pens.index') }}" class="btn">Cancel</a>
            </div>
        </form>
    </div>
@endsection