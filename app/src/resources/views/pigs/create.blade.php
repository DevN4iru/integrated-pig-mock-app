@extends('layouts.app')

@section('title', 'Create Pig')
@section('page_title', 'Create Pig')
@section('page_subtitle', 'Add a new pig record.')

@section('top_actions')
    <a href="{{ route('pigs.index') }}">Back to Pig List</a>
@endsection

@section('content')
    @if ($errors->any())
        <div>
            <p>Please fix the following errors:</p>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('pigs.store') }}">
        @csrf

        <div>
            <label for="ear_tag">Ear Tag</label>
            <input id="ear_tag" name="ear_tag" type="text" value="{{ old('ear_tag') }}" required>
        </div>

        <div>
            <label for="breed">Breed</label>
            <input id="breed" name="breed" type="text" value="{{ old('breed') }}" required>
        </div>

        <div>
            <label for="sex">Sex</label>
            <input id="sex" name="sex" type="text" value="{{ old('sex') }}" required>
        </div>

        <div>
            <label for="pen_location">Pen Location</label>
            <input id="pen_location" name="pen_location" type="text" value="{{ old('pen_location') }}" required>
        </div>

        <div>
            <label for="status">Status</label>
            <input id="status" name="status" type="text" value="{{ old('status', 'active') }}" required>
        </div>

        <div>
            <label for="origin_date">Origin Date</label>
            <input id="origin_date" name="origin_date" type="date" value="{{ old('origin_date') }}" required>
        </div>

        <div>
            <label for="latest_weight">Latest Weight</label>
            <input id="latest_weight" name="latest_weight" type="number" step="0.01" value="{{ old('latest_weight') }}" required>
        </div>

        <div>
            <label for="weight_date_added">Weight Date Added</label>
            <input id="weight_date_added" name="weight_date_added" type="date" value="{{ old('weight_date_added') }}" required>
        </div>

        <div>
            <label for="asset_value">Asset Value</label>
            <input id="asset_value" name="asset_value" type="number" step="0.01" value="{{ old('asset_value') }}" required>
        </div>

        <div>
            <label for="date_sold">Date Sold</label>
            <input id="date_sold" name="date_sold" type="date" value="{{ old('date_sold') }}">
        </div>

        <div>
            <label for="weight_sold_kg">Weight Sold (kg)</label>
            <input id="weight_sold_kg" name="weight_sold_kg" type="number" step="0.01" value="{{ old('weight_sold_kg') }}">
        </div>

        <div>
            <label for="price_sold">Price Sold</label>
            <input id="price_sold" name="price_sold" type="number" step="0.01" value="{{ old('price_sold') }}">
        </div>

        <div>
            <button type="submit">Save Pig</button>
        </div>
    </form>
@endsection
