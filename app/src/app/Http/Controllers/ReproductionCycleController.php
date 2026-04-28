<?php

namespace App\Http\Controllers;

use App\Models\Pig;
use App\Models\ReproductionCycle;
use App\Models\ReproductionCycleUpdate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ReproductionCycleController extends Controller
{
    public function index()
    {
        $cycles = ReproductionCycle::with(['sow', 'boar'])
            ->withCount(['updates', 'bornPiglets'])
            ->orderByDesc('service_date')
            ->orderByDesc('id')
            ->get();

        $activeCycles = $cycles
            ->filter(fn ($cycle) => $cycle->is_active_cycle)
            ->values();

        $closedCycles = $cycles
            ->filter(fn ($cycle) => !$cycle->is_active_cycle)
            ->values();

        return view('reproduction-cycles.index', compact('cycles', 'activeCycles', 'closedCycles'));
    }

    public function show(ReproductionCycle $reproductionCycle)
    {
        $relations = ['sow.pen', 'boar', 'updates'];

        if ($this->supportsAttemptMetadata()) {
            $relations[] = 'updates.donorBoar';
        }

        $reproductionCycle->load($relations)->loadCount('bornPiglets');

        return view('reproduction-cycles.show', [
            'cycle' => $reproductionCycle,
            'pig' => $reproductionCycle->sow,
            'availableUpdateEvents' => $this->availableUpdateEvents($reproductionCycle),
            'pregnancyResultOptions' => ReproductionCycle::pregnancyResultOptions(),
        ]);
    }

    public function create(Pig $pig)
    {
        $this->assertSowEligible($pig);
        $this->assertNoOtherActiveCycle($pig);

        $boars = $this->availableBoars($pig);

        return view('reproduction-cycles.create', [
            'pig' => $pig->loadMissing('pen'),
            'boars' => $boars,
            'boarRiskMap' => $this->buildBoarRiskMap($pig, $boars),
            'initialSelectedBoarId' => (string) old('boar_id', ''),
            'breedingTypeOptions' => ReproductionCycle::breedingTypeOptions(),
            'semenSourceOptions' => ReproductionCycle::semenSourceOptions(),
            'cycle' => null,
            'formMode' => 'create',
            'submitRoute' => route('reproduction-cycles.store', $pig),
            'submitLabel' => 'Save Breeding Record',
            'attemptNumber' => 1,
            'defaults' => [
                'breeding_type' => old('breeding_type', ''),
                'service_date' => old('service_date', now()->toDateString()),
                'boar_id' => old('boar_id', ''),
                'semen_source_type' => old('semen_source_type', ''),
                'semen_source_name' => old('semen_source_name', ''),
                'semen_cost' => old('semen_cost', '0.00'),
                'breeding_cost' => old('breeding_cost', '0.00'),
                'notes' => old('notes', ''),
            ],
        ]);
    }

    public function store(Request $request, Pig $pig)
    {
        $this->assertSowEligible($pig);
        $this->assertNoOtherActiveCycle($pig);

        $validated = $this->validateAttemptMetadata($request, $pig);

        $cycle = ReproductionCycle::create($this->buildInitialCyclePayload($validated, $pig));

        $this->recordServiceStartedUpdate($cycle, $validated, 1);

        return redirect()
            ->route('reproduction-cycles.show', $cycle)
            ->with('success', 'Breeding case created successfully.');
    }

    public function createNextAttempt(ReproductionCycle $reproductionCycle)
    {
        $relations = ['sow.pen', 'boar', 'updates'];

        if ($this->supportsAttemptMetadata()) {
            $relations[] = 'updates.donorBoar';
        }

        $reproductionCycle->load($relations);
        $this->assertRetryCanStart($reproductionCycle);

        $defaults = $this->nextAttemptDefaults($reproductionCycle);
        $attemptNumber = $reproductionCycle->current_attempt_number + 1;
        $boars = $this->availableBoars($reproductionCycle->sow);

        return view('reproduction-cycles.create', [
            'pig' => $reproductionCycle->sow,
            'boars' => $boars,
            'boarRiskMap' => $this->buildBoarRiskMap($reproductionCycle->sow, $boars),
            'initialSelectedBoarId' => (string) old('boar_id', $defaults['boar_id']),
            'breedingTypeOptions' => ReproductionCycle::breedingTypeOptions(),
            'semenSourceOptions' => ReproductionCycle::semenSourceOptions(),
            'cycle' => $reproductionCycle,
            'formMode' => 'retry',
            'submitRoute' => route('reproduction-cycles.attempts.store', $reproductionCycle),
            'submitLabel' => 'Start Attempt ' . $attemptNumber,
            'attemptNumber' => $attemptNumber,
            'defaults' => [
                'breeding_type' => old('breeding_type', $defaults['breeding_type']),
                'service_date' => old('service_date', now()->toDateString()),
                'boar_id' => old('boar_id', $defaults['boar_id']),
                'semen_source_type' => old('semen_source_type', $defaults['semen_source_type']),
                'semen_source_name' => old('semen_source_name', $defaults['semen_source_name']),
                'semen_cost' => old('semen_cost', number_format((float) $defaults['semen_cost'], 2, '.', '')),
                'breeding_cost' => old('breeding_cost', number_format((float) $defaults['breeding_cost'], 2, '.', '')),
                'notes' => old('notes', $defaults['notes']),
            ],
        ]);
    }

    public function storeNextAttempt(Request $request, ReproductionCycle $reproductionCycle)
    {
        $reproductionCycle->load('sow');
        $this->assertRetryCanStart($reproductionCycle);

        $validated = $this->validateAttemptMetadata($request, $reproductionCycle->sow, $reproductionCycle);
        $attemptNumber = $reproductionCycle->current_attempt_number + 1;

        $reproductionCycle->update($this->buildRetryCyclePayload($validated, $reproductionCycle));

        $this->recordServiceStartedUpdate($reproductionCycle->fresh(), $validated, $attemptNumber);

        return redirect()
            ->route('reproduction-cycles.show', $reproductionCycle)
            ->with('success', 'Attempt ' . $attemptNumber . ' started successfully.');
    }

    public function edit(ReproductionCycle $reproductionCycle)
    {
        $reproductionCycle->load(['sow.pen', 'boar']);
        $this->assertSowEligible($reproductionCycle->sow, $reproductionCycle);

        $boars = $this->availableBoars($reproductionCycle->sow);

        return view('reproduction-cycles.edit', [
            'cycle' => $reproductionCycle,
            'pig' => $reproductionCycle->sow,
            'boars' => $boars,
            'boarRiskMap' => $this->buildBoarRiskMap($reproductionCycle->sow, $boars),
            'initialSelectedBoarId' => (string) old('boar_id', $reproductionCycle->boar_id),
            'breedingTypeOptions' => ReproductionCycle::breedingTypeOptions(),
            'semenSourceOptions' => ReproductionCycle::semenSourceOptions(),
        ]);
    }

    public function update(Request $request, ReproductionCycle $reproductionCycle)
    {
        $reproductionCycle->load(['sow', 'updates']);
        $this->assertSowEligible($reproductionCycle->sow, $reproductionCycle);

        $validated = $this->validateMetadataUpdate($request, $reproductionCycle);

        $reproductionCycle->update($this->buildMetadataUpdatePayload($validated, $reproductionCycle));
        $this->syncCurrentAttemptServiceMetadata($reproductionCycle->fresh('updates'), $validated);

        return redirect()
            ->route('reproduction-cycles.show', $reproductionCycle)
            ->with('success', 'Breeding case metadata updated successfully.');
    }

    public function destroy(ReproductionCycle $reproductionCycle)
    {
        $reproductionCycle->load('sow');
        $this->assertSowEligible($reproductionCycle->sow, $reproductionCycle);

        $sow = $reproductionCycle->sow;
        $reproductionCycle->delete();

        return redirect()
            ->route('pigs.show', $sow)
            ->with('success', 'Breeding case deleted successfully.');
    }

    protected function validateAttemptMetadata(Request $request, Pig $sow, ?ReproductionCycle $currentCycle = null): array
    {
        $validated = $request->validate([
            'breeding_type' => ['required', Rule::in(array_keys(ReproductionCycle::breedingTypeOptions()))],
            'service_date' => ['required', 'date', 'before_or_equal:today'],
            'boar_id' => ['nullable', 'integer', 'exists:pigs,id'],
            'semen_source_type' => ['nullable', Rule::in(array_keys(ReproductionCycle::semenSourceOptions()))],
            'semen_source_name' => ['nullable', 'string', 'max:255'],
            'semen_cost' => ['nullable', 'numeric', 'min:0'],
            'breeding_cost' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        return $this->normalizeAttemptMetadata($validated, $sow, $currentCycle);
    }

    protected function validateMetadataUpdate(Request $request, ReproductionCycle $cycle): array
    {
        $validated = $request->validate([
            'breeding_type' => ['required', Rule::in(array_keys(ReproductionCycle::breedingTypeOptions()))],
            'service_date' => ['required', 'date', 'before_or_equal:today'],
            'boar_id' => ['nullable', 'integer', 'exists:pigs,id'],
            'semen_source_type' => ['nullable', Rule::in(array_keys(ReproductionCycle::semenSourceOptions()))],
            'semen_source_name' => ['nullable', 'string', 'max:255'],
            'semen_cost' => ['nullable', 'numeric', 'min:0'],
            'breeding_cost' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated = $this->normalizeAttemptMetadata($validated, $cycle->sow, $cycle);
        $errors = [];

        if ($cycle->pregnancy_check_date && Carbon::parse($validated['service_date'])->greaterThan($cycle->pregnancy_check_date)) {
            $errors['service_date'] = 'Service date cannot be later than the existing pregnancy check date.';
        }

        if ($cycle->actual_farrow_date && Carbon::parse($validated['service_date'])->greaterThan($cycle->actual_farrow_date)) {
            $errors['service_date'] = 'Service date cannot be later than the actual farrow date already recorded on this case.';
        }

        if (!empty($errors)) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }

        return $validated;
    }

    protected function normalizeAttemptMetadata(array $validated, Pig $sow, ?ReproductionCycle $currentCycle = null): array
    {
        $errors = [];
        $breedingType = $validated['breeding_type'];
        $semenSourceType = $validated['semen_source_type'] ?? null;

        if ($breedingType === ReproductionCycle::BREEDING_TYPE_NATURAL_MATING) {
            if (empty($validated['boar_id'])) {
                $errors['boar_id'] = 'A boar is required for natural mating records.';
            }

            $validated['semen_source_type'] = null;
            $validated['semen_source_name'] = null;
            $validated['semen_cost'] = 0;
        }

        if ($breedingType === ReproductionCycle::BREEDING_TYPE_ARTIFICIAL_INSEMINATION) {
            if (!$semenSourceType) {
                $errors['semen_source_type'] = 'Semen source type is required for artificial insemination.';
            }

            if ($semenSourceType === ReproductionCycle::SEMEN_SOURCE_LOCAL) {
                if (empty($validated['boar_id'])) {
                    $errors['boar_id'] = 'A donor boar is required for locally sourced AI.';
                }

                $validated['semen_cost'] = 0;
            }

            if ($semenSourceType === ReproductionCycle::SEMEN_SOURCE_PURCHASED) {
                $validated['boar_id'] = null;

                if (empty(trim((string) ($validated['semen_source_name'] ?? '')))) {
                    $errors['semen_source_name'] = 'Supplier or semen source name is required for purchased AI.';
                }

                if (!isset($validated['semen_cost']) || (float) $validated['semen_cost'] <= 0) {
                    $errors['semen_cost'] = 'Purchased semen cost is required and must be greater than zero.';
                }
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
            } else {
                $pairingGuard = $sow->breedingPairingGuardWith($boar);

                if ($pairingGuard['blocked']) {
                    $errors['boar_id'] = $pairingGuard['message'];
                }
            }
        }

        $activeCycleQuery = $sow->reproductionCyclesAsSow()
            ->whereIn('status', ReproductionCycle::activeStatuses());

        if ($currentCycle) {
            $activeCycleQuery->where('id', '!=', $currentCycle->id);
        }

        if ($activeCycleQuery->exists()) {
            $errors['service_date'] = 'This sow already has another active reproduction cycle.';
        }

        if (!empty($errors)) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }

        $validated['semen_source_name'] = $validated['semen_source_name'] ?? null;
        $validated['semen_cost'] = (float) ($validated['semen_cost'] ?? 0);
        $validated['breeding_cost'] = (float) ($validated['breeding_cost'] ?? 0);
        $validated['notes'] = $validated['notes'] ?? null;

        return $validated;
    }

    protected function buildInitialCyclePayload(array $validated, Pig $sow): array
    {
        return [
            'sow_id' => $sow->id,
            'boar_id' => $validated['boar_id'] ?? null,
            'breeding_type' => $validated['breeding_type'],
            'service_date' => $validated['service_date'],
            'pregnancy_check_date' => null,
            'pregnancy_result' => ReproductionCycle::PREGNANCY_RESULT_PENDING,
            'expected_farrow_date' => null,
            'actual_farrow_date' => null,
            'status' => ReproductionCycle::STATUS_SERVICED,
            'semen_source_type' => $validated['semen_source_type'] ?? null,
            'semen_source_name' => $validated['semen_source_name'] ?? null,
            'semen_cost' => (float) ($validated['semen_cost'] ?? 0),
            'breeding_cost' => (float) ($validated['breeding_cost'] ?? 0),
            'total_born' => null,
            'born_alive' => null,
            'stillborn' => null,
            'mummified' => null,
            'notes' => $validated['notes'] ?? null,
        ];
    }

    protected function buildRetryCyclePayload(array $validated, ReproductionCycle $cycle): array
    {
        return [
            'boar_id' => $validated['boar_id'] ?? null,
            'breeding_type' => $validated['breeding_type'],
            'service_date' => $validated['service_date'],
            'pregnancy_check_date' => null,
            'pregnancy_result' => ReproductionCycle::PREGNANCY_RESULT_PENDING,
            'expected_farrow_date' => null,
            'actual_farrow_date' => null,
            'status' => ReproductionCycle::STATUS_SERVICED,
            'semen_source_type' => $validated['semen_source_type'] ?? null,
            'semen_source_name' => $validated['semen_source_name'] ?? null,
            'semen_cost' => (float) ($validated['semen_cost'] ?? 0),
            'breeding_cost' => (float) $cycle->breeding_cost + (float) ($validated['breeding_cost'] ?? 0),
            'total_born' => null,
            'born_alive' => null,
            'stillborn' => null,
            'mummified' => null,
            'notes' => $validated['notes'] ?? null,
        ];
    }

    protected function buildMetadataUpdatePayload(array $validated, ReproductionCycle $cycle): array
    {
        $shouldShowExpected = $cycle->pregnancy_result === ReproductionCycle::PREGNANCY_RESULT_PREGNANT || !empty($cycle->actual_farrow_date);

        return [
            'boar_id' => $validated['boar_id'] ?? null,
            'breeding_type' => $validated['breeding_type'],
            'service_date' => $validated['service_date'],
            'expected_farrow_date' => $shouldShowExpected
                ? Carbon::parse($validated['service_date'])->addDays(ReproductionCycle::gestationDays())->toDateString()
                : null,
            'semen_source_type' => $validated['semen_source_type'] ?? null,
            'semen_source_name' => $validated['semen_source_name'] ?? null,
            'semen_cost' => (float) ($validated['semen_cost'] ?? 0),
            'breeding_cost' => (float) ($validated['breeding_cost'] ?? 0),
            'notes' => $validated['notes'] ?? null,
        ];
    }

    protected function recordServiceStartedUpdate(ReproductionCycle $cycle, array $validated, int $attemptNumber): void
    {
        $payload = [
            'event_type' => ReproductionCycleUpdate::EVENT_SERVICE_STARTED,
            'event_date' => $validated['service_date'],
            'status_after_event' => ReproductionCycle::STATUS_SERVICED,
            'pregnancy_result' => ReproductionCycle::PREGNANCY_RESULT_PENDING,
            'added_cost' => (float) ($validated['breeding_cost'] ?? 0),
            'notes' => $validated['notes'] ?? null,
        ];

        if ($this->supportsAttemptMetadata()) {
            $payload = array_merge([
                'attempt_number' => $attemptNumber,
                'boar_id' => $validated['boar_id'] ?? null,
                'breeding_type' => $validated['breeding_type'],
                'semen_source_type' => $validated['semen_source_type'] ?? null,
                'semen_source_name' => $validated['semen_source_name'] ?? null,
                'semen_cost' => (float) ($validated['semen_cost'] ?? 0),
            ], $payload);
        }

        $cycle->updates()->create($payload);
    }

    protected function syncCurrentAttemptServiceMetadata(ReproductionCycle $cycle, array $validated): void
    {
        if (!$this->supportsAttemptMetadata()) {
            return;
        }

        $serviceUpdate = $cycle->updates()
            ->where('event_type', ReproductionCycleUpdate::EVENT_SERVICE_STARTED)
            ->where('attempt_number', $cycle->current_attempt_number)
            ->orderByDesc('id')
            ->first();

        if (!$serviceUpdate) {
            return;
        }

        $serviceUpdate->update([
            'boar_id' => $validated['boar_id'] ?? null,
            'breeding_type' => $validated['breeding_type'],
            'semen_source_type' => $validated['semen_source_type'] ?? null,
            'semen_source_name' => $validated['semen_source_name'] ?? null,
            'semen_cost' => (float) ($validated['semen_cost'] ?? 0),
            'event_date' => $validated['service_date'],
            'notes' => $validated['notes'] ?? null,
        ]);
    }

    protected function nextAttemptDefaults(ReproductionCycle $cycle): array
    {
        $breedingCost = 0;

        if ($this->supportsAttemptMetadata()) {
            $serviceUpdate = $cycle->updates()
                ->where('event_type', ReproductionCycleUpdate::EVENT_SERVICE_STARTED)
                ->where('attempt_number', $cycle->current_attempt_number)
                ->orderByDesc('id')
                ->first();

            $breedingCost = (float) ($serviceUpdate->added_cost ?? 0);
        }

        return [
            'breeding_type' => $cycle->breeding_type,
            'boar_id' => $cycle->boar_id,
            'semen_source_type' => $cycle->semen_source_type,
            'semen_source_name' => $cycle->semen_source_name,
            'semen_cost' => (float) ($cycle->semen_cost ?? 0),
            'breeding_cost' => $breedingCost,
            'notes' => $cycle->notes,
        ];
    }

    protected function assertNoOtherActiveCycle(Pig $pig): void
    {
        if ($pig->reproductionCyclesAsSow()->whereIn('status', ReproductionCycle::activeStatuses())->exists()) {
            abort(422, 'This sow already has an active reproduction cycle.');
        }
    }

    protected function assertRetryCanStart(ReproductionCycle $cycle): void
    {
        $cycle->loadMissing('sow');
        $this->assertSowEligible($cycle->sow, $cycle);

        if ($cycle->display_status !== ReproductionCycle::STATUS_RETURNED_TO_HEAT) {
            abort(422, 'A new breeding attempt can only be started after the case is marked returned to heat.');
        }

        $otherActiveCycleExists = $cycle->sow->reproductionCyclesAsSow()
            ->where('id', '!=', $cycle->id)
            ->whereIn('status', ReproductionCycle::activeStatuses())
            ->exists();

        if ($otherActiveCycleExists) {
            abort(422, 'This sow already has another active reproduction cycle.');
        }
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

    protected function buildBoarRiskMap(Pig $sow, $boars): array
    {
        return collect($boars)
            ->mapWithKeys(function (Pig $boar) use ($sow) {
                $guard = $sow->breedingPairingGuardWith($boar);

                $reasonLabel = match ($guard['code']) {
                    'self_pairing' => 'Same Pig',
                    'parent_offspring' => 'Parent-Offspring',
                    'full_siblings' => 'Full Siblings',
                    'half_siblings' => 'Shared Proven Parent',
                    'grandparent_grandchild' => 'Grandparent Line',
                    default => 'No Proven Dangerous Relation',
                };

                return [
                    (string) $boar->id => [
                        'blocked' => (bool) $guard['blocked'],
                        'code' => $guard['code'],
                        'status_label' => $guard['blocked'] ? 'Blocked' : 'Allowed',
                        'status_badge_class' => $guard['blocked'] ? 'red' : 'green',
                        'reason_label' => $reasonLabel,
                        'message' => $guard['blocked']
                            ? $guard['message']
                            : 'No proven dangerous relation found in stored lineage truth for this sow and boar.',
                        'boar_ear_tag' => $boar->ear_tag,
                        'boar_breed' => $boar->breed,
                        'dam_ear_tag' => $boar->motherSow?->ear_tag ?? 'Unknown',
                        'sire_ear_tag' => $boar->sireBoar?->ear_tag ?? 'Unknown',
                    ],
                ];
            })
            ->all();
    }

    protected function availableUpdateEvents(ReproductionCycle $cycle): array
    {
        return match ($cycle->display_status) {
            ReproductionCycle::STATUS_SERVICED => [
                ReproductionCycleUpdate::EVENT_PREGNANCY_CHECKED => ReproductionCycleUpdate::eventOptions()[ReproductionCycleUpdate::EVENT_PREGNANCY_CHECKED],
            ],

            ReproductionCycle::STATUS_PREGNANT,
            ReproductionCycle::STATUS_DUE_SOON => [
                ReproductionCycleUpdate::EVENT_FARROWING_RECORDED => ReproductionCycleUpdate::eventOptions()[ReproductionCycleUpdate::EVENT_FARROWING_RECORDED],
            ],

            ReproductionCycle::STATUS_NOT_PREGNANT => [
                ReproductionCycleUpdate::EVENT_RETURNED_TO_HEAT => ReproductionCycleUpdate::eventOptions()[ReproductionCycleUpdate::EVENT_RETURNED_TO_HEAT],
                ReproductionCycleUpdate::EVENT_CYCLE_CLOSED => ReproductionCycleUpdate::eventOptions()[ReproductionCycleUpdate::EVENT_CYCLE_CLOSED],
            ],

            ReproductionCycle::STATUS_RETURNED_TO_HEAT => [
                ReproductionCycleUpdate::EVENT_CYCLE_CLOSED => ReproductionCycleUpdate::eventOptions()[ReproductionCycleUpdate::EVENT_CYCLE_CLOSED],
            ],

            ReproductionCycle::STATUS_FARROWED => [
                ReproductionCycleUpdate::EVENT_CYCLE_CLOSED => ReproductionCycleUpdate::eventOptions()[ReproductionCycleUpdate::EVENT_CYCLE_CLOSED],
            ],

            default => [],
        };
    }

    protected function supportsAttemptMetadata(): bool
    {
        return Schema::hasColumn('reproduction_cycle_updates', 'attempt_number');
    }
}
