<?php

namespace App\Http\Controllers;

use App\Models\Pig;
use App\Models\ReproductionCycle;
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

        $activeCycles = $cycles->filter(fn ($cycle) => in_array($cycle->status, ['open', 'pregnant'], true))->values();
        $closedCycles = $cycles->filter(fn ($cycle) => in_array($cycle->status, ['failed', 'farrowed'], true))->values();

        return view('reproduction-cycles.index', compact('cycles', 'activeCycles', 'closedCycles'));
    }

    public function create(Pig $pig)
    {
        $this->assertSowEligible($pig);

        $boars = $this->availableBoars($pig);

        return view('reproduction-cycles.create', [
            'pig' => $pig,
            'boars' => $boars,
            'statusOptions' => $this->statusOptions(),
            'breedingTypeOptions' => $this->breedingTypeOptions(),
            'semenSourceOptions' => $this->semenSourceOptions(),
        ]);
    }

    public function store(Request $request, Pig $pig)
    {
        $this->assertSowEligible($pig);

        $validated = $this->validateCycle($request, $pig);

        ReproductionCycle::create([
            'sow_id' => $pig->id,
            'boar_id' => $validated['boar_id'] ?: null,
            'breeding_type' => $validated['breeding_type'],
            'service_date' => $validated['service_date'],
            'pregnancy_check_date' => $validated['pregnancy_check_date'] ?: null,
            'expected_farrow_date' => $validated['expected_farrow_date'] ?: now()->parse($validated['service_date'])->addDays(114)->toDateString(),
            'actual_farrow_date' => $validated['actual_farrow_date'] ?: null,
            'status' => $validated['status'],
            'semen_source_type' => $validated['semen_source_type'] ?: null,
            'semen_source_name' => $validated['semen_source_name'] ?: null,
            'semen_cost' => (float) ($validated['semen_cost'] ?? 0),
            'breeding_cost' => (float) ($validated['breeding_cost'] ?? 0),
            'total_born' => $validated['total_born'] !== null && $validated['total_born'] !== '' ? (int) $validated['total_born'] : null,
            'born_alive' => $validated['born_alive'] !== null && $validated['born_alive'] !== '' ? (int) $validated['born_alive'] : null,
            'stillborn' => $validated['stillborn'] !== null && $validated['stillborn'] !== '' ? (int) $validated['stillborn'] : null,
            'mummified' => $validated['mummified'] !== null && $validated['mummified'] !== '' ? (int) $validated['mummified'] : null,
            'notes' => $validated['notes'] ?: null,
        ]);

        return redirect()
            ->route('pigs.show', $pig)
            ->with('success', 'Reproduction cycle recorded successfully.');
    }

    public function edit(ReproductionCycle $reproductionCycle)
    {
        $reproductionCycle->load(['sow', 'boar']);
        $this->assertSowEligible($reproductionCycle->sow, $reproductionCycle);

        $boars = $this->availableBoars($reproductionCycle->sow);

        return view('reproduction-cycles.edit', [
            'cycle' => $reproductionCycle,
            'pig' => $reproductionCycle->sow,
            'boars' => $boars,
            'statusOptions' => $this->statusOptions(),
            'breedingTypeOptions' => $this->breedingTypeOptions(),
            'semenSourceOptions' => $this->semenSourceOptions(),
        ]);
    }

    public function update(Request $request, ReproductionCycle $reproductionCycle)
    {
        $reproductionCycle->load('sow');
        $this->assertSowEligible($reproductionCycle->sow, $reproductionCycle);

        $validated = $this->validateCycle($request, $reproductionCycle->sow, $reproductionCycle);

        $reproductionCycle->update([
            'boar_id' => $validated['boar_id'] ?: null,
            'breeding_type' => $validated['breeding_type'],
            'service_date' => $validated['service_date'],
            'pregnancy_check_date' => $validated['pregnancy_check_date'] ?: null,
            'expected_farrow_date' => $validated['expected_farrow_date'] ?: now()->parse($validated['service_date'])->addDays(114)->toDateString(),
            'actual_farrow_date' => $validated['actual_farrow_date'] ?: null,
            'status' => $validated['status'],
            'semen_source_type' => $validated['semen_source_type'] ?: null,
            'semen_source_name' => $validated['semen_source_name'] ?: null,
            'semen_cost' => (float) ($validated['semen_cost'] ?? 0),
            'breeding_cost' => (float) ($validated['breeding_cost'] ?? 0),
            'total_born' => $validated['total_born'] !== null && $validated['total_born'] !== '' ? (int) $validated['total_born'] : null,
            'born_alive' => $validated['born_alive'] !== null && $validated['born_alive'] !== '' ? (int) $validated['born_alive'] : null,
            'stillborn' => $validated['stillborn'] !== null && $validated['stillborn'] !== '' ? (int) $validated['stillborn'] : null,
            'mummified' => $validated['mummified'] !== null && $validated['mummified'] !== '' ? (int) $validated['mummified'] : null,
            'notes' => $validated['notes'] ?: null,
        ]);

        return redirect()
            ->route('pigs.show', $reproductionCycle->sow)
            ->with('success', 'Reproduction cycle updated successfully.');
    }

    protected function validateCycle(Request $request, Pig $sow, ?ReproductionCycle $currentCycle = null): array
    {
        $validated = $request->validate([
            'breeding_type' => ['required', Rule::in(array_keys($this->breedingTypeOptions()))],
            'service_date' => ['required', 'date', 'before_or_equal:today'],
            'pregnancy_check_date' => ['nullable', 'date', 'after_or_equal:service_date'],
            'expected_farrow_date' => ['nullable', 'date', 'after_or_equal:service_date'],
            'actual_farrow_date' => ['nullable', 'date', 'after_or_equal:service_date'],
            'status' => ['required', Rule::in(array_keys($this->statusOptions()))],
            'boar_id' => ['nullable', 'integer', 'exists:pigs,id'],
            'semen_source_type' => ['nullable', Rule::in(array_keys($this->semenSourceOptions()))],
            'semen_source_name' => ['nullable', 'string', 'max:255'],
            'semen_cost' => ['nullable', 'numeric', 'min:0'],
            'breeding_cost' => ['nullable', 'numeric', 'min:0'],
            'total_born' => ['nullable', 'integer', 'min:0'],
            'born_alive' => ['nullable', 'integer', 'min:0'],
            'stillborn' => ['nullable', 'integer', 'min:0'],
            'mummified' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $errors = [];

        if ($validated['breeding_type'] === 'natural_mating' && empty($validated['boar_id'])) {
            $errors['boar_id'] = 'A boar is required for natural mating records.';
        }

        if ($validated['breeding_type'] === 'artificial_insemination' && empty($validated['semen_source_type'])) {
            $errors['semen_source_type'] = 'Semen source type is required for artificial insemination.';
        }

        if (($validated['semen_source_type'] ?? null) === 'purchased') {
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

        if (($validated['status'] ?? null) === 'farrowed' && empty($validated['actual_farrow_date'])) {
            $errors['actual_farrow_date'] = 'Actual farrowing date is required when status is farrowed.';
        }

        if ($hasOutcomeCounts && ($validated['status'] ?? null) !== 'farrowed') {
            $errors['status'] = 'Litter outcome counts can only be recorded when the cycle status is farrowed.';
        }

        $activeCycleQuery = $sow->reproductionCyclesAsSow()
            ->whereIn('status', ['open', 'pregnant']);

        if ($currentCycle) {
            $activeCycleQuery->where('id', '!=', $currentCycle->id);
        }

        if ($activeCycleQuery->exists() && in_array($validated['status'], ['open', 'pregnant'], true)) {
            $errors['status'] = 'This sow already has an active reproduction cycle.';
        }

        if (!empty($errors)) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }

        return $validated;
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

    protected function statusOptions(): array
    {
        return [
            'open' => 'Open',
            'pregnant' => 'Pregnant',
            'failed' => 'Failed',
            'farrowed' => 'Farrowed',
        ];
    }

    protected function breedingTypeOptions(): array
    {
        return [
            'natural_mating' => 'Natural Mating',
            'artificial_insemination' => 'Artificial Insemination',
        ];
    }

    protected function semenSourceOptions(): array
    {
        return [
            'local' => 'Locally Sourced',
            'purchased' => 'Purchased',
        ];
    }
}
