<?php

namespace App\Http\Controllers;

use App\Models\Pig;
use App\Models\ReproductionCycle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReproductionCycleController extends Controller
{
    public function index()
    {
        $cycles = ReproductionCycle::with(['sow', 'boar'])
            ->orderByDesc('service_date')
            ->orderByDesc('id')
            ->get();

        $activeCycles = $cycles
            ->filter(fn ($cycle) => in_array($cycle->status, ReproductionCycle::activeStatuses(), true))
            ->values();

        $closedCycles = $cycles
            ->filter(fn ($cycle) => !in_array($cycle->status, ReproductionCycle::activeStatuses(), true))
            ->values();

        return view('reproduction-cycles.index', compact('cycles', 'activeCycles', 'closedCycles'));
    }

    public function create(Pig $pig)
    {
        $this->assertSowEligible($pig);

        return view('reproduction-cycles.create', [
            'pig' => $pig->loadMissing('pen'),
            'boars' => $this->availableBoars($pig),
            'statusOptions' => ReproductionCycle::statusOptions(),
            'pregnancyResultOptions' => ReproductionCycle::pregnancyResultOptions(),
            'breedingTypeOptions' => ReproductionCycle::breedingTypeOptions(),
            'semenSourceOptions' => ReproductionCycle::semenSourceOptions(),
        ]);
    }

    public function store(Request $request, Pig $pig)
    {
        $this->assertSowEligible($pig);

        $validated = $this->validateCycle($request, $pig);

        ReproductionCycle::create($this->buildPayload($validated, $pig));

        return redirect()
            ->route('pigs.show', $pig)
            ->with('success', 'Breeding record created successfully.');
    }

    public function edit(ReproductionCycle $reproductionCycle)
    {
        $reproductionCycle->load(['sow.pen', 'boar']);
        $this->assertSowEligible($reproductionCycle->sow, $reproductionCycle);

        return view('reproduction-cycles.edit', [
            'cycle' => $reproductionCycle,
            'pig' => $reproductionCycle->sow,
            'boars' => $this->availableBoars($reproductionCycle->sow),
            'statusOptions' => ReproductionCycle::statusOptions(),
            'pregnancyResultOptions' => ReproductionCycle::pregnancyResultOptions(),
            'breedingTypeOptions' => ReproductionCycle::breedingTypeOptions(),
            'semenSourceOptions' => ReproductionCycle::semenSourceOptions(),
        ]);
    }

    public function update(Request $request, ReproductionCycle $reproductionCycle)
    {
        $reproductionCycle->load('sow');
        $this->assertSowEligible($reproductionCycle->sow, $reproductionCycle);

        $validated = $this->validateCycle($request, $reproductionCycle->sow, $reproductionCycle);

        $reproductionCycle->update($this->buildPayload($validated, $reproductionCycle->sow));

        return redirect()
            ->route('pigs.show', $reproductionCycle->sow)
            ->with('success', 'Breeding record updated successfully.');
    }

    public function destroy(ReproductionCycle $reproductionCycle)
    {
        $reproductionCycle->load('sow');
        $this->assertSowEligible($reproductionCycle->sow, $reproductionCycle);

        $sow = $reproductionCycle->sow;
        $reproductionCycle->delete();

        return redirect()
            ->route('pigs.show', $sow)
            ->with('success', 'Breeding record deleted successfully.');
    }

    protected function validateCycle(Request $request, Pig $sow, ?ReproductionCycle $currentCycle = null): array
    {
        $validated = $request->validate([
            'breeding_type' => ['required', Rule::in(array_keys(ReproductionCycle::breedingTypeOptions()))],
            'service_date' => ['required', 'date', 'before_or_equal:today'],
            'pregnancy_check_date' => ['nullable', 'date', 'after_or_equal:service_date', 'before_or_equal:today'],
            'pregnancy_result' => ['nullable', Rule::in(array_keys(ReproductionCycle::pregnancyResultOptions()))],
            'expected_farrow_date' => ['nullable', 'date', 'after_or_equal:service_date'],
            'actual_farrow_date' => ['nullable', 'date', 'after_or_equal:service_date', 'before_or_equal:today'],
            'status' => [
                Rule::requiredIf(fn () => $currentCycle !== null),
                Rule::in(array_keys(ReproductionCycle::statusOptions())),
            ],
            'boar_id' => ['nullable', 'integer', 'exists:pigs,id'],
            'semen_source_type' => ['nullable', Rule::in(array_keys(ReproductionCycle::semenSourceOptions()))],
            'semen_source_name' => ['nullable', 'string', 'max:255'],
            'semen_cost' => ['nullable', 'numeric', 'min:0'],
            'breeding_cost' => ['nullable', 'numeric', 'min:0'],
            'total_born' => ['nullable', 'integer', 'min:0'],
            'born_alive' => ['nullable', 'integer', 'min:0'],
            'stillborn' => ['nullable', 'integer', 'min:0'],
            'mummified' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        if (!$currentCycle) {
            $validated['status'] = ReproductionCycle::STATUS_SERVICED;
        }

        $validated['pregnancy_result'] = $validated['pregnancy_result']
            ?? $this->resolvePregnancyResult($validated);

        $validated['expected_farrow_date'] = !empty($validated['expected_farrow_date'])
            ? $validated['expected_farrow_date']
            : Carbon::parse($validated['service_date'])->addDays(114)->toDateString();

        $errors = [];

        if ($validated['breeding_type'] === ReproductionCycle::BREEDING_TYPE_NATURAL_MATING && empty($validated['boar_id'])) {
            $errors['boar_id'] = 'A boar is required for natural mating records.';
        }

        if ($validated['breeding_type'] === ReproductionCycle::BREEDING_TYPE_ARTIFICIAL_INSEMINATION && empty($validated['semen_source_type'])) {
            $errors['semen_source_type'] = 'Semen source type is required for artificial insemination.';
        }

        if (($validated['semen_source_type'] ?? null) === ReproductionCycle::SEMEN_SOURCE_LOCAL) {
            if (empty($validated['semen_source_name'])) {
                $errors['semen_source_name'] = 'Local semen source name is required.';
            }
        }

        if (($validated['semen_source_type'] ?? null) === ReproductionCycle::SEMEN_SOURCE_PURCHASED) {
            if (empty($validated['semen_source_name'])) {
                $errors['semen_source_name'] = 'Purchased semen source name is required.';
            }

            if (!isset($validated['semen_cost']) || (float) $validated['semen_cost'] <= 0) {
                $errors['semen_cost'] = 'Purchased semen cost is required and must be greater than zero.';
            }
        }

        if (!empty($validated['boar_id'])) {
            $boar = Pig::query()->find($validated['boar_id']);

            if (!$boar) {
                $errors['boar_id'] = 'Selected boar could not be found.';
            } elseif (strtolower((string) $boar->sex) !== 'male') {
                $errors['boar_id'] = 'Selected boar must be a male pig.';
            } elseif ($boar->isOperationallyLocked()) {
                $errors['boar_id'] = $boar->operationalLockMessage('breeding records');
            }
        }

        $hasOutcomeCounts =
            ($validated['total_born'] ?? null) !== null ||
            ($validated['born_alive'] ?? null) !== null ||
            ($validated['stillborn'] ?? null) !== null ||
            ($validated['mummified'] ?? null) !== null;

        $status = $validated['status'];
        $pregnancyResult = $validated['pregnancy_result'];
        $hasActualFarrow = !empty($validated['actual_farrow_date']);

        if ($status === ReproductionCycle::STATUS_SERVICED) {
            if ($pregnancyResult !== ReproductionCycle::PREGNANCY_RESULT_PENDING) {
                $errors['pregnancy_result'] = 'Serviced status must keep pregnancy result as pending.';
            }

            if (!empty($validated['pregnancy_check_date'])) {
                $errors['pregnancy_check_date'] = 'Pregnancy check date cannot be recorded while the cycle is still only serviced.';
            }

            if ($hasActualFarrow) {
                $errors['actual_farrow_date'] = 'Actual farrowing date cannot be recorded while the cycle is still only serviced.';
            }

            if ($hasOutcomeCounts) {
                $errors['total_born'] = 'Litter outcome cannot be recorded while the cycle is still only serviced.';
            }
        }

        if (in_array($status, [
            ReproductionCycle::STATUS_PREGNANT,
            ReproductionCycle::STATUS_DUE_SOON,
            ReproductionCycle::STATUS_FARROWED,
        ], true)) {
            if ($pregnancyResult !== ReproductionCycle::PREGNANCY_RESULT_PREGNANT) {
                $errors['pregnancy_result'] = 'This cycle status requires pregnancy result to be pregnant.';
            }

            if (empty($validated['pregnancy_check_date']) && $status !== ReproductionCycle::STATUS_DUE_SOON) {
                $errors['pregnancy_check_date'] = 'Pregnancy check date is required once the sow is marked pregnant.';
            }
        }

        if (in_array($status, [
            ReproductionCycle::STATUS_NOT_PREGNANT,
            ReproductionCycle::STATUS_RETURNED_TO_HEAT,
        ], true)) {
            if ($pregnancyResult !== ReproductionCycle::PREGNANCY_RESULT_NOT_PREGNANT) {
                $errors['pregnancy_result'] = 'This cycle status requires pregnancy result to be not pregnant.';
            }

            if (empty($validated['pregnancy_check_date'])) {
                $errors['pregnancy_check_date'] = 'Pregnancy check date is required for failed breeding outcomes.';
            }

            if ($hasActualFarrow) {
                $errors['actual_farrow_date'] = 'Actual farrowing date cannot be recorded for not pregnant or returned to heat statuses.';
            }

            if ($hasOutcomeCounts) {
                $errors['total_born'] = 'Litter outcome cannot be recorded for not pregnant or returned to heat statuses.';
            }
        }

        if ($status === ReproductionCycle::STATUS_DUE_SOON) {
            if (!$this->isDueSoon($validated['expected_farrow_date'])) {
                $errors['status'] = 'Due soon can only be used when the expected farrowing date is within the due-soon threshold.';
            }

            if ($hasActualFarrow) {
                $errors['actual_farrow_date'] = 'Actual farrowing date should only be recorded once the sow has farrowed.';
            }

            if ($hasOutcomeCounts) {
                $errors['total_born'] = 'Litter outcome cannot be recorded while the sow is only due soon.';
            }
        }

        if ($status === ReproductionCycle::STATUS_FARROWED) {
            if (!$hasActualFarrow) {
                $errors['actual_farrow_date'] = 'Actual farrowing date is required when status is farrowed.';
            }

            if ($pregnancyResult !== ReproductionCycle::PREGNANCY_RESULT_PREGNANT) {
                $errors['pregnancy_result'] = 'Farrowed status requires pregnancy result to be pregnant.';
            }
        }

        if ($status === ReproductionCycle::STATUS_CLOSED) {
            $isClosedSuccess = $hasActualFarrow;
            $isClosedFailure = !$hasActualFarrow && $pregnancyResult === ReproductionCycle::PREGNANCY_RESULT_NOT_PREGNANT;

            if (!$isClosedSuccess && !$isClosedFailure) {
                $errors['status'] = 'Closed status can only be used for a completed successful farrowing or a failed breeding outcome.';
            }

            if ($isClosedSuccess && $pregnancyResult !== ReproductionCycle::PREGNANCY_RESULT_PREGNANT) {
                $errors['pregnancy_result'] = 'A successfully closed farrowing cycle must keep pregnancy result as pregnant.';
            }

            if ($isClosedFailure && $hasOutcomeCounts) {
                $errors['total_born'] = 'Failed closed cycles cannot contain litter outcome values.';
            }
        }

        if ($pregnancyResult === ReproductionCycle::PREGNANCY_RESULT_PENDING && !empty($validated['pregnancy_check_date'])) {
            $errors['pregnancy_check_date'] = 'Pregnancy check date can only be set once a pregnancy result is recorded.';
        }

        if (
            $pregnancyResult !== ReproductionCycle::PREGNANCY_RESULT_PENDING &&
            empty($validated['pregnancy_check_date']) &&
            !in_array($status, [ReproductionCycle::STATUS_SERVICED, ReproductionCycle::STATUS_CLOSED], true)
        ) {
            $errors['pregnancy_check_date'] = 'Pregnancy check date is required once the pregnancy result is no longer pending.';
        }

        if ($pregnancyResult === ReproductionCycle::PREGNANCY_RESULT_PENDING && $hasActualFarrow) {
            $errors['pregnancy_result'] = 'A farrowing record cannot remain pending. Record the pregnancy result first.';
        }

        if ($hasOutcomeCounts && !$hasActualFarrow) {
            $errors['actual_farrow_date'] = 'Litter outcome counts can only be recorded after actual farrowing date is set.';
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

        $activeCycleQuery = $sow->reproductionCyclesAsSow()
            ->whereIn('status', ReproductionCycle::activeStatuses());

        if ($currentCycle) {
            $activeCycleQuery->where('id', '!=', $currentCycle->id);
        }

        if ($activeCycleQuery->exists() && in_array($status, ReproductionCycle::activeStatuses(), true)) {
            $errors['service_date'] = 'This sow already has an active reproduction cycle.';
        }

        if (!empty($errors)) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }

        return $validated;
    }

    protected function buildPayload(array $validated, Pig $sow): array
    {
        return [
            'sow_id' => $sow->id,
            'boar_id' => $validated['boar_id'] ?? null,
            'breeding_type' => $validated['breeding_type'],
            'service_date' => $validated['service_date'],
            'pregnancy_check_date' => $validated['pregnancy_check_date'] ?? null,
            'pregnancy_result' => $validated['pregnancy_result'] ?? ReproductionCycle::PREGNANCY_RESULT_PENDING,
            'expected_farrow_date' => $validated['expected_farrow_date'] ?? Carbon::parse($validated['service_date'])->addDays(114)->toDateString(),
            'actual_farrow_date' => $validated['actual_farrow_date'] ?? null,
            'status' => $validated['status'] ?? ReproductionCycle::STATUS_SERVICED,
            'semen_source_type' => $validated['semen_source_type'] ?? null,
            'semen_source_name' => $validated['semen_source_name'] ?? null,
            'semen_cost' => (float) ($validated['semen_cost'] ?? 0),
            'breeding_cost' => (float) ($validated['breeding_cost'] ?? 0),
            'total_born' => array_key_exists('total_born', $validated) && $validated['total_born'] !== '' ? (int) $validated['total_born'] : null,
            'born_alive' => array_key_exists('born_alive', $validated) && $validated['born_alive'] !== '' ? (int) $validated['born_alive'] : null,
            'stillborn' => array_key_exists('stillborn', $validated) && $validated['stillborn'] !== '' ? (int) $validated['stillborn'] : null,
            'mummified' => array_key_exists('mummified', $validated) && $validated['mummified'] !== '' ? (int) $validated['mummified'] : null,
            'notes' => $validated['notes'] ?? null,
        ];
    }

    protected function resolvePregnancyResult(array $validated): string
    {
        if (!empty($validated['actual_farrow_date'])) {
            return ReproductionCycle::PREGNANCY_RESULT_PREGNANT;
        }

        return ReproductionCycle::PREGNANCY_RESULT_PENDING;
    }

    protected function isDueSoon(string $expectedFarrowDate): bool
    {
        $daysUntilDue = Carbon::today()->diffInDays(Carbon::parse($expectedFarrowDate), false);

        return $daysUntilDue >= 0 && $daysUntilDue <= ReproductionCycle::dueSoonThresholdDays();
    }

    protected function assertSowEligible(Pig $pig, ?ReproductionCycle $currentCycle = null): void
    {
        if (strtolower((string) $pig->sex) !== 'female') {
            abort(422, 'Reproduction cycles can only be recorded on female pigs.');
        }

        if ($pig->isOperationallyLocked()) {
            abort(422, $pig->operationalLockMessage('breeding records'));
        }

        if ($currentCycle) {
            return;
        }
    }

    protected function availableBoars(Pig $sow)
    {
        return Pig::query()
            ->where('id', '!=', $sow->id)
            ->whereRaw('LOWER(sex) = ?', ['male'])
            ->whereNull('deleted_at')
            ->whereDoesntHave('sales')
            ->whereDoesntHave('mortalityLogs')
            ->orderBy('ear_tag')
            ->get();
    }
}
