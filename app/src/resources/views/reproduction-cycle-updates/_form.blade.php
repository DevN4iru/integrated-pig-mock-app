@php
    $oldEventType = old('event_type', request('event_type', ''));
    $oldEventDate = old('event_date', now()->toDateString());

    $projectedExpectedFarrowDate = optional($cycle->expected_farrow_date)->copy()
        ?? optional($cycle->service_date)->copy()?->addDays(114);

    $expectedFarrowDateValue = $projectedExpectedFarrowDate?->format('Y-m-d') ?? '';
    $farrowWindowStartValue = $projectedExpectedFarrowDate?->copy()->subMonthNoOverflow()->startOfMonth()->format('Y-m-d') ?? '';
    $farrowWindowEndValue = $projectedExpectedFarrowDate?->copy()->addMonthNoOverflow()->endOfMonth()->format('Y-m-d') ?? '';

    $defaultActualFarrowDate = '';

    if (
        $oldEventType === \App\Models\ReproductionCycleUpdate::EVENT_FARROWING_RECORDED
        && $projectedExpectedFarrowDate
    ) {
        $defaultActualFarrowDate = $expectedFarrowDateValue;
    }

    $oldActualFarrowDate = old('actual_farrow_date', $defaultActualFarrowDate);
@endphp

<form
    method="POST"
    action="{{ route('reproduction-cycle-updates.store', $cycle) }}"
    id="reproduction-update-form"
    data-expected-farrow-date="{{ $expectedFarrowDateValue }}"
    data-farrow-window-start="{{ $farrowWindowStartValue }}"
    data-farrow-window-end="{{ $farrowWindowEndValue }}"
