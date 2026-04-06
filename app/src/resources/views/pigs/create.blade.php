@extends('layouts.app')

@section('title', 'Create Pig')
@section('page_title', 'Create Pig')
@section('page_subtitle', 'Add a new pig record.')

@section('top_actions')
    <a href="{{ route('pigs.index') }}" class="btn">Back to Pig List</a>
@endsection

@section('content')
    <div class="panel-card">
        <div class="section-title">
            <div>
                <h3>New Pig Record</h3>
                <p>Fill in the details below to save a pig into the system.</p>
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
                    <input id="sex" name="sex" type="text" value="{{ old('sex') }}" required>
                </div>

                <div class="form-group">
                    <label for="pen_location">Pen Location</label>
                    <input id="pen_location" name="pen_location" type="text" value="{{ old('pen_location') }}" required>
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <input id="status" name="status" type="text" value="{{ old('status', 'active') }}" required>
                </div>

                <div class="form-group">
                    <label for="origin_date">Origin Date</label>
                    <input id="origin_date" name="origin_date" type="date" value="{{ old('origin_date') }}" required>
                </div>

                <div class="form-group">
                    <label for="latest_weight">Latest Weight</label>
                    <input id="latest_weight" name="latest_weight" type="number" step="0.01" value="{{ old('latest_weight') }}" required>
                </div>

                <div class="form-group">
                    <label for="weight_date_added">Weight Date Added</label>
                    <input id="weight_date_added" name="weight_date_added" type="date" value="{{ old('weight_date_added') }}" required>
                </div>

                <div class="form-group">
                    <label for="asset_value">Asset Value</label>
                    <input id="asset_value" name="asset_value" type="number" step="0.01" value="{{ old('asset_value') }}" required>
                </div>

                <div class="form-group">
                    <label for="date_sold">Date Sold</label>
                    <input id="date_sold" name="date_sold" type="date" value="{{ old('date_sold') }}">
                </div>

                <div class="form-group">
                    <label for="weight_sold_kg">Weight Sold (kg)</label>
                    <input id="weight_sold_kg" name="weight_sold_kg" type="number" step="0.01" value="{{ old('weight_sold_kg') }}">
                </div>

                <div class="form-group">
                    <label for="price_sold">Price Sold</label>
                    <input id="price_sold" name="price_sold" type="number" step="0.01" value="{{ old('price_sold') }}">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn primary">Save Pig</button>
                <a href="{{ route('pigs.index') }}" class="btn">Cancel</a>
            </div>
        </form>
    </div>
@endsection