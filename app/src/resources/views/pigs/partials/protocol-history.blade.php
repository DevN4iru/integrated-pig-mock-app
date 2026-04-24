@php
    use App\Models\ProtocolExecution;
@endphp

<div class="panel-card">
    <div class="section-title">
        <div>
            <h3>Protocol Execution History</h3>
            <p>Recorded execution outcomes for this pig, simplified to historical truth and linked actual records.</p>
        </div>
        <span class="badge blue">{{ $protocolExecutionHistory->count() }}</span>
    </div>

    @if($protocolExecutionHistory->isEmpty())
        <div class="empty-state">No protocol execution history yet.</div>
    @else
        <div class="protocol-history-shell">
            @foreach($protocolExecutionHistory as $entry)
                @php
                    $historyStatusClass = $entry['status'] ?? 'pending';
                    $isMedicationHistory = ($entry['type'] ?? null) === 'medication';
                    $isVaccinationHistory = ($entry['type'] ?? null) === 'vaccination';
                    $isDetailedHistory = $isMedicationHistory || $isVaccinationHistory;

                    $productNote = trim((string) ($entry['product_note'] ?? ''));
                    $dosageNote = trim((string) ($entry['dosage_note'] ?? ''));
                    $administrationNote = trim((string) ($entry['administration_note'] ?? ''));
                    $marketNote = trim((string) ($entry['market_note'] ?? ''));
                    $conditionNote = trim((string) ($entry['condition_note'] ?? ''));

                    $hasReferenceDetails = $productNote !== ''
                        || $dosageNote !== ''
                        || $administrationNote !== ''
                        || $marketNote !== ''
                        || $conditionNote !== '';

                    $hasActualHistory = !empty($entry['actual_product_name'])
                        || !empty($entry['actual_dose'])
                        || $entry['actual_cost'] !== null
                        || !empty($entry['actual_notes']);
                @endphp

                <div class="protocol-history-record">
                    <div class="protocol-history-top">
                        <div>
                            <h5 class="protocol-history-title">{{ $entry['action'] ?: 'Protocol Occurrence' }}</h5>
                        </div>

                        <span class="badge protocol-status-badge {{ $historyStatusClass }}">
                            {{ $entry['status_label'] ?? ucfirst((string) ($entry['status'] ?? 'pending')) }}
                        </span>
                    </div>

                    <div class="protocol-history-grid">
                        @if (!empty($entry['type']))
                            <div class="protocol-row">
                                <div class="protocol-row-label">Type</div>
                                <div class="protocol-row-value">{{ ucfirst(str_replace('_', ' ', (string) $entry['type'])) }}</div>
                            </div>
                        @endif

                        @if (!empty($entry['requirement']))
                            <div class="protocol-row">
                                <div class="protocol-row-label">Requirement</div>
                                <div class="protocol-row-value">{{ ucfirst((string) $entry['requirement']) }}</div>
                            </div>
                        @endif

                        @if (!empty($entry['scheduled_for_date']))
                            <div class="protocol-row">
                                <div class="protocol-row-label">Scheduled For</div>
                                <div class="protocol-row-value">{{ $entry['scheduled_for_date'] }}</div>
                            </div>
                        @endif

                        @if (!empty($entry['executed_date']))
                            <div class="protocol-row">
                                <div class="protocol-row-label">Executed Date</div>
                                <div class="protocol-row-value">{{ $entry['executed_date'] }}</div>
                            </div>
                        @endif

                        @if (!empty($entry['notes']))
                            <div class="protocol-row">
                                <div class="protocol-row-label">Protocol Notes</div>
                                <div class="protocol-row-value">{{ $entry['notes'] }}</div>
                            </div>
                        @endif

                        @if (!empty($entry['has_linked_admin_log']) && !$hasActualHistory)
                            <div class="protocol-row">
                                <div class="protocol-row-label">Linked Detailed Record</div>
                                <div class="protocol-row-value">Yes</div>
                            </div>
                        @endif
                    </div>

                    @if ($hasActualHistory)
                        <div class="protocol-actual-block">
                            <div class="protocol-actual-title">Actual Completed Record</div>

                            <div class="protocol-actual-grid">
                                @if (!empty($entry['actual_product_name']))
                                    <div class="protocol-row">
                                        <div class="protocol-row-label">{{ $isVaccinationHistory ? 'Actual Vaccine' : 'Actual Product' }}</div>
                                        <div class="protocol-row-value">{{ $entry['actual_product_name'] }}</div>
                                    </div>
                                @endif

                                @if (!empty($entry['actual_dose']))
                                    <div class="protocol-row">
                                        <div class="protocol-row-label">{{ $isVaccinationHistory ? 'Actual Dose' : 'Actual Dosage' }}</div>
                                        <div class="protocol-row-value">{{ $entry['actual_dose'] }}</div>
                                    </div>
                                @endif

                                @if ($entry['actual_cost'] !== null)
                                    <div class="protocol-row">
                                        <div class="protocol-row-label">Actual Cost</div>
                                        <div class="protocol-row-value">₱ {{ number_format((float) $entry['actual_cost'], 2) }}</div>
                                    </div>
                                @endif

                                @if (!empty($entry['actual_notes']))
                                    <div class="protocol-row">
                                        <div class="protocol-row-label">Actual Notes</div>
                                        <div class="protocol-row-value">{{ $entry['actual_notes'] }}</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if ($hasReferenceDetails)
                        <details class="protocol-collapsible">
                            <summary>Reference Details</summary>

                            <div class="protocol-collapsible-body">
                                <div class="protocol-guide-detail-grid">
                                    @if ($productNote !== '')
                                        <div class="protocol-guide-detail-row">
                                            <strong>Recommended Product</strong>
                                            {{ $productNote }}
                                        </div>
                                    @endif

                                    @if ($dosageNote !== '')
                                        <div class="protocol-guide-detail-row">
                                            <strong>Recommended Dosage</strong>
                                            {{ $dosageNote }}
                                        </div>
                                    @endif

                                    @if ($administrationNote !== '')
                                        <div class="protocol-guide-detail-row">
                                            <strong>Administration Note</strong>
                                            {{ $administrationNote }}
                                        </div>
                                    @endif

                                    @if ($marketNote !== '')
                                        <div class="protocol-guide-detail-row">
                                            <strong>Options / Alternatives</strong>
                                            {{ $marketNote }}
                                        </div>
                                    @endif

                                    @if ($conditionNote !== '')
                                        <div class="protocol-guide-detail-row">
                                            <strong>Condition</strong>
                                            {{ $conditionNote }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </details>
                    @endif

                    @if (($entry['status'] ?? null) === ProtocolExecution::STATUS_DEFERRED)
                        <div class="protocol-history-active-note">
                            This occurrence is deferred and remains unresolved, so it can still appear in the active schedule buckets.
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
