<div class="protocol-guide-card-grid">
    <div class="protocol-guide-card">
        <h4>Medication Program</h4>
        <p>Read-only operational program view for medication, supplement, procedure, and management items in this shared program.</p>

        @if ($medicationProgramRules->isEmpty())
            <div class="empty-state">No medication-side program items found.</div>
        @else
            <div class="protocol-guide-list">
                @foreach ($medicationProgramRules as $rule)
                    <div class="protocol-guide-item">
                        <strong>{{ $rule->action_name }}</strong><br>
                        {{ $rule->action_type_label }} · {{ $rule->requirement_level_label }} · {{ $rule->due_window_label }}
                        @if ($rule->condition_note)
                            <br><span class="text-muted">{{ $rule->condition_note }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="protocol-guide-card">
        <h4>Medication Guide / Options</h4>
        <p>Read-only reference content separated from schedule truth.</p>

        @if ($medicationGuideRows->isEmpty())
            <div class="empty-state">No medication-side guide notes are currently stored in shared rule fields.</div>
        @else
            <div class="protocol-guide-list">
                @foreach ($medicationGuideRows as $row)
                    <div class="protocol-guide-item">
                        <strong>{{ $row['label'] }}</strong><br>
                        {{ $row['content'] }}
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="protocol-guide-card">
        <h4>Vaccination Program</h4>
        <p>Read-only operational vaccine program view for this shared template.</p>

        @if ($vaccinationProgramRules->isEmpty())
            <div class="empty-state">No vaccination program items found.</div>
        @else
            <div class="protocol-guide-list">
                @foreach ($vaccinationProgramRules as $rule)
                    <div class="protocol-guide-item">
                        <strong>{{ $rule->action_name }}</strong><br>
                        {{ $rule->requirement_level_label }} · {{ $rule->due_window_label }}
                        @if ($rule->condition_note)
                            <br><span class="text-muted">{{ $rule->condition_note }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="protocol-guide-card">
        <h4>Vaccination Guide / Options</h4>
        <p>Read-only vaccine note display separated from rigid schedule truth.</p>

        @if ($vaccinationGuideRows->isEmpty())
            <div class="empty-state">No vaccination-side guide notes are currently stored in shared rule fields.</div>
        @else
            <div class="protocol-guide-list">
                @foreach ($vaccinationGuideRows as $row)
                    <div class="protocol-guide-item">
                        <strong>{{ $row['label'] }}</strong><br>
                        {{ $row['content'] }}
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="protocol-guide-card">
        <h4>Why / Explanation</h4>
        <p>Read-only explanation layer to keep caution and rationale separate from executable schedule rules.</p>

        <div class="protocol-guide-list">
            @foreach ($whyExplanationRows as $row)
                <div class="protocol-guide-item">{{ $row }}</div>
            @endforeach
        </div>
    </div>
</div>
