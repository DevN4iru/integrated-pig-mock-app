<?php

namespace App\Http\Controllers;

use App\Models\Medication;
use App\Models\Pig;
use App\Models\ProtocolExecution;
use App\Models\ProtocolRule;
use App\Models\Vaccination;
use App\Services\ProtocolEligibilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProtocolExecutionController extends Controller
{
    public function upsert(Request $request, Pig $pig)
    {
        if ($pig->isOperationallyLocked()) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', $pig->operationalLockMessage('protocol executions'));
        }

        $validated = $request->validate([
            'protocol_rule_id' => ['required', 'integer', 'exists:protocol_rules,id'],
            'scheduled_for_date' => ['required', 'date'],
            'status' => ['required', Rule::in([
                ProtocolExecution::STATUS_COMPLETED,
                ProtocolExecution::STATUS_SKIPPED,
                ProtocolExecution::STATUS_DEFERRED,
            ])],
            'executed_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],

            'actual_product_name' => ['nullable', 'string', 'max:255'],
            'actual_dose' => ['nullable', 'string', 'max:255'],
            'actual_cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $validated['notes'] = $this->normalizeNullableString($validated['notes'] ?? null);
        $validated['actual_product_name'] = $this->normalizeNullableString($validated['actual_product_name'] ?? null);
        $validated['actual_dose'] = $this->normalizeNullableString($validated['actual_dose'] ?? null);

        if (
            $validated['status'] === ProtocolExecution::STATUS_COMPLETED
            && empty($validated['executed_date'])
        ) {
            return back()
                ->withErrors(['executed_date' => 'Executed date is required when marking a protocol occurrence as completed.'])
                ->withInput();
        }

        if (
            in_array($validated['status'], [
                ProtocolExecution::STATUS_SKIPPED,
                ProtocolExecution::STATUS_DEFERRED,
            ], true)
            && empty($validated['notes'])
        ) {
            return back()
                ->withErrors(['notes' => 'Notes are required when marking a protocol occurrence as skipped or deferred.'])
                ->withInput();
        }

        $rule = ProtocolRule::with('template')->findOrFail($validated['protocol_rule_id']);

        if (!$rule->is_active || !$rule->template || !$rule->template->is_active) {
            return back()
                ->withErrors(['protocol_rule_id' => 'Selected protocol rule is not active.'])
                ->withInput();
        }

        $pig->loadMissing([
            'birthCycle:id,actual_farrow_date',
            'reproductionCyclesAsSow:id,sow_id,service_date,actual_farrow_date',
            'protocolExecutions.medication',
            'protocolExecutions.vaccination',
        ]);

        if (!(new ProtocolEligibilityService())->qualifiesForAnyClientProtocol($pig)) {
            return back()
                ->withErrors(['protocol_rule_id' => 'This pig is not eligible for a client medication program.'])
                ->withInput();
        }

        if (!$this->protocolOccurrenceBelongsToCurrentSummary($pig, $rule, $validated['scheduled_for_date'])) {
            return back()
                ->withErrors(['protocol_rule_id' => 'Selected protocol occurrence does not belong to this pig current medication program.'])
                ->withInput();
        }

        $isMedicationRule = $rule->action_type === ProtocolRule::ACTION_MEDICATION;
        $isVaccinationRule = $rule->action_type === ProtocolRule::ACTION_VACCINATION;
        $requiresDetailedAdminLog = $isMedicationRule || $isVaccinationRule;

        if (
            $validated['status'] === ProtocolExecution::STATUS_COMPLETED
            && $requiresDetailedAdminLog
        ) {
            if (empty($validated['actual_product_name'])) {
                return back()
                    ->withErrors(['actual_product_name' => 'Actual product name is required for completed medication or vaccination protocol items.'])
                    ->withInput();
            }

            if (empty($validated['actual_dose'])) {
                return back()
                    ->withErrors(['actual_dose' => 'Actual dose or dosage is required for completed medication or vaccination protocol items.'])
                    ->withInput();
            }

            if (!array_key_exists('actual_cost', $validated) || $validated['actual_cost'] === null || $validated['actual_cost'] === '') {
                return back()
                    ->withErrors(['actual_cost' => 'Actual cost is required for completed medication or vaccination protocol items.'])
                    ->withInput();
            }
        }

        $existingExecution = ProtocolExecution::with(['medication', 'vaccination'])
            ->where('pig_id', $pig->id)
            ->where('protocol_rule_id', $rule->id)
            ->where('scheduled_for_date', $validated['scheduled_for_date'])
            ->first();

        if (
            $existingExecution
            && in_array($validated['status'], [
                ProtocolExecution::STATUS_SKIPPED,
                ProtocolExecution::STATUS_DEFERRED,
            ], true)
            && ($existingExecution->medication || $existingExecution->vaccination)
        ) {
            return back()
                ->withErrors([
                    'status' => 'This protocol occurrence already has a linked detailed administration record. Keep it as completed and edit the linked record instead of changing it to skipped or deferred.',
                ])
                ->withInput();
        }

        if ($existingExecution && $isMedicationRule && $existingExecution->vaccination) {
            return back()
                ->withErrors([
                    'protocol_rule_id' => 'This medication protocol occurrence is already inconsistently linked to a vaccination record. Resolve that data conflict first.',
                ])
                ->withInput();
        }

        if ($existingExecution && $isVaccinationRule && $existingExecution->medication) {
            return back()
                ->withErrors([
                    'protocol_rule_id' => 'This vaccination protocol occurrence is already inconsistently linked to a medication record. Resolve that data conflict first.',
                ])
                ->withInput();
        }

        $execution = null;

        DB::transaction(function () use (
            $pig,
            $rule,
            $validated,
            $isMedicationRule,
            $isVaccinationRule,
            &$execution
        ): void {
            $execution = ProtocolExecution::updateOrCreate(
                [
                    'pig_id' => $pig->id,
                    'protocol_rule_id' => $rule->id,
                    'scheduled_for_date' => $validated['scheduled_for_date'],
                ],
                [
                    'status' => $validated['status'],
                    'executed_date' => $validated['status'] === ProtocolExecution::STATUS_COMPLETED
                        ? $validated['executed_date']
                        : null,
                    'notes' => $validated['notes'],
                ]
            );

            if (
                $validated['status'] === ProtocolExecution::STATUS_COMPLETED
                && ($isMedicationRule || $isVaccinationRule)
            ) {
                $this->syncDetailedAdministrationRecord($pig, $rule, $execution, $validated);
            }
        });

        $successMessage = 'Protocol occurrence updated: ' . $execution->status_label . '.';

        if ($validated['status'] === ProtocolExecution::STATUS_COMPLETED && $isMedicationRule) {
            $successMessage .= ' Linked medication record synced.';
        }

        if ($validated['status'] === ProtocolExecution::STATUS_COMPLETED && $isVaccinationRule) {
            $successMessage .= ' Linked vaccination record synced.';
        }

        return redirect()
            ->route('pigs.show', $pig->id)
            ->with('success', $successMessage);
    }

    private function protocolOccurrenceBelongsToCurrentSummary(Pig $pig, ProtocolRule $rule, string $scheduledForDate): bool
    {
        $summary = $pig->protocol_summary;

        if (!is_array($summary)) {
            return false;
        }

        foreach (['due_today', 'upcoming', 'overdue'] as $bucket) {
            foreach (($summary[$bucket] ?? []) as $row) {
                if (
                    (int) ($row['rule_id'] ?? 0) === (int) $rule->id
                    && (string) ($row['due_start'] ?? '') === (string) $scheduledForDate
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    private function syncDetailedAdministrationRecord(
        Pig $pig,
        ProtocolRule $rule,
        ProtocolExecution $execution,
        array $validated
    ): void {
        if ($rule->action_type === ProtocolRule::ACTION_MEDICATION) {
            Medication::updateOrCreate(
                [
                    'protocol_execution_id' => $execution->id,
                ],
                [
                    'pig_id' => $pig->id,
                    'medication_name' => $validated['actual_product_name'],
                    'dosage' => $validated['actual_dose'],
                    'cost' => (float) $validated['actual_cost'],
                    'administered_at' => $validated['executed_date'],
                    'notes' => $validated['notes'],
                ]
            );

            return;
        }

        if ($rule->action_type === ProtocolRule::ACTION_VACCINATION) {
            Vaccination::updateOrCreate(
                [
                    'protocol_execution_id' => $execution->id,
                ],
                [
                    'pig_id' => $pig->id,
                    'vaccine_name' => $validated['actual_product_name'],
                    'dose' => $validated['actual_dose'],
                    'cost' => (float) $validated['actual_cost'],
                    'vaccinated_at' => $validated['executed_date'],
                    'notes' => $validated['notes'],
                ]
            );
        }
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
