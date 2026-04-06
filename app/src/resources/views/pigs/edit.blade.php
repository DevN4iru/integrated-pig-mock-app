@extends('layouts.app')

@section('title', 'Edit Pig')
@section('page_title', 'Edit Pig')
@section('page_subtitle', 'Update an existing pig record.')

@section('top_actions')
    <a href="{{ route('pigs.index') }}" class="btn">Back to Pig List</a>
@endsection

@section('content')
    <div class="panel-card">
        <div class="section-title">
            <div>
                <h3>Edit Pig Record</h3>
                <p>Update the details below and save changes.</p>
            </div>
        </div>

        @php
            $dateValue = old('date_added', $pig->date_added ? substr((string) $pig->date_added, 0, 10) : '');
        @endphp

        <form method="POST" action="{{ route('pigs.update', $pig) }}">
            @csrf
            @method('PUT')

            <div class="form-grid">
                <div class="form-group">
                    <label for="ear_tag">Ear Tag</label>
                    <input id="ear_tag" name="ear_tag" type="text" value="{{ old('ear_tag', $pig->ear_tag) }}" required>
                </div>

                <div class="form-group">
                    <label for="breed">Breed</label>
                    <input id="breed" name="breed" type="text" value="{{ old('breed', $pig->breed) }}" required>
                </div>

                <div class="form-group">
                    <label for="sex">Sex</label>
                    <select id="sex" name="sex" required>
                        <option value="">Select sex</option>
                        <option value="male" {{ old('sex', $pig->sex) === 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('sex', $pig->sex) === 'female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="pen_id">Assigned Pen</label>
                    <select id="pen_id" name="pen_id" required>
                        <option value="">Select pen</option>
                        @foreach ($pens as $pen)
                            <option value="{{ $pen->id }}" {{ (string) old('pen_id', $pig->pen_id) === (string) $pen->id ? 'selected' : '' }}>
                                {{ $pen->name }} — {{ $pen->type }} (Cap: {{ $pen->capacity }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="pig_source">Pig Source</label>
                    <select id="pig_source" name="pig_source" required>
                        <option value="">Select source</option>
                        <option value="birthed" {{ old('pig_source', $pig->pig_source) === 'birthed' ? 'selected' : '' }}>Birthed</option>
                        <option value="purchased" {{ old('pig_source', $pig->pig_source) === 'purchased' ? 'selected' : '' }}>Purchased</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="date_added">Date Added</label>
                    <input id="date_added" name="date_added" type="date" value="{{ $dateValue }}" required>
                </div>

                <div class="form-group">
                    <label for="latest_weight">Weight Upon Entry</label>
                    <input id="latest_weight" name="latest_weight" type="number" step="0.01" value="{{ old('latest_weight', $pig->latest_weight) }}" required>
                </div>

                <div class="form-group">
                    <label for="asset_value">Asset Value</label>
                    <input id="asset_value" name="asset_value" type="number" step="0.01" value="{{ old('asset_value', $pig->asset_value) }}" required>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn primary">Save Changes</button>
                <a href="{{ route('pigs.index') }}" class="btn">Cancel</a>
            </div>
        </form>
    </div>
@endsection