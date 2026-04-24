@extends('layouts.app')

@section('title', 'Edit Protocol Program Content')
@section('page_title', 'Edit Protocol Program Content')
@section('page_subtitle', 'Controlled shared-content editing only. Scheduling and execution logic stay locked.')

@section('top_actions')
    <a href="{{ route('protocol-programs.show', $protocolTemplate) }}" class="btn">Back to Program</a>
    <a href="{{ route('protocol-programs.index') }}" class="btn">All Protocol Programs</a>
@endsection

@section('styles')
.protocol-edit-stack {
    display: grid;
    gap: 20px;
}

.protocol-edit-warning {
    border: 1px solid #fed7aa;
    background: #fff7ed;
    color: #9a3412;
}

.protocol-edit-warning strong {
    color: #7c2d12;
}

.protocol-edit-locked-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 10px;
}

.protocol-edit-locked-item {
    border: 1px solid var(--line);
    border-radius: 14px;
    background: #f8fbff;
    padding: 12px;
}

.protocol-edit-locked-item strong {
    display: block;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #64748b;
    margin-bottom: 4px;
}

.protocol-edit-rule-card {
    border: 1px solid var(--line);
    border-radius: 18px;
    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    padding: 16px;
    display: grid;
    gap: 14px;
}

.protocol-edit-rule-header {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    align-items: flex-start;
}

.protocol-edit-rule-title h4 {
    margin: 0 0 4px;
    font-size: 16px;
}

.protocol-edit-rule-title p {
    margin: 0;
    color: var(--muted);
    font-size: 13px;
    line-height: 1.45;
}

.protocol-edit-locked-pill-row {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.protocol-edit-locked-pill {
    display: inline-flex;
    border: 1px solid var(--line);
    background: #ffffff;
    border-radius: 999px;
    padding: 6px 10px;
    font-size: 11px;
    font-weight: 800;
    color: #475569;
}

.protocol-edit-notes-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

.protocol-edit-rule-card textarea {
    min-height: 92px;
}

@media (max-width: 1200px) {
    .protocol-edit-locked-grid,
    .protocol-edit-notes-grid {
        grid-template-columns: 1fr;
    }

    .protocol-edit-rule-header {
        display: grid;
    }
}
@endsection

@section('content')
    <form method="POST" action="{{ route('protocol-programs.update', $protocolTemplate) }}">
        @csrf
        @method('PUT')

        <div class="protocol-edit-stack">
            <div class="flash protocol-edit-warning">
                <strong>Shared-impact warning</strong><br>
                Editing this shared protocol program affects all pigs currently using it.
                This page only edits display/guide content, not scheduling or execution logic.
            </div>

            <div class="panel-card">
                <div class="section-title">
                    <div>
                        <h3>Program Display Content</h3>
                        <p>Only the program name and description are editable here. Routing still uses the locked program code.</p>
                    </div>
                    <span class="badge {{ $protocolTemplate->is_active ? 'green' : 'orange' }}">
                        {{ $protocolTemplate->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                <div class="protocol-edit-locked-grid" style="margin-bottom: 16px;">
                    <div class="protocol-edit-locked-item">
                        <strong>Locked Code</strong>
                        {{ $protocolTemplate->code }}
                    </div>

                    <div class="protocol-edit-locked-item">
                        <strong>Locked Category</strong>
                        {{ $protocolTemplate->target_type_label }}
                    </div>

                    <div class="protocol-edit-locked-item">
                        <strong>Locked Anchor</strong>
                        {{ $protocolTemplate->anchor_event_label }}
                    </div>

                    <div class="protocol-edit-locked-item">
                        <strong>Locked Rules</strong>
                        {{ $rules->count() }} total
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Program Name</label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name', $protocolTemplate->name) }}"
                            required
                            maxlength="255"
                        >
                    </div>

                    <div class="form-group full">
                        <label for="description">Program Description</label>
                        <textarea
                            id="description"
                            name="description"
                            rows="4"
                        >{{ old('description', $protocolTemplate->description) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="panel-card">
                <div class="section-title">
                    <div>
                        <h3>Rule Guide Notes</h3>
                        <p>Only existing DB-backed guide text fields are editable. Schedule and execution fields are displayed as locked reference only.</p>
                    </div>
                </div>

                @if ($rules->isEmpty())
                    <div class="empty-state">No rules found for this protocol program.</div>
                @else
                    <div class="protocol-edit-stack">
                        @foreach ($rules as $index => $rule)
                            @php
                                $oldRulePath = 'rules.' . $index . '.';
                            @endphp

                            <div class="protocol-edit-rule-card">
                                <input
                                    type="hidden"
                                    name="rules[{{ $index }}][id]"
                                    value="{{ $rule->id }}"
                                >

                                <div class="protocol-edit-rule-header">
                                    <div class="protocol-edit-rule-title">
                                        <h4>{{ $rule->action_name }}</h4>
                                        <p>
                                            Schedule, type, requirement, condition key, and active state are locked in this phase.
                                        </p>
                                    </div>

                                    <span class="badge {{ $rule->is_active ? 'green' : 'orange' }}">
                                        {{ $rule->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>

                                <div class="protocol-edit-locked-pill-row">
                                    <span class="protocol-edit-locked-pill">Seq {{ $rule->sequence_order }}</span>
                                    <span class="protocol-edit-locked-pill">{{ $rule->due_window_label }}</span>
                                    <span class="protocol-edit-locked-pill">{{ $rule->action_type_label }}</span>
                                    <span class="protocol-edit-locked-pill">{{ $rule->requirement_level_label }}</span>

                                    @if ($rule->condition_key_label)
                                        <span class="protocol-edit-locked-pill">{{ $rule->condition_key_label }}</span>
                                    @endif
                                </div>

                                <div class="protocol-edit-notes-grid">
                                    <div class="form-group">
                                        <label for="rules_{{ $rule->id }}_product_note">Product / Option Note</label>
                                        <textarea
                                            id="rules_{{ $rule->id }}_product_note"
                                            name="rules[{{ $index }}][product_note]"
                                            rows="3"
                                        >{{ old($oldRulePath . 'product_note', $rule->product_note) }}</textarea>
                                    </div>

                                    <div class="form-group">
                                        <label for="rules_{{ $rule->id }}_dosage_note">Dosage Note</label>
                                        <textarea
                                            id="rules_{{ $rule->id }}_dosage_note"
                                            name="rules[{{ $index }}][dosage_note]"
                                            rows="3"
                                        >{{ old($oldRulePath . 'dosage_note', $rule->dosage_note) }}</textarea>
                                    </div>

                                    <div class="form-group">
                                        <label for="rules_{{ $rule->id }}_administration_note">Administration Note</label>
                                        <textarea
                                            id="rules_{{ $rule->id }}_administration_note"
                                            name="rules[{{ $index }}][administration_note]"
                                            rows="3"
                                        >{{ old($oldRulePath . 'administration_note', $rule->administration_note) }}</textarea>
                                    </div>

                                    <div class="form-group">
                                        <label for="rules_{{ $rule->id }}_market_note">Alternative / Market Note</label>
                                        <textarea
                                            id="rules_{{ $rule->id }}_market_note"
                                            name="rules[{{ $index }}][market_note]"
                                            rows="3"
                                        >{{ old($oldRulePath . 'market_note', $rule->market_note) }}</textarea>
                                    </div>

                                    <div class="form-group full">
                                        <label for="rules_{{ $rule->id }}_condition_note">Condition Note</label>
                                        <textarea
                                            id="rules_{{ $rule->id }}_condition_note"
                                            name="rules[{{ $index }}][condition_note]"
                                            rows="3"
                                        >{{ old($oldRulePath . 'condition_note', $rule->condition_note) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="form-actions">
                    <button type="submit" class="btn primary">Save Display / Guide Content</button>
                    <a href="{{ route('protocol-programs.show', $protocolTemplate) }}" class="btn">Cancel</a>
                </div>
            </div>
        </div>
    </form>
@endsection
