@extends('layouts.app')

@section('title', 'Protocol Program')
@section('page_title', 'Protocol Program')
@section('page_subtitle', 'Shared program detail for schedule truth, guide cards, and pig-facing preview.')

@section('top_actions')
    <a href="{{ route('protocol-programs.index') }}" class="btn">Back to Protocol Programs</a>
    <a href="{{ route('protocol-programs.edit', $protocolTemplate) }}" class="btn primary">Edit Content</a>
@endsection

@section('styles')
.protocol-program-stack {
    display: grid;
    gap: 20px;
}

.protocol-program-header {
    display: grid;
    grid-template-columns: 1.15fr 0.85fr;
    gap: 18px;
}

.protocol-admin-meta-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.protocol-admin-chip-row {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.protocol-admin-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: 1px solid var(--line);
    border-radius: 999px;
    background: #ffffff;
    padding: 7px 10px;
    font-size: 12px;
    font-weight: 700;
    color: var(--text);
}

.protocol-admin-chip strong {
    color: #0f172a;
}

.protocol-admin-preview-grid {
    display: grid;
    grid-template-columns: 1.1fr 0.9fr;
    gap: 18px;
}

.protocol-admin-subsection {
    display: grid;
    gap: 12px;
}

.protocol-admin-card {
    border: 1px solid var(--line);
    border-radius: 16px;
    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    padding: 14px;
}

.protocol-admin-card h4 {
    margin: 0 0 8px;
    font-size: 15px;
}

.protocol-admin-card p {
    margin: 0;
    color: var(--muted);
    font-size: 13px;
    line-height: 1.55;
}

.protocol-preview-shell {
    display: grid;
    gap: 12px;
}

.protocol-preview-top {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    align-items: flex-start;
}

.protocol-preview-title {
    font-size: 16px;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
}

.protocol-preview-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.protocol-preview-pill {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    border: 1px solid var(--line);
    background: #f8fbff;
    padding: 5px 10px;
    font-size: 11px;
    font-weight: 800;
    color: #334155;
}

.protocol-preview-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.protocol-preview-row {
    border: 1px solid var(--line);
    border-radius: 12px;
    background: #f8fbff;
    padding: 10px;
}

.protocol-preview-row strong {
    display: block;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #64748b;
    margin-bottom: 4px;
}

.protocol-guide-card-grid {
    display: grid;
    gap: 12px;
}

.protocol-guide-card {
    border: 1px solid var(--line);
    border-radius: 16px;
    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    padding: 14px;
}

.protocol-guide-card h4 {
    margin: 0 0 8px;
    font-size: 15px;
}

.protocol-guide-card p {
    margin: 0 0 10px;
    color: var(--muted);
    font-size: 13px;
    line-height: 1.55;
}

.protocol-guide-list {
    display: grid;
    gap: 8px;
}

.protocol-guide-item {
    border: 1px solid var(--line);
    border-radius: 12px;
    background: #ffffff;
    padding: 10px;
    font-size: 12px;
    line-height: 1.55;
    color: #334155;
}

.protocol-guide-item strong {
    color: #0f172a;
}

.protocol-rule-note {
    display: inline-flex;
    align-items: center;
    padding: 5px 10px;
    border-radius: 999px;
    background: #f8fbff;
    border: 1px solid var(--line);
    font-size: 11px;
    font-weight: 700;
    color: #475569;
}

@media (max-width: 1200px) {
    .protocol-program-header,
    .protocol-admin-preview-grid,
    .protocol-admin-meta-grid,
    .protocol-preview-grid {
        grid-template-columns: 1fr;
    }
}
@endsection

@section('content')
    <div class="protocol-program-stack">
        <div class="protocol-program-header">
            <div class="panel-card">
                <div class="section-title">
                    <div>
                        <h3>{{ $protocolTemplate->name }}</h3>
                        <p>{{ $protocolTemplate->description ?: 'No description recorded for this shared protocol program.' }}</p>
                    </div>
                    <span class="badge {{ $protocolTemplate->is_active ? 'green' : 'orange' }}">
                        {{ $protocolTemplate->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                <div class="protocol-admin-chip-row" style="margin-bottom: 14px;">
                    <span class="protocol-admin-chip"><strong>Code</strong> {{ $protocolTemplate->code }}</span>
                    <span class="protocol-admin-chip"><strong>Category</strong> {{ $protocolTemplate->target_type_label }}</span>
                    <span class="protocol-admin-chip"><strong>Anchor</strong> {{ $protocolTemplate->anchor_event_label }}</span>
                    <span class="protocol-admin-chip"><strong>Total Rules</strong> {{ $rules->count() }}</span>
                </div>

                <div class="flash">
                    <strong>Shared-impact warning</strong><br>
                    Editing this shared protocol program affects all pigs currently using it.
                    This page only edits display/guide content, not scheduling or execution logic.
                </div>
            </div>

            <div class="panel-card">
                <div class="section-title">
                    <div>
                        <h3>Editing Boundary</h3>
                        <p>This controlled edit pass keeps execution and scheduling protected.</p>
                    </div>
                </div>

                <div class="protocol-admin-subsection">
                    <div class="protocol-admin-card">
                        <h4>Program Rules</h4>
                        <p>Schedule truth remains locked. Sequence, offsets, action names, action types, requirements, and conditions are not editable here.</p>
                    </div>

                    <div class="protocol-admin-card">
                        <h4>Guide Cards</h4>
                        <p>Only existing DB-backed guide-note text can be edited.</p>
                    </div>

                    <div class="protocol-admin-card">
                        <h4>Pig-facing Preview</h4>
                        <p>Pig-level protocol execution remains on pig profiles only.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel-card">
            <div class="section-title">
                <div>
                    <h3>Program Rules</h3>
                    <p>Locked schedule table for this shared program.</p>
                </div>
            </div>

            @include('protocol-programs.partials.program-rules-table', [
                'rules' => $rules,
            ])
        </div>

        <div class="protocol-admin-preview-grid">
            <div class="panel-card">
                <div class="section-title">
                    <div>
                        <h3>Guide Cards</h3>
                        <p>Shared guide content grouped for pig-facing display.</p>
                    </div>
                </div>

                @include('protocol-programs.partials.guide-cards-display', [
                    'protocolTemplate' => $protocolTemplate,
                    'medicationProgramRules' => $medicationProgramRules,
                    'vaccinationProgramRules' => $vaccinationProgramRules,
                    'medicationGuideRows' => $medicationGuideRows,
                    'vaccinationGuideRows' => $vaccinationGuideRows,
                    'whyExplanationRows' => $whyExplanationRows,
                ])
            </div>

            <div class="panel-card">
                <div class="section-title">
                    <div>
                        <h3>Pig-facing Preview</h3>
                        <p>Preview-only rendering of how this shared program should be consumed on pig profiles.</p>
                    </div>
                </div>

                @include('protocol-programs.partials.pig-facing-preview', [
                    'protocolTemplate' => $protocolTemplate,
                    'previewRules' => $previewRules,
                ])
            </div>
        </div>
    </div>
@endsection
