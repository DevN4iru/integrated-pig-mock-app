@extends('layouts.app')

@section('title', 'Register Born Piglets')
@section('page_title', 'Register Born Piglets')
@section('page_subtitle', 'Batch-create the live piglets produced from this breeding case.')

@section('top_actions')
    <a href="{{ route('reproduction-cycles.show', $cycle) }}" class="btn">Back to Breeding Case</a>
    <a href="{{ route('pigs.index') }}" class="btn">Pig List</a>
@endsection

@section('content')
    @if($errors->any())
        <div class="panel-card" style="margin-bottom: 20px; border-color: #f0b4b4; background: #fff7f7;">
            <div class="section-title">
                <div>
                    <h3 style="color:#b42318;">Please fix the following</h3>
                    <p>The batch was not saved. Review the messages below.</p>
                </div>
            </div>

            <ul style="margin:0; padding-left: 20px; color:#b42318;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="panel-card" style="margin-bottom: 20px;">
        <div class="section-title">
            <div>
                <h3>Birth Registration Context</h3>
                <p>This batch flow registers the live piglets from the farrowing already recorded on the breeding case.</p>
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label>Sow</label>
                <input type="text" value="{{ $cycle->sow?->ear_tag ?? '—' }}" readonly>
            </div>

            <div class="form-group">
                <label>Boar</label>
                <input type="text" value="{{ $cycle->boar?->ear_tag ?? '—' }}" readonly>
            </div>

            <div class="form-group">
                <label>Actual Farrow Date</label>
                <input type="text" value="{{ $cycle->actual_farrow_date?->format('Y-m-d') ?? '—' }}" readonly>
            </div>

            <div class="form-group">
                <label>Born Alive</label>
                <input type="text" value="{{ (int) $cycle->born_alive }}" readonly>
            </div>

            <div class="form-group">
                <label>Source</label>
                <input type="text" value="Birthed" readonly>
            </div>

            <div class="form-group">
                <label>Current Price per kg</label>
                <input type="text" value="₱ {{ number_format((float) $pricePerKg, 2) }}" readonly>
            </div>
        </div>

        @if($recommendedPens->isNotEmpty())
            <div class="flash" style="margin-top: 16px;">
                Recommended pens for newborn registration:
                <strong>{{ $recommendedPens->pluck('name')->join(', ') }}</strong>
            </div>
        @endif

        <div class="flash success" style="margin-top: 16px;">
            Every piglet created here will be linked back to this breeding case and the mother sow. Re-registering the same litter later will be blocked.
        </div>
    </div>

    <div class="panel-card">
        <div class="section-title">
            <div>
                <h3>Live Piglet Registration</h3>
                <p>Enter one row for each born-alive piglet. Date added is fixed to the farrow date and source is automatically set to birthed.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('pigs.store-born-batch', $cycle) }}">
            @csrf

            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Ear Tag</th>
                            <th>Breed</th>
                            <th>Sex</th>
                            <th>Assigned Pen</th>
                            <th>Birth Weight (kg)</th>
                            <th>Asset Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for($i = 0; $i < $pigletCount; $i++)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>
                                    <input
                                        type="text"
                                        name="piglets[{{ $i }}][ear_tag]"
                                        value="{{ old("piglets.$i.ear_tag") }}"
                                        required
                                    >
                                    @error("piglets.$i.ear_tag")
                                        <div class="error-text">{{ $message }}</div>
                                    @enderror
                                </td>
                                <td>
                                    <input
                                        type="text"
                                        name="piglets[{{ $i }}][breed]"
                                        value="{{ old("piglets.$i.breed", $cycle->sow?->breed) }}"
                                        required
                                    >
                                    @error("piglets.$i.breed")
                                        <div class="error-text">{{ $message }}</div>
                                    @enderror
                                </td>
                                <td>
                                    <select name="piglets[{{ $i }}][sex]" required>
                                        <option value="">Select sex</option>
                                        <option value="male" {{ old("piglets.$i.sex") === 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old("piglets.$i.sex") === 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="undetermined" {{ old("piglets.$i.sex") === 'undetermined' ? 'selected' : '' }}>Undetermined</option>
                                    </select>
                                    @error("piglets.$i.sex")
                                        <div class="error-text">{{ $message }}</div>
                                    @enderror
                                </td>
                                <td>
                                    <select name="piglets[{{ $i }}][pen_id]" required>
                                        <option value="">Select pen</option>
                                        @foreach($pens as $pen)
                                            <option value="{{ $pen->id }}" {{ (string) old("piglets.$i.pen_id") === (string) $pen->id ? 'selected' : '' }}>
                                                {{ $pen->name }} — {{ $pen->type }} ({{ $pen->availableSlots() }} slot(s) open)
                                            </option>
                                        @endforeach
                                    </select>
                                    @error("piglets.$i.pen_id")
                                        <div class="error-text">{{ $message }}</div>
                                    @enderror
                                </td>
                                <td>
                                    <input
                                        class="piglet-weight-input"
                                        data-preview="piglet_asset_preview_{{ $i }}"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        name="piglets[{{ $i }}][latest_weight]"
                                        value="{{ old("piglets.$i.latest_weight") }}"
                                        required
                                    >
                                    @error("piglets.$i.latest_weight")
                                        <div class="error-text">{{ $message }}</div>
                                    @enderror
                                </td>
                                <td>
                                    <input
                                        id="piglet_asset_preview_{{ $i }}"
                                        type="text"
                                        value="0.00"
                                        readonly
                                    >
                                </td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>

            <div class="flash" style="margin-top: 16px;">
                This batch flow registers only the <strong>born alive</strong> piglets. Stillborn and mummified counts stay as breeding outcome records and are not created as pig records.
            </div>

            <div class="form-actions">
                <button type="submit" class="btn primary">Register Born Piglets</button>
                <a href="{{ route('reproduction-cycles.show', $cycle) }}" class="btn">Cancel</a>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
const BIRTH_PRICE_PER_KG = {{ json_encode((float) $pricePerKg) }};

function updatePigletAssetPreview(input) {
    const previewId = input.getAttribute('data-preview');
    const preview = document.getElementById(previewId);
    if (!preview) return;

    const weight = parseFloat(input.value || '0');
    const asset = Number.isNaN(weight) ? 0 : (weight * BIRTH_PRICE_PER_KG);

    preview.value = asset.toFixed(2);
}

document.querySelectorAll('.piglet-weight-input').forEach((input) => {
    input.addEventListener('input', function () {
        updatePigletAssetPreview(this);
    });

    updatePigletAssetPreview(input);
});
@endsection