>
    @csrf

    <div class="form-grid">
        <div class="form-group">
            <label for="event_type">Event Type</label>
            <select id="event_type" name="event_type" required>
                <option value="">Select event</option>
                @foreach($availableUpdateEvents as $value => $label)
                    <option value="{{ $value }}" {{ $oldEventType === $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            @error('event_type')
                <div class="error-text">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="event_date">Event Date</label>
            <input
                id="event_date"
                type="date"
                name="event_date"
                value="{{ $oldEventDate }}"
                required
            >
            <small class="metric-note">Event Date is the timeline date for this update. For farrowing, keep it on the same day as the Actual Farrow Date or later.</small>
            @error('event_date')
                <div class="error-text">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group full">
            <div id="event_help" class="flash success" style="display:none;"></div>
        </div>

        <div class="form-group event-specific" data-events="pregnancy_checked">
            <label for="pregnancy_result">Pregnancy Result</label>
            <select id="pregnancy_result" name="pregnancy_result" disabled>
                <option value="">Select result</option>
                @foreach($pregnancyResultOptions as $value => $label)
                    @if($value !== \App\Models\ReproductionCycle::PREGNANCY_RESULT_PENDING)
                        <option value="{{ $value }}" {{ old('pregnancy_result') === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endif
                @endforeach
            </select>
            @error('pregnancy_result')
                <div class="error-text">{{ $message }}</div>
            @enderror
        </div>

        <div
            class="form-group event-specific full"
            data-events="pregnancy_checked"
            id="pregnancy_expected_preview_group"
            style="display:none;"
        >
            <label for="pregnancy_expected_farrow_preview">Computed Expected Farrow Date</label>
            <input
                id="pregnancy_expected_farrow_preview"
                type="text"
                value=""
                readonly
            >
            <small class="metric-note" id="pregnancy_expected_preview_note">Computed from service date + 114 days.</small>
        </div>

        <div class="form-group event-specific" data-events="pregnancy_checked,farrowing_recorded">
            <label for="added_cost">Added Cost</label>
            <input
                id="added_cost"
                type="number"
                name="added_cost"
                value="{{ old('added_cost', '0') }}"
                min="0"
                step="0.01"
                placeholder="0.00"
                disabled
            >
            @error('added_cost')
                <div class="error-text">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group event-specific" data-events="farrowing_recorded">
            <label for="actual_farrow_date">Actual Farrow Date</label>
            <input
                id="actual_farrow_date"
                type="date"
                name="actual_farrow_date"
                value="{{ $oldActualFarrowDate }}"
                disabled
            >
            <small class="metric-note" id="actual_farrow_window_note" style="display:none;"></small>
            @error('actual_farrow_date')
                <div class="error-text">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group event-specific" data-events="farrowing_recorded">
            <label for="total_born">Total Born</label>
            <input
                id="total_born"
                type="number"
                name="total_born"
                value="{{ old('total_born') }}"
                min="0"
                disabled
            >
            @error('total_born')
                <div class="error-text">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group event-specific" data-events="farrowing_recorded">
            <label for="born_alive">Born Alive</label>
            <input
                id="born_alive"
                type="number"
                name="born_alive"
                value="{{ old('born_alive') }}"
                min="0"
                disabled
            >
            @error('born_alive')
                <div class="error-text">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group event-specific" data-events="farrowing_recorded">
            <label for="stillborn">Stillborn</label>
            <input
                id="stillborn"
                type="number"
                name="stillborn"
                value="{{ old('stillborn') }}"
                min="0"
                disabled
            >
            @error('stillborn')
                <div class="error-text">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group event-specific" data-events="farrowing_recorded">
            <label for="mummified">Mummified</label>
            <input
                id="mummified"
                type="number"
                name="mummified"
                value="{{ old('mummified') }}"
                min="0"
                disabled
            >
            @error('mummified')
                <div class="error-text">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group full">
            <label for="notes" id="notes_label">Notes / Observation</label>
            <textarea
                id="notes"
                name="notes"
                rows="4"
                placeholder="Add the observation or event note here."
            >{{ old('notes') }}</textarea>
            @error('notes')
                <div class="error-text">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top: 16px;">
        <button type="submit" class="btn primary">Add Progress Update</button>
        <a href="{{ route('reproduction-cycles.index') }}" class="btn">Back to Breeding</a>
    </div>
</form>

<script>
(function () {
    const form = document.getElementById('reproduction-update-form');
    if (!form) return;

    const eventType = form.querySelector('#event_type');
    const eventDate = form.querySelector('#event_date');
    const pregnancyResult = form.querySelector('#pregnancy_result');
    const actualFarrowDate = form.querySelector('#actual_farrow_date');
    const actualFarrowWindowNote = form.querySelector('#actual_farrow_window_note');
    const notes = form.querySelector('#notes');
    const notesLabel = form.querySelector('#notes_label');
    const help = form.querySelector('#event_help');
    const groups = Array.from(form.querySelectorAll('.event-specific'));

    const pregnancyExpectedPreviewGroup = form.querySelector('#pregnancy_expected_preview_group');
    const pregnancyExpectedFarrowPreview = form.querySelector('#pregnancy_expected_farrow_preview');
    const pregnancyExpectedPreviewNote = form.querySelector('#pregnancy_expected_preview_note');

    const expectedFarrowDate = form.dataset.expectedFarrowDate || '';
    const farrowWindowStart = form.dataset.farrowWindowStart || '';
    const farrowWindowEnd = form.dataset.farrowWindowEnd || '';

    let eventDateTouched = false;
    let actualFarrowDateTouched = actualFarrowDate ? actualFarrowDate.value !== '' : false;

    const configs = {
        pregnancy_checked: {
            help: 'Pregnancy check records the diagnosis only. Choose Pregnant or Not Pregnant.',
            notesLabel: 'Pregnancy Check Notes / Symptoms',
            notesPlaceholder: 'Add pregnancy check findings, symptoms, or observations.'
        },
        returned_to_heat: {
            help: 'Returned to heat confirms that the failed attempt cycled back. After this, you can quick-close the parent case or start the next attempt from the case page.',
            notesLabel: 'Return-to-Heat Notes / Signs',
            notesPlaceholder: 'Describe the observed return-to-heat signs or repeat-service notes.'
        },
        farrowing_recorded: {
            help: 'Farrowing records only farrowing-specific fields. Actual Farrow Date may differ from Event Date, but it cannot be later than Event Date on submit.',
            notesLabel: 'Farrowing Notes',
            notesPlaceholder: 'Add farrowing observations, complications, or post-farrow notes.'
        },
        cycle_closed: {
            help: 'Cycle closure ends the parent breeding case. Use this after return to heat or after farrowing when the case is fully finished.',
            notesLabel: 'Closure Notes',
            notesPlaceholder: 'Explain why this breeding case is being closed.'
        }
    };

    function clearGroupValues(group) {
        group.querySelectorAll('input, select, textarea').forEach((field) => {
            if (field.id === 'actual_farrow_date') {
                return;
            }

            if (field.id === 'pregnancy_expected_farrow_preview') {
                return;
            }

            if (field.tagName === 'SELECT') {
                field.selectedIndex = 0;
            } else {
                field.value = '';
            }
        });
    }

    function showPregnancyPreview(selectedEvent) {
        if (!pregnancyExpectedPreviewGroup || !pregnancyExpectedFarrowPreview) {
            return;
        }

        const show = selectedEvent === 'pregnancy_checked'
            && pregnancyResult
            && pregnancyResult.value === 'pregnant';

        pregnancyExpectedPreviewGroup.style.display = show ? '' : 'none';

        if (!show) {
            pregnancyExpectedFarrowPreview.value = '';
            return;
        }

        pregnancyExpectedFarrowPreview.value = expectedFarrowDate || 'Unavailable';

        if (pregnancyExpectedPreviewNote) {
            pregnancyExpectedPreviewNote.textContent = expectedFarrowDate
                ? 'Computed from service date + 114 days.'
                : 'Expected farrow date cannot be computed because service date is unavailable.';
        }
    }

    function syncActualFarrowDate() {
        if (!actualFarrowDate) return;

        if (eventType.value !== 'farrowing_recorded') {
            actualFarrowDateTouched = false;

            if (actualFarrowWindowNote) {
                actualFarrowWindowNote.style.display = 'none';
                actualFarrowWindowNote.textContent = '';
            }

            return;
        }

        if (!eventDateTouched && expectedFarrowDate && eventDate && (!eventDate.value || eventDate.value < expectedFarrowDate)) {
            eventDate.value = expectedFarrowDate;
        }

        if (actualFarrowWindowNote) {
            actualFarrowWindowNote.style.display = '';

            if (expectedFarrowDate && farrowWindowStart && farrowWindowEnd) {
                actualFarrowWindowNote.textContent =
                    `Default actual farrow date is set from the computed expected farrow date (${expectedFarrowDate}). Allowed biological fallback window: ${farrowWindowStart} to ${farrowWindowEnd}. You may edit both dates, but Actual Farrow Date cannot be later than Event Date on submit.`;
            } else if (expectedFarrowDate) {
                actualFarrowWindowNote.textContent =
                    `Default actual farrow date is set from the computed expected farrow date (${expectedFarrowDate}). You may edit both dates, but Actual Farrow Date cannot be later than Event Date on submit.`;
            } else {
                actualFarrowWindowNote.textContent =
                    'Actual Farrow Date may be entered manually. You may edit both dates, but Actual Farrow Date cannot be later than Event Date on submit.';
            }
        }

        if (!actualFarrowDateTouched) {
            actualFarrowDate.value = expectedFarrowDate || '';
        }
    }

    function refresh() {
        const selected = eventType.value;

        groups.forEach((group) => {
            const allowedEvents = (group.dataset.events || '')
                .split(',')
                .map(value => value.trim())
                .filter(Boolean);

            const visible = selected !== '' && allowedEvents.includes(selected);

            group.style.display = visible ? '' : 'none';

            group.querySelectorAll('input, select, textarea').forEach((field) => {
                if (field.id === 'pregnancy_expected_farrow_preview') {
                    field.disabled = true;
                    return;
                }

                field.disabled = !visible;
            });

            if (!visible) {
                clearGroupValues(group);
            }
        });

        if (!selected || !configs[selected]) {
            help.style.display = 'none';
            help.textContent = '';

            if (notesLabel) notesLabel.textContent = 'Notes / Observation';
            if (notes) notes.placeholder = 'Add the observation or event note here.';

            showPregnancyPreview(selected);
            syncActualFarrowDate();
            return;
        }

        help.style.display = '';
        help.textContent = configs[selected].help;

        if (notesLabel) notesLabel.textContent = configs[selected].notesLabel;
        if (notes) notes.placeholder = configs[selected].notesPlaceholder;

        showPregnancyPreview(selected);
        syncActualFarrowDate();
    }

    if (eventDate) {
        eventDate.addEventListener('input', function () {
            eventDateTouched = true;
        });

        eventDate.addEventListener('change', function () {
            eventDateTouched = true;
        });
    }

    if (actualFarrowDate) {
        actualFarrowDate.addEventListener('input', function () {
            actualFarrowDateTouched = this.value !== '';
        });

        actualFarrowDate.addEventListener('change', function () {
            actualFarrowDateTouched = this.value !== '';
        });
    }

    if (pregnancyResult) {
        pregnancyResult.addEventListener('change', refresh);
    }

    eventType.addEventListener('change', refresh);
    eventDate.addEventListener('change', syncActualFarrowDate);

    refresh();
})();
</script>