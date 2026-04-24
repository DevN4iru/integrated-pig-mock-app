<div class="protocol-preview-shell">
    <div class="protocol-admin-card">
        <h4>Shared Program Header Preview</h4>
        <p>This preview shows the intended pig-facing structure only. It is not a live pig execution screen.</p>
    </div>

    <div class="protocol-admin-card">
        <div class="protocol-preview-top">
            <div>
                <h5 class="protocol-preview-title">{{ $protocolTemplate->name }}</h5>
            </div>

            <div class="protocol-preview-badges">
                <span class="protocol-preview-pill">{{ $protocolTemplate->target_type_label }}</span>
                <span class="protocol-preview-pill">{{ $protocolTemplate->anchor_event_label }}</span>
                <span class="protocol-preview-pill">{{ $previewRules->count() }} sample rule(s)</span>
            </div>
        </div>
    </div>

    @if ($previewRules->isEmpty())
        <div class="empty-state">No preview rules are available for this program.</div>
    @else
        @foreach ($previewRules as $rule)
            <div class="protocol-admin-card">
                <div class="protocol-preview-top">
                    <div>
                        <h5 class="protocol-preview-title">{{ $rule->action_name }}</h5>
                    </div>

                    <div class="protocol-preview-badges">
                        <span class="protocol-preview-pill">{{ $rule->action_type_label }}</span>
                        <span class="protocol-preview-pill">{{ $rule->requirement_level_label }}</span>
                        <span class="protocol-preview-pill">Preview</span>
                    </div>
                </div>

                <div class="protocol-preview-grid">
                    <div class="protocol-preview-row">
                        <strong>Due Window</strong>
                        {{ $rule->due_window_label }}
                    </div>

                    <div class="protocol-preview-row">
                        <strong>Status</strong>
                        Pending preview
                    </div>

                    @if ($rule->condition_note)
                        <div class="protocol-preview-row">
                            <strong>Condition</strong>
                            {{ $rule->condition_note }}
                        </div>
                    @endif

                    @if ($rule->product_note)
                        <div class="protocol-preview-row">
                            <strong>Guide Teaser</strong>
                            {{ $rule->product_note }}
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    @endif
</div>
