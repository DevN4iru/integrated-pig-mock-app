@extends('layouts.app')

@section('title', 'Edit Pen')
@section('page_title', 'Edit Pen')
@section('page_subtitle', 'Update an existing pen record.')

@section('top_actions')
    <a href="{{ route('pens.index') }}" class="btn">Back to Pen List</a>
@endsection

@section('content')
    <div class="panel-card">
        <div class="section-title">
            <div>
                <h3>Edit Pen Record</h3>
                <p>Update the details below and save changes.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('pens.update', $pen) }}">
            @csrf
            @method('PUT')

            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Pen Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $pen->name) }}" required>
                </div>

                <div class="form-group">
                    <label for="type">Pen Type</label>
                    <select id="type" name="type" required>
                        <option value="">Select type</option>
                        <option value="Grower" {{ old('type', $pen->type) === 'Grower' ? 'selected' : '' }}>Grower</option>
                        <option value="Finisher" {{ old('type', $pen->type) === 'Finisher' ? 'selected' : '' }}>Finisher</option>
                        <option value="Nursery" {{ old('type', $pen->type) === 'Nursery' ? 'selected' : '' }}>Nursery</option>
                        <option value="Sow" {{ old('type', $pen->type) === 'Sow' ? 'selected' : '' }}>Sow</option>
                        <option value="Quarantine" {{ old('type', $pen->type) === 'Quarantine' ? 'selected' : '' }}>Quarantine</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="capacity">Capacity</label>
                    <input id="capacity" name="capacity" type="number" min="1" value="{{ old('capacity', $pen->capacity) }}" required>
                </div>

                <div class="form-group full">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes">{{ old('notes', $pen->notes) }}</textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn primary">Save Changes</button>
                <a href="{{ route('pens.index') }}" class="btn">Cancel</a>
            </div>
        </form>
    </div>
@endsection