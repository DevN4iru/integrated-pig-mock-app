@extends('layouts.app')

@section('title', 'Edit Sale')
@section('page_title', 'Edit Sale')
@section('page_subtitle', 'Update sale record for this pig.')

@section('top_actions')
    <a href="{{ route('pigs.show', $pig) }}" class="btn">Back</a>
@endsection

@section('content')
<div class="panel-card">
    <h3>Edit Sale</h3>

    <form method="POST" action="{{ route('sales.update', [$pig, $sale]) }}">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <div class="form-group">
                <label>Sold Date</label>
                <input type="date" name="sold_date" value="{{ old('sold_date', $sale->sold_date) }}" required>
            </div>

            <div class="form-group">
                <label>Price</label>
                <input type="number" step="0.01" name="price" value="{{ old('price', $sale->price) }}" required>
            </div>

            <div class="form-group">
                <label>Buyer</label>
                <input type="text" name="buyer" value="{{ old('buyer', $sale->buyer) }}">
            </div>

            <div class="form-group full">
                <label>Notes</label>
                <textarea name="notes">{{ old('notes', $sale->notes) }}</textarea>
            </div>
        </div>

        <div class="form-actions">
            <button class="btn primary">Save Changes</button>
            <a href="{{ route('pigs.show', $pig) }}" class="btn">Cancel</a>
        </div>
    </form>
</div>
@endsection
