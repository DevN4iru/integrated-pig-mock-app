@if ($rules->isEmpty())
    <div class="empty-state">No rules found for this protocol program.</div>
@else
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Seq</th>
                    <th>Due Window</th>
                    <th>Action</th>
                    <th>Type</th>
                    <th>Requirement</th>
                    <th>Condition</th>
                    <th>Guide Notes Present</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rules as $rule)
                    @php
                        $noteCount = collect([
                            $rule->product_note,
                            $rule->dosage_note,
                            $rule->administration_note,
                            $rule->market_note,
                            $rule->condition_note,
                        ])->filter(fn ($value) => filled($value))->count();
                    @endphp

                    <tr>
                        <td>{{ $rule->sequence_order }}</td>
                        <td>{{ $rule->due_window_label }}</td>
                        <td>
                            <strong>{{ $rule->action_name }}</strong>
                        </td>
                        <td>{{ $rule->action_type_label }}</td>
                        <td>{{ $rule->requirement_level_label }}</td>
                        <td>{{ $rule->condition_key_label ?? '—' }}</td>
                        <td>
                            @if ($noteCount > 0)
                                <span class="protocol-rule-note">{{ $noteCount }} note field(s)</span>
                            @else
                                <span class="text-muted">None</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $rule->is_active ? 'green' : 'orange' }}">
                                {{ $rule->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
