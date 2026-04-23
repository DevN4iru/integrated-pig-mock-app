@php
    use App\Models\ProtocolExecution;

    $executionStatus = $item['execution_status'] ?? null;
    $executionStatusLabel = $executionStatus ? ucfirst($executionStatus) : 'Pending';
    $executionStatusClass = $executionStatus ?: 'pending';

    $type = (string) ($item['type'] ?? '');
    $requirement = (string) ($item['requirement'] ?? '');
    $conditionNote = trim((string) ($item['condition_note'] ?? ''));
    $productNote = trim((string) ($item['product_note'] ?? ''));
    $dosageNote = trim((string) ($item['dosage_note'] ?? ''));
    $administrationNote = trim((string) ($item['administration_note'] ?? ''));
    $marketNote = trim((string) ($item['market_note'] ?? ''));
    $executionNotes = trim((string) ($item['execution_notes'] ?? ''));

    $isMedicationProtocol = $type === 'medication';
    $isVaccinationProtocol = $type === 'vaccination';
    $isDetailedAdminType = $isMedicationProtocol || $isVaccinationProtocol;

    $hasGuideDetails = $productNote !== ''
        || $dosageNote !== ''
        || $administrationNote !== ''
        || $marketNote !== ''
        || $conditionNote !== '';

    $guideTeaser = match (true) {
        $type === 'vaccination' && $hasGuideDetails => 'Guide available: product options and timing notes.',
        $type === 'medication' && $hasGuideDetails => 'Guide available: product options and use notes.',
        $type === 'supplement' && $hasGuideDetails => 'Guide available: support-product notes.',
        $type === 'procedure' && $conditionNote !== '' => 'Guide available: condition-specific procedure note.',
        $hasGuideDetails => 'Guide available for this schedule item.',
        default => null,
    };

    $hasActualCompletedRecord = (bool) ($item['has_linked_admin_log'] ?? false)
        || ($item['actual_cost'] !== null)
        || !empty($item['actual_notes']);

    $showActualProduct = $hasActualCompletedRecord && !empty($item['actual_product_name']);
    $showActualDose = $hasActualCompletedRecord && !empty($item['actual_dose']);
    $showActualCost = $hasActualCompletedRecord && $item['actual_cost'] !== null;
    $showActualNotes = $hasActualCompletedRecord && !empty($item['actual_notes']);

    $isCurrentOldForm = (string) old('protocol_rule_id') === (string) ($item['rule_id'] ?? '')
        && (string) old('scheduled_for_date') === (string) ($item['due_start'] ?? '');

    $defaultStatus = $executionStatus ?: ProtocolExecution::STATUS_COMPLETED;
    $prefillStatus = $isCurrentOldForm
        ? old('status', $defaultStatus)
        : $defaultStatus;

    $prefillExecutedDate = $isCurrentOldForm
        ? old('executed_date', $item['executed_date'] ?? '')
        : ($item['executed_date'] ?? '');

    $prefillNotes = $isCurrentOldForm
        ? old('notes', $executionNotes)
        : $executionNotes;

    $prefillActualProductName = $isCurrentOldForm
        ? old('actual_product_name', $item['actual_product_name'] ?? '')
        : ($item['actual_product_name'] ?? '');

    $prefillActualDose = $isCurrentOldForm
        ? old('actual_dose', $item['actual_dose'] ?? '')
        : ($item['actual_dose'] ?? '');

    $prefillActualCost = $isCurrentOldForm
        ? old('actual_cost', $item['actual_cost'] ?? '')
        : ($item['actual_cost'] ?? '');

    $actualProductLabel = $isVaccinationProtocol ? 'Actual Vaccine Used' : 'Actual Product Used';
    $actualDoseLabel = $isVaccinationProtocol ? 'Actual Dose' : 'Actual Dosage';

    $formShouldOpen = $isCurrentOldForm && $protocolHasFormErrors;
@endphp

