@extends('layouts.app')

@section('title', 'Add Pig')
@section('page_title', 'Add Pig')
@section('page_subtitle', 'Add a new pig record.')

@section('top_actions')
    <a href="{{ route('pigs.index') }}" class="btn">Back to Pig List</a>
@endsection

@section('content')
    <div class="panel-card">
        <div class="section-title">
            <div>
                <h3>Add Pig Record</h3>
                <p>Fill in the details below to add a pig into the system.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('pigs.store') }}">
            @csrf

            <div class="form-grid">
                <div class="form-group">
                    <label for="ear_tag">Ear Tag</label>
                    <input id="ear_tag" name="ear_tag" type="text" value="{{ old('ear_tag') }}" required>
                </div>

                <div class="form-group">
                    <label for="breed">Breed</label>
                    <input id="breed" name="breed" type="text" value="{{ old('breed') }}" required>
                </div>

                <div class="form-group">
                    <label for="sex">Sex</label>
                    <select id="sex" name="sex" required>
                        <option value="">Select sex</option>
                        <option value="male" {{ old('sex') === 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('sex') === 'female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="pen_location">Pen Location</label>
                    <input id="pen_location" name="pen_location" type="text" value="{{ old('pen_location') }}" required>
                </div>

                <div class="form-group">
                    <label for="pig_source">Pig Source</label>
                    <select id="pig_source" name="pig_source" required>
                        <option value="">Select source</option>
                        <option value="birthed" {{ old('pig_source') === 'birthed' ? 'selected' : '' }}>Birthed</option>
                        <option value="purchased" {{ old('pig_source') === 'purchased' ? 'selected' : '' }}>Purchased</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="date_added">Date Added</label>
                    <input id="date_added" name="date_added" type="date" value="{{ old('date_added') }}" required>
                </div>

                <div class="form-group">
                    <label for="latest_weight">Weight Upon Entry</label>
                    <input id="latest_weight" name="latest_weight" type="number" step="0.01" value="{{ old('latest_weight') }}" required>
                </div>

                <div class="form-group">
                    <label for="asset_value">Asset Value</label>
                    <input id="asset_value" name="asset_value" type="number" step="0.01" value="{{ old('asset_value') }}" required>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn primary">Add Pig</button>
                <a href="{{ route('pigs.index') }}" class="btn">Cancel</a>
            </div>
        </form>
    </div>
@endsection