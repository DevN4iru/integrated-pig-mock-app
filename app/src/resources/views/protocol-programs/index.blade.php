@extends('layouts.app')

@section('title', 'Protocol Programs')
@section('page_title', 'Protocol Programs')
@section('page_subtitle', 'Read-only shared program registry for category-wide protocol schedules and guide content.')

@section('top_actions')
    <a href="{{ route('dashboard') }}" class="btn">Back to Dashboard</a>
@endsection

@section('content')
    <div class="grid">
        <div class="panel-card">
            <div class="section-title">
                <div>
                    <h3>Shared Programs</h3>
                    <p>These are shared category-level protocol programs. Pig profiles consume these programs for display and execution, but do not edit them directly in this phase.</p>
                </div>
            </div>

            <div class="flash" style="margin-bottom: 16px;">
                <strong>Admin guardrail</strong><br>
                This area is read-only in Phase 1. Shared program editing is intentionally locked because any future change here will affect all pigs using the selected program.
            </div>

            @if ($programs->isEmpty())
                <div class="empty-state">No protocol programs found.</div>
            @else
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Program</th>
                                <th>Code</th>
                                <th>Category</th>
                                <th>Anchor</th>
                                <th>Rules</th>
                                <th>Active Rules</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($programs as $program)
                                <tr>
                                    <td>
                                        <strong>{{ $program->name }}</strong><br>
                                        <span class="text-muted">{{ $program->description ?: 'No description.' }}</span>
                                    </td>
                                    <td>{{ $program->code }}</td>
                                    <td>{{ $program->target_type_label }}</td>
                                    <td>{{ $program->anchor_event_label }}</td>
                                    <td>{{ $program->rules_count }}</td>
                                    <td>{{ $program->active_rules_count }}</td>
                                    <td>
                                        <span class="badge {{ $program->is_active ? 'green' : 'orange' }}">
                                            {{ $program->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('protocol-programs.show', $program) }}" class="btn primary">Open Program</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
