@extends('layouts.app')

@section('title', 'Record Sale')
@section('page_title', 'Record Sale')
@section('page_subtitle', 'Record sale details for this pig.')

@section('top_actions')
    <a href="{{ route('pigs.show', $pig) }}" class="btn">Back</a>
@endsection

@section('content')
<div class="panel-card">
    <h3>Record Sale</h3>

    <form method="POST" action="{{ route('sales.store', $pig) }}">
        @csrf

        <div class="form-grid">
            <div class="form-group">
                <label>Sold Date</label>
                <input type="date" name="sold_date" value="{{ old('sold_date') }}" required>
            </div>

            <div class="form-group">
                <label>Price</label>
                <input type="number" step="0.01" name="price" value="{{ old('price') }}" required>
            </div>

            <div class="form-group">
                <label>Buyer</label>
                <input type="text" name="buyer" value="{{ old('buyer') }}">
            </div>

            <div class="form-group full">
                <label>Notes</label>
                <textarea name="notes">{{ old('notes') }}</textarea>
            </div>
        </div>

        <button class="btn primary">Save Sale Record</button>
    </form>
</div>
@endsection