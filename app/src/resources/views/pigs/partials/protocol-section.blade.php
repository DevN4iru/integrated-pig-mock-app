<div class="panel-card">
    <div class="section-title">
        <div>
            <h3>Protocol Schedule</h3>
            <p>Live schedule, visible guide cards, and execution entry for this pig’s assigned shared program.</p>
        </div>
        @if ($protocolTemplateCode)
            <span class="badge blue">{{ $protocolTemplateCode }}</span>
        @endif
    </div>

    @if (!$protocol)
        <div class="empty-state">No protocol template currently applies to this pig.</div>
    @else
        <div class="protocol-shell">
            <div class="protocol-header-card">
                <div class="protocol-header-grid">
                    <div class="protocol-program-meta">
                        <div class="protocol-program-meta-row">
                            <span class="protocol-program-chip">
                                <strong>Program</strong> {{ $protocol['template_name'] ?? $protocolTemplateCode }}
                            </span>

                            @if (!empty($protocol['target_type']))
                                <span class="protocol-program-chip">
                                    <strong>Category</strong> {{ ucfirst(str_replace('_', ' ', (string) $protocol['target_type'])) }}
                                </span>
                            @endif

                            @if (!empty($protocol['anchor_event']))
                                <span class="protocol-program-chip">
                                    <strong>Anchor</strong> {{ ucfirst(str_replace('_', ' ', (string) $protocol['anchor_event'])) }}
                                </span>
                            @endif

                            <span class="protocol-program-chip">
                                <strong>Anchor Date</strong> {{ $protocolAnchorDate ?? 'Not available' }}
                            </span>
                        </div>

                        <div class="protocol-shared-note">
                            This pig follows a <strong>shared protocol program</strong>. Schedule logic and guide content come from the assigned program. Editing shared program rules belongs in a future <strong>Protocol Programs</strong> admin surface, not on this pig profile.
                        </div>

                        @if ($protocolHasFormErrors)
                            <div class="flash error">
                                @if ($errors->has('status'))
                                    {{ $errors->first('status') }}
                                @elseif ($errors->has('executed_date'))
                                    {{ $errors->first('executed_date') }}
                                @elseif ($errors->has('actual_product_name'))
                                    {{ $errors->first('actual_product_name') }}
                                @elseif ($errors->has('actual_dose'))
                                    {{ $errors->first('actual_dose') }}
                                @elseif ($errors->has('actual_cost'))
                                    {{ $errors->first('actual_cost') }}
                                @elseif ($errors->has('notes'))
                                    {{ $errors->first('notes') }}
                                @else
                                    {{ $errors->first('protocol_rule_id') }}
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="protocol-summary-strip">
                        <div class="protocol-summary-stat">
                            <label>Due Today</label>
                            <div class="protocol-summary-value">{{ $protocolDueToday->count() }}</div>
                        </div>

                        <div class="protocol-summary-stat">
                            <label>Upcoming</label>
                            <div class="protocol-summary-value">{{ $protocolUpcoming->count() }}</div>
                        </div>

                        <div class="protocol-summary-stat">
                            <label>Overdue</label>
                            <div class="protocol-summary-value">{{ $protocolOverdue->count() }}</div>
                        </div>

                        <div class="protocol-summary-stat">
                            <label>History</label>
                            <div class="protocol-summary-value">{{ collect($pig->protocol_execution_history ?? [])->count() }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="protocol-main-grid">
                <div class="protocol-buckets">
                    @foreach ($protocolBuckets as $bucket)
                        <div class="protocol-bucket-card {{ $bucket['class'] }}">
                            <div class="protocol-bucket-head">
                                <div>
                                    <h4>{{ $bucket['title'] }}</h4>
                                    <p class="section-subtle">{{ $bucket['items']->count() }} item(s)</p>
                                </div>

                                <span class="badge {{ $bucket['badge'] }}">{{ $bucket['items']->count() }}</span>
                            </div>

                            <div class="protocol-bucket-note">{{ $bucket['note'] }}</div>

                            @if ($bucket['items']->isEmpty())
                                <div class="protocol-empty">No items in this bucket.</div>
                            @else
                                <div class="protocol-bucket-list">
                                    @foreach ($bucket['items'] as $item)
                                        @include('pigs.partials.protocol-occurrence-card', [
                                            'pig' => $pig,
                                            'item' => $item,
                                            'isOperationalLocked' => $isOperationalLocked,
                                            'protocolHasFormErrors' => $protocolHasFormErrors,
                                        ])
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                @include('pigs.partials.protocol-guide-cards', [
                    'protocolTemplateCode' => $protocolTemplateCode,
                    'protocolMedicationProgramItems' => $protocolMedicationProgramItems,
                    'protocolVaccinationProgramItems' => $protocolVaccinationProgramItems,
                ])
            </div>
        </div>
    @endif
</div>
