<?php

namespace App\Http\Controllers;

use App\Models\ReproductionCycle;
use App\Models\ReproductionCycleUpdate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReproductionCycleUpdateController extends Controller
{
    public function store(Request $request, ReproductionCycle $reproductionCycle)
    {
        $reproductionCycle->load(['sow', 'updates']);

        $this->assertCycleCanReceiveUpdates($reproductionCycle);

        $validated = $this->validateUpdate($request, $reproductionCycle);
        $payload = $this->buildPayload($validated, $reproductionCycle);

        $update = $reproductionCycle->updates()->create($payload);

        $this->applyUpdateToCycle($reproductionCycle, $update);

        return redirect()
            ->route('reproduction-cycles.show', $reproductionCycle)
            ->with('success', 'Progress update added successfully.');
    }

    protected function validateUpdate(Request $request, ReproductionCycle $reproductionCycle): array
    {
        $latestEventDate = $reproductionCycle->updates->max('event_date');
        $minimumEventDate = $latestEventDate
            ? Carbon::parse($latestEventDate)->toDateString()
            : $reproductionCycle->service_date->toDateString();

        $validated = $request->validate([
            'event_type' => ['required', Rule::in(array_keys(ReproductionCycleUpdate::eventOptions()))],
            'event_date' => ['required', 'date', 'after_or_equal:' . $minimumEventDate, 'before_or_equal:today'],
            'pregnancy_result' => ['nullable', Rule::in(array_keys(ReproductionCycle::pregnancyResultOptions()))],
            'actual_farrow_date' => ['nullable', 'date', 'after_or_equal:' . $reproductionCycle->service_date->toDateString(), 'before_or_equal:today'],
            'total_born' => ['nullable', 'integer', 'min:0'],
            'born_alive' => ['nullable', 'integer', 'min:0'],
            'stillborn' => ['nullable', 'integer', 'min:0'],
            'mummified' => ['nullable', 'integer', 'min:0'],
            'added_cost' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $errors = [];
        $hasOutcomeCounts =
            ($validated['total_born'] ?? null) !== null ||
            ($validated['born_alive'] ?? null) !== null ||
            ($validated['stillborn'] ?? null) !== null ||
            ($validated['mummified'] ?? null) !== null;

        if ($validated['event_type'] === ReproductionCycleUpdate::EVENT_SERVICE_STARTED) {
            $errors['event_type'] = 'Service started is recorded automatically when the breeding case is first created.';
        }

        [$statusAfterEvent, $pregnancyResult] = $this->resolveDerivedState($validated, $reproductionCycle, $errors);

        switch ($validated['event_type']) {
            case ReproductionCycleUpdate::EVENT_PREGNANCY_CHECKED:
                if (!in_array($pregnancyResult, [
                    ReproductionCycle::PREGNANCY_RESULT_PREGNANT,
                    ReproductionCycle::PREGNANCY_RESULT_NOT_PREGNANT,
                ], true)) {
                    $errors['pregnancy_result'] = 'Pregnancy check requires a pregnant or not pregnant result.';
                }

                if (!empty($validated['actual_farrow_date'])) {
                    $errors['actual_farrow_date'] = 'Actual farrowing date cannot be recorded on a pregnancy check.';
                }

                if ($hasOutcomeCounts) {
                    $errors['total_born'] = 'Litter outcome values cannot be recorded on a pregnancy check.';
                }
                break;

            case ReproductionCycleUpdate::EVENT_RETURNED_TO_HEAT:
                if (empty(trim((string) ($validated['notes'] ?? '')))) {
                    $errors['notes'] = 'Return-to-heat observation notes are required.';
                }

                if (!empty($validated['pregnancy_result']) && $validated['pregnancy_result'] !== ReproductionCycle::PREGNANCY_RESULT_NOT_PREGNANT) {
                    $errors['pregnancy_result'] = 'Returned to heat must remain under a not pregnant result.';
                }

                if (!empty($validated['actual_farrow_date'])) {
                    $errors['actual_farrow_date'] = 'Actual farrowing date cannot be recorded on a returned-to-heat event.';
                }

                if ($hasOutcomeCounts) {
                    $errors['total_born'] = 'Litter outcome values cannot be recorded on a returned-to-heat event.';
                }

                if (!empty($validated['added_cost']) && (float) $validated['added_cost'] > 0) {
                    $errors['added_cost'] = 'Added cost is not used on a returned-to-heat observation.';
                }
                break;

            case ReproductionCycleUpdate::EVENT_FARROWING_RECORDED:
                if (empty($validated['actual_farrow_date'])) {
                    $errors['actual_farrow_date'] = 'Actual farrowing date is required for a farrowing event.';
                }

                if (($validated['total_born'] ?? null) === null) {
                    $errors['total_born'] = 'Total born is required for a farrowing event.';
                }

                if (
                    !empty($validated['actual_farrow_date'])
                    && !empty($validated['event_date'])
                    && Carbon::parse($validated['actual_farrow_date'])->greaterThan(Carbon::parse($validated['event_date']))
                ) {
                    $errors['actual_farrow_date'] = 'Actual farrowing date cannot be later than the farrowing event date.';
                }
                break;

            case ReproductionCycleUpdate::EVENT_CYCLE_CLOSED:
                if (empty(trim((string) ($validated['notes'] ?? '')))) {
                    $errors['notes'] = 'Closure notes are required when closing a breeding case.';
                }

                if (!empty($validated['actual_farrow_date'])) {
                    $errors['actual_farrow_date'] = 'Actual farrowing date cannot be recorded on a cycle closure event.';
                }

                if ($hasOutcomeCounts) {
                    $errors['total_born'] = 'Litter outcome values cannot be recorded on a cycle closure event.';
                }

                if (!empty($validated['added_cost']) && (float) $validated['added_cost'] > 0) {
                    $errors['added_cost'] = 'Added cost is not used on a cycle closure event.';
                }
                break;
        }

        if (($validated['born_alive'] ?? null) !== null && ($validated['total_born'] ?? null) !== null && (int) $validated['born_alive'] > (int) $validated['total_born']) {
            $errors['born_alive'] = 'Born alive cannot be greater than total born.';
        }

        if (($validated['stillborn'] ?? null) !== null && ($validated['total_born'] ?? null) !== null && (int) $validated['stillborn'] > (int) $validated['total_born']) {
            $errors['stillborn'] = 'Stillborn cannot be greater than total born.';
        }

        if (($validated['mummified'] ?? null) !== null && ($validated['total_born'] ?? null) !== null && (int) $validated['mummified'] > (int) $validated['total_born']) {
            $errors['mummified'] = 'Mummified cannot be greater than total born.';
        }

        if (
            ($validated['born_alive'] ?? null) !== null &&
            ($validated['stillborn'] ?? null) !== null &&
            ($validated['mummified'] ?? null) !== null &&
            ($validated['total_born'] ?? null) !== null
        ) {
            $recordedTotal = (int) $validated['born_alive'] + (int) $validated['stillborn'] + (int) $validated['mummified'];

            if ($recordedTotal > (int) $validated['total_born']) {
                $errors['total_born'] = 'Total born cannot be less than the sum of born alive, stillborn, and mummified.';
            }
        }

        if (!empty($errors)) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }

        $validated['status_after_event'] = $statusAfterEvent;
        $validated['pregnancy_result'] = $pregnancyResult;

        return $validated;
    }

    protected function buildPayload(array $validated, ReproductionCycle $reproductionCycle): array
    {
        return [
            'reproduction_cycle_id' => $reproductionCycle->id,
            'event_type' => $validated['event_type'],
            'event_date' => $validated['event_date'],
            'status_after_event' => $validated['status_after_event'],
            'pregnancy_result' => $validated['pregnancy_result'],
            'actual_farrow_date' => $validated['actual_farrow_date'] ?? null,
            'total_born' => array_key_exists('total_born', $validated) && $validated['total_born'] !== '' ? (int) $validated['total_born'] : null,
            'born_alive' => array_key_exists('born_alive', $validated) && $validated['born_alive'] !== '' ? (int) $validated['born_alive'] : null,
            'stillborn' => array_key_exists('stillborn', $validated) && $validated['stillborn'] !== '' ? (int) $validated['stillborn'] : null,
            'mummified' => array_key_exists('mummified', $validated) && $validated['mummified'] !== '' ? (int) $validated['mummified'] : null,
            'added_cost' => (float) ($validated['added_cost'] ?? 0),
            'notes' => $validated['notes'] ?? null,
        ];
    }

    protected function resolveDerivedState(array $validated, ReproductionCycle $reproductionCycle, array &$errors): array
    {
        $eventType = $validated['event_type'];
        $pregnancyResult = $validated['pregnancy_result'] ?? null;

        switch ($eventType) {
            case ReproductionCycleUpdate::EVENT_PREGNANCY_CHECKED:
                if ($reproductionCycle->status !== ReproductionCycle::STATUS_SERVICED) {
                    $errors['event_type'] = 'Pregnancy check can only be recorded while the breeding case is still serviced.';
                }

                if ($pregnancyResult === ReproductionCycle::PREGNANCY_RESULT_PREGNANT) {
                    return [ReproductionCycle::STATUS_PREGNANT, ReproductionCycle::PREGNANCY_RESULT_PREGNANT];
                }

                if ($pregnancyResult === ReproductionCycle::PREGNANCY_RESULT_NOT_PREGNANT) {
                    return [ReproductionCycle::STATUS_NOT_PREGNANT, ReproductionCycle::PREGNANCY_RESULT_NOT_PREGNANT];
                }

                return [null, $pregnancyResult];

            case ReproductionCycleUpdate::EVENT_RETURNED_TO_HEAT:
                if ($reproductionCycle->status !== ReproductionCycle::STATUS_NOT_PREGNANT) {
                    $errors['event_type'] = 'Returned to heat can only be recorded after a not pregnant result.';
                }

                return [ReproductionCycle::STATUS_RETURNED_TO_HEAT, ReproductionCycle::PREGNANCY_RESULT_NOT_PREGNANT];

            case ReproductionCycleUpdate::EVENT_FARROWING_RECORDED:
                if (!in_array($reproductionCycle->display_status, [
                    ReproductionCycle::STATUS_PREGNANT,
                    ReproductionCycle::STATUS_DUE_SOON,
                ], true)) {
                    $errors['event_type'] = 'Farrowing can only be recorded on a pregnant or due-soon breeding case.';
                }

                return [ReproductionCycle::STATUS_FARROWED, ReproductionCycle::PREGNANCY_RESULT_PREGNANT];

            case ReproductionCycleUpdate::EVENT_CYCLE_CLOSED:
                if (!in_array($reproductionCycle->status, [
                    ReproductionCycle::STATUS_NOT_PREGNANT,
                    ReproductionCycle::STATUS_RETURNED_TO_HEAT,
                    ReproductionCycle::STATUS_FARROWED,
                ], true)) {
                    $errors['event_type'] = 'Cycle can only be closed after a failed breeding path or after farrowing.';
                }

                return [ReproductionCycle::STATUS_CLOSED, $reproductionCycle->pregnancy_result];
        }

        return [null, $pregnancyResult];
    }

    protected function applyUpdateToCycle(ReproductionCycle $reproductionCycle, ReproductionCycleUpdate $update): void
    {
        $breedingCost = (float) $reproductionCycle->breeding_cost + (float) ($update->added_cost ?? 0);

        $payload = [
            'status' => $update->status_after_event ?? $reproductionCycle->status,
            'pregnancy_result' => $update->pregnancy_result ?? $reproductionCycle->pregnancy_result,
            'breeding_cost' => $breedingCost,
        ];

        if (
            $update->event_type === ReproductionCycleUpdate::EVENT_PREGNANCY_CHECKED
            && !$reproductionCycle->expected_farrow_date
        ) {
            $payload['expected_farrow_date'] = Carbon::parse($reproductionCycle->service_date)->addDays(114)->toDateString();
        }

        if ($update->event_type === ReproductionCycleUpdate::EVENT_PREGNANCY_CHECKED) {
            $payload['pregnancy_check_date'] = $update->event_date;
        }

        if ($update->event_type === ReproductionCycleUpdate::EVENT_FARROWING_RECORDED) {
            $payload['actual_farrow_date'] = $update->actual_farrow_date;
            $payload['total_born'] = $update->total_born;
            $payload['born_alive'] = $update->born_alive;
            $payload['stillborn'] = $update->stillborn;
            $payload['mummified'] = $update->mummified;
        }

        $reproductionCycle->update($payload);
    }

    protected function assertCycleCanReceiveUpdates(ReproductionCycle $reproductionCycle): void
    {
        if ($reproductionCycle->status === ReproductionCycle::STATUS_CLOSED) {
            abort(422, 'This breeding case is already closed and can no longer receive progress updates.');
        }

        if ($reproductionCycle->sow && $reproductionCycle->sow->isOperationallyLocked()) {
            abort(422, $reproductionCycle->sow->operationalLockMessage('breeding records'));
        }
    }
}
