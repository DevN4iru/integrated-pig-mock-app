<form method="POST" action="{{ route('reproduction-cycle-updates.store', $cycle) }}" id="reproduction-update-form">
    @csrf

    @php
        $today = now()->toDateString();
        $oldEventType = old('event_type', '');
        $oldEventDate = old('event_date', $today);
        $oldActualFarrowDate = old(
            'actual_farrow_date',
            $oldEventType === \App\Models\ReproductionCycleUpdate::EVENT_FARROWING_RECORDED ? $oldEventDate : ''
        );
    @endphp

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
                max="{{ $today }}"
                required
            >
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
                max="{{ $today }}"
                disabled
            >
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
            <label for="notes">Notes / Observation</label>
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
    const actualFarrowDate = form.querySelector('#actual_farrow_date');
    const notes = form.querySelector('#notes');
    const help = form.querySelector('#event_help');
    const groups = Array.from(form.querySelectorAll('.event-specific'));
    const notesLabel = form.querySelector('label[for="notes"]');
    const today = new Date().toISOString().split('T')[0];

    let actualFarrowDateTouched = actualFarrowDate ? actualFarrowDate.value !== '' : false;

    const configs = {
        pregnancy_checked: {
            help: 'Pregnancy check records the diagnosis only. Choose Pregnant or Not Pregnant. Returned to heat is a separate later event.',
            notesLabel: 'Pregnancy Check Notes / Symptoms',
            notesPlaceholder: 'Add pregnancy check findings, symptoms, or observations.'
        },
        returned_to_heat: {
            help: 'Returned to heat is a separate observation after a not-pregnant result. Use notes to describe heat signs or repeat-service readiness.',
            notesLabel: 'Return-to-Heat Notes / Signs',
            notesPlaceholder: 'Describe the observed return-to-heat signs or repeat-service notes.'
        },
        farrowing_recorded: {
            help: 'Farrowing records only farrowing-specific fields. Actual farrow date cannot be in the future and should usually match the farrowing event date.',
            notesLabel: 'Farrowing Notes',
            notesPlaceholder: 'Add farrowing observations, complications, or post-farrow notes.'
        },
        cycle_closed: {
            help: 'Cycle closure is only for ended cases. Add a clear closure note.',
            notesLabel: 'Closure Notes',
            notesPlaceholder: 'Explain why this breeding case is being closed.'
        }
    };

    function clearGroupValues(group) {
        group.querySelectorAll('input, select, textarea').forEach((field) => {
            if (field.id === 'actual_farrow_date') {
                return;
            }

            if (field.tagName === 'SELECT') {
                field.selectedIndex = 0;
            } else {
                field.value = '';
            }
        });
    }

    function syncActualFarrowDate() {
        if (!actualFarrowDate) return;

        actualFarrowDate.max = today;

        if (eventType.value !== 'farrowing_recorded') {
            actualFarrowDateTouched = false;
            return;
        }

        if (!actualFarrowDateTouched && eventDate.value) {
            actualFarrowDate.value = eventDate.value > today ? today : eventDate.value;
        }

        if (actualFarrowDate.value && actualFarrowDate.value > today) {
            actualFarrowDate.value = today;
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
                field.disabled = !visible;
            });

            if (!visible) {
                clearGroupValues(group);
            }
        });

        if (!selected || !configs[selected]) {
            help.style.display = 'none';
            help.textContent = '';
            notesLabel.textContent = 'Notes / Observation';
            notes.placeholder = 'Add the observation or event note here.';
            syncActualFarrowDate();
            return;
        }

        help.style.display = '';
        help.textContent = configs[selected].help;
        notesLabel.textContent = configs[selected].notesLabel;
        notes.placeholder = configs[selected].notesPlaceholder;

        syncActualFarrowDate();
    }

    eventDate.max = today;

    eventType.addEventListener('change', refresh);
    eventDate.addEventListener('input', syncActualFarrowDate);

    if (actualFarrowDate) {
        actualFarrowDate.addEventListener('input', function () {
            actualFarrowDateTouched = this.value !== '';
        });
    }

    refresh();
})();
</script>
