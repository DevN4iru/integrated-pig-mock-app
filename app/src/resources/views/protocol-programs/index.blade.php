@extends('layouts.app')

@section('title', 'Protocol Programs')
@section('page_title', 'Protocol Programs')
@section('page_subtitle', 'Shared program registry for category-wide protocol schedules and guide content.')

@section('top_actions')
    <a href="{{ route('dashboard') }}" class="btn">Back to Dashboard</a>
@endsection

@section('content')
    <div class="grid">
        <div class="panel-card">
            <div class="section-title">
                <div>
                    <h3>Shared Programs</h3>
                    <p>These are shared category-level protocol programs. Pig profiles consume these programs for display and execution.</p>
                </div>
            </div>

            <div class="flash" style="margin-bottom: 16px;">
                <strong>Shared-impact warning</strong><br>
                Editing this shared protocol program affects all pigs currently using it.
                This area only edits display/guide content, not scheduling or execution logic.
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
                                        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                            <a href="{{ route('protocol-programs.show', $program) }}" class="btn primary">Open Program</a>
                                            <a href="{{ route('protocol-programs.edit', $program) }}" class="btn">Edit Content</a>
                                        </div>
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
