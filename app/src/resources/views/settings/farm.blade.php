@extends('layouts.app')

@section('title', 'Farm Settings')
@section('page_title', 'Farm Settings')
@section('page_subtitle', 'Configure global pricing used for pig asset valuation.')

@section('top_actions')
    <a href="{{ route('dashboard') }}" class="btn">Back to Dashboard</a>
@endsection

@section('content')
    <div class="panel-card">
        <div class="section-title">
            <div>
                <h3>Global Price Per Kilo</h3>
                <p>This value is used to auto-compute pig asset value: latest weight × price per kilo.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('settings.farm.update') }}">
            @csrf
            @method('PUT')

            <div class="form-grid">
                <div class="form-group">
                    <label for="price_per_kg">Price per kg</label>
                    <input
                        id="price_per_kg"
                        name="price_per_kg"
                        type="number"
                        step="0.01"
                        min="0"
                        value="{{ old('price_per_kg', $setting->price_per_kg) }}"
                        required
                    >
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn primary">Save Settings</button>
            </div>
        </form>
    </div>
@endsection