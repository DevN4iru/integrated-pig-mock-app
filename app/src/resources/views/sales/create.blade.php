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

    <div class="form-grid" style="margin-bottom: 18px;">
        <div class="form-group">
            <label>Current Weight</label>
            <input type="text" value="{{ number_format((float) $currentWeight, 2) }} kg" readonly>
        </div>
<div class="form-group">
            <label>Current Farm Value</label>
            <input type="text" value="₱ {{ number_format((float) $recommendedPrice, 2) }}" readonly>
        </div>

        <div class="form-group full">
            <label>Pricing Note</label>
            <input type="text" value="Suggested price uses the pig's manually entered farm value." readonly>
        </div>
    </div>

    <form method="POST" action="{{ route('sales.store', $pig) }}">
        @csrf

        <div class="form-grid">
            <div class="form-group">
                <label>Sold Date</label>
                <input type="date" name="sold_date" value="{{ old('sold_date', now()->toDateString()) }}" required>
            </div>

            <div class="form-group">
                <label>Price</label>
                <input id="sale_price_input" type="number" step="0.01" min="0" name="price" value="{{ old('price', number_format((float) $recommendedPrice, 2, '.', '')) }}" required>
                <div class="inline-note">Use the farm value or override it if buyer negotiation changes the final deal.</div>
            </div>

            <div class="form-group">
                <label>&nbsp;</label>
                <button type="button" class="btn" onclick="useRecommendedSalePrice('{{ number_format((float) $recommendedPrice, 2, '.', '') }}')">
                    Use Farm Value
                </button>
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

@section('scripts')
function useRecommendedSalePrice(value) {
    const input = document.getElementById('sale_price_input');
    if (input) {
        input.value = value;
    }
}
@endsection