<div class="protocol-card">
    <div class="protocol-card-top">
        <div>
            <h5 class="protocol-card-title">{{ $item['action'] }}</h5>
        </div>

        <div class="protocol-card-badges">
            @if ($type !== '')
                <span class="protocol-pill">{{ ucfirst(str_replace('_', ' ', $type)) }}</span>
            @endif

            @if ($requirement !== '')
                <span class="protocol-pill">{{ ucfirst($requirement) }}</span>
            @endif

            <span class="badge protocol-status-badge {{ $executionStatusClass }}">
                {{ $executionStatusLabel }}
            </span>
        </div>
    </div>

    <div class="protocol-row-grid">
        <div class="protocol-row">
            <div class="protocol-row-label">Due Window</div>
            <div class="protocol-row-value">
                {{ $item['due_start'] ?? '—' }}
                @if (!empty($item['due_end']) && $item['due_end'] !== $item['due_start'])
                    to {{ $item['due_end'] }}
                @endif
            </div>
        </div>

        @if ($conditionNote !== '')
            <div class="protocol-row">
                <div class="protocol-row-label">Condition</div>
                <div class="protocol-row-value">{{ $conditionNote }}</div>
            </div>
        @endif

        <div class="protocol-row">
            <div class="protocol-row-label">Status</div>
            <div class="protocol-row-value">
                {{ $executionStatusLabel }}
                @if (!empty($item['executed_date']))
                    · Executed {{ $item['executed_date'] }}
                @endif
            </div>
        </div>

        @if ($executionNotes !== '' && in_array($executionStatus, [ProtocolExecution::STATUS_SKIPPED, ProtocolExecution::STATUS_DEFERRED], true))
            <div class="protocol-row">
                <div class="protocol-row-label">Current Note</div>
                <div class="protocol-row-value">{{ $executionNotes }}</div>
            </div>
        @endif
    </div>

    @if ($guideTeaser)
        <div class="protocol-guide-teaser">
            {{ $guideTeaser }}
        </div>
    @endif

    @if ($hasActualCompletedRecord)
        <div class="protocol-actual-block">
            <div class="protocol-actual-title">Actual Completed Record</div>

            <div class="protocol-actual-grid">
                @if ($showActualProduct)
                    <div class="protocol-row">
                        <div class="protocol-row-label">{{ $isVaccinationProtocol ? 'Actual Vaccine' : 'Actual Product' }}</div>
                        <div class="protocol-row-value">{{ $item['actual_product_name'] }}</div>
                    </div>
                @endif

                @if ($showActualDose)
                    <div class="protocol-row">
                        <div class="protocol-row-label">{{ $isVaccinationProtocol ? 'Actual Dose' : 'Actual Dosage' }}</div>
                        <div class="protocol-row-value">{{ $item['actual_dose'] }}</div>
                    </div>
                @endif

                @if ($showActualCost)
                    <div class="protocol-row">
                        <div class="protocol-row-label">Actual Cost</div>
                        <div class="protocol-row-value">₱ {{ number_format((float) $item['actual_cost'], 2) }}</div>
                    </div>
                @endif

                @if ($showActualNotes)
                    <div class="protocol-row">
                        <div class="protocol-row-label">Actual Notes</div>
                        <div class="protocol-row-value">{{ $item['actual_notes'] }}</div>
                    </div>
                @endif

                @if (!empty($item['has_linked_admin_log']))
                    <div class="protocol-row">
                        <div class="protocol-row-label">Linked Detailed Record</div>
                        <div class="protocol-row-value">Yes</div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if ($hasGuideDetails || $executionNotes !== '')
        <details class="protocol-collapsible">
            <summary>Guide & Details</summary>

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

                    @if ($executionNotes !== '' && !in_array($executionStatus, [ProtocolExecution::STATUS_SKIPPED, ProtocolExecution::STATUS_DEFERRED], true))
                        <div class="protocol-guide-detail-row">
                            <strong>Protocol Notes</strong>
                            {{ $executionNotes }}
                        </div>
                    @endif
                </div>
            </div>
        </details>
    @endif

    @if (!$isOperationalLocked)
        <details class="protocol-collapsible" data-protocol-form-panel {{ $formShouldOpen ? 'open' : '' }}>
            <summary>Record / Update</summary>

            <div class="protocol-collapsible-body">
                <form method="POST" action="{{ route('protocol-executions.upsert', $pig) }}" class="protocol-form-shell">
                    @csrf
                    <input type="hidden" name="protocol_rule_id" value="{{ $item['rule_id'] }}">
                    <input type="hidden" name="scheduled_for_date" value="{{ $item['due_start'] }}">

                    @if ($formShouldOpen)
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

                    @if ($productNote !== '' || $dosageNote !== '' || $administrationNote !== '')
                        <div class="protocol-helper-text">
                            @if ($productNote !== '')
                                <div><strong>Recommended:</strong> {{ $productNote }}</div>
                            @endif
                            @if ($dosageNote !== '')
                                <div><strong>Dosage note:</strong> {{ $dosageNote }}</div>
                            @endif
                            @if ($administrationNote !== '')
                                <div><strong>Administration note:</strong> {{ $administrationNote }}</div>
                            @endif
                        </div>
                    @endif

                    <div class="protocol-form-grid">
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status">
                                <option value="{{ ProtocolExecution::STATUS_COMPLETED }}" {{ $prefillStatus === ProtocolExecution::STATUS_COMPLETED ? 'selected' : '' }}>Completed</option>
                                <option value="{{ ProtocolExecution::STATUS_SKIPPED }}" {{ $prefillStatus === ProtocolExecution::STATUS_SKIPPED ? 'selected' : '' }}>Skipped</option>
                                <option value="{{ ProtocolExecution::STATUS_DEFERRED }}" {{ $prefillStatus === ProtocolExecution::STATUS_DEFERRED ? 'selected' : '' }}>Deferred</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Executed Date</label>
                            <input type="date" name="executed_date" value="{{ $prefillExecutedDate }}">
                        </div>
                    </div>

                    @if ($isDetailedAdminType)
                        <div class="protocol-form-grid">
                            <div class="form-group">
                                <label>{{ $actualProductLabel }}</label>
                                <input type="text" name="actual_product_name" value="{{ $prefillActualProductName }}">
                            </div>

                            <div class="form-group">
                                <label>{{ $actualDoseLabel }}</label>
                                <input type="text" name="actual_dose" value="{{ $prefillActualDose }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Actual Cost</label>
                            <input type="number" name="actual_cost" step="0.01" min="0" value="{{ $prefillActualCost }}">
                        </div>
                    @endif

                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes" rows="2" placeholder="{{ $isDetailedAdminType ? 'Optional for completed. Required for skipped or deferred.' : 'Required for skipped or deferred.' }}">{{ $prefillNotes }}</textarea>
                    </div>

                    <div class="protocol-form-submit">
                        <button type="submit" class="btn primary">Save Execution</button>
                    </div>
                </form>
            </div>
        </details>
    @else
        <div class="flash error">
            Protocol execution updates are locked for this pig.
        </div>
    @endif
</div>
