@extends('layouts.app')

@section('title', 'Transfer Pig')
@section('page_title', 'Transfer Pig')
@section('page_subtitle', 'Move this pig to another pen and preserve transfer history.')

@section('top_actions')
    <a href="{{ route('pigs.show', $pig) }}" class="btn">Back to Pig Profile</a>
@endsection

@section('content')
    @php
        $currentPen = $pig->pen;
    @endphp

    <div class="panel-card" style="margin-bottom: 20px;">
        <div class="section-title">
            <div>
                <h3>Current Assignment</h3>
                <p>This pig will be moved from its current pen to a new destination.</p>
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label>Ear Tag</label>
                <input type="text" value="{{ $pig->ear_tag }}" readonly>
            </div>

            <div class="form-group">
                <label>Current Pen</label>
                <input type="text" value="{{ $currentPen?->name ?? '—' }}" readonly>
            </div>

            <div class="form-group">
                <label>Current Pen Type</label>
                <input type="text" value="{{ $currentPen?->type ?? '—' }}" readonly>
            </div>

            <div class="form-group">
                <label>Occupancy</label>
                <input type="text" value="{{ $currentPen ? $currentPen->pigs_count . '/' . $currentPen->capacity : '—' }}" readonly>
            </div>
        </div>
    </div>

    <div class="panel-card">
        <div class="section-title">
            <div>
                <h3>Transfer Details</h3>
                <p>Select a valid destination pen and provide transfer context.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('pig-transfers.store', $pig) }}">
            @csrf

            <div class="form-grid">
                <div class="form-group full">
                    <label for="to_pen_id">Destination Pen</label>

                    <select id="to_pen_id" name="to_pen_id" required>
                        <option value="">Select destination pen</option>

                        @foreach ($destinationPens->groupBy('type') as $type => $pensByType)
                            <optgroup label="{{ strtoupper($type) }}">
                                @foreach ($pensByType as $pen)
                                    @php
                                        $isFull = $pen->pigs_count >= $pen->capacity;
                                        $remaining = $pen->capacity - $pen->pigs_count;

                                        $label = "{$pen->name} ({$pen->pigs_count}/{$pen->capacity})";

                                        if ($isFull) {
                                            $label .= " - FULL";
                                        } elseif ($remaining <= 2) {
                                            $label .= " - NEAR FULL";
                                        }
                                    @endphp

                                    <option
                                        value="{{ $pen->id }}"
                                        {{ $isFull ? 'disabled' : '' }}
                                        {{ (string) old('to_pen_id') === (string) $pen->id ? 'selected' : '' }}
                                    >
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>

                    <small class="metric-note">
                        Full pens are disabled. Pens near capacity are marked.
                    </small>
                </div>

                <div class="form-group">
                    <label for="transfer_date">Transfer Date</label>
                    <input
                        id="transfer_date"
                        name="transfer_date"
                        type="date"
                        value="{{ old('transfer_date', now()->toDateString()) }}"
                        max="{{ now()->toDateString() }}"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="reason_code">Transfer Reason</label>
                    <select id="reason_code" name="reason_code" required>
                        <option value="">Select reason</option>
                        @foreach ($reasonOptions as $value => $label)
                            <option value="{{ $value }}" {{ old('reason_code') === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group full">
                    <label for="reason_notes">Reason Notes</label>
                    <textarea
                        id="reason_notes"
                        name="reason_notes"
                        placeholder="Optional additional detail about this transfer"
                    >{{ old('reason_notes') }}</textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn primary">Confirm Transfer</button>
                <a href="{{ route('pigs.show', $pig) }}" class="btn">Cancel</a>
            </div>
        </form>
    </div>
@endsection
