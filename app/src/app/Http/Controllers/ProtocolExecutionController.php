<?php

namespace App\Http\Controllers;

use App\Models\Pig;
use App\Models\ProtocolExecution;
use App\Models\ProtocolRule;
use Illuminate\Http\Request;
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
        ]);

        $validated['notes'] = isset($validated['notes'])
            ? trim((string) $validated['notes'])
            : null;

        $validated['notes'] = $validated['notes'] === '' ? null : $validated['notes'];

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

        return redirect()
            ->route('pigs.show', $pig->id)
            ->with('success', 'Protocol occurrence updated: ' . $execution->status_label . '.');
    }
}
