<?php

namespace App\Http\Controllers;

use App\Models\FarmSetting;
use App\Models\Pen;
use App\Models\Pig;
use App\Models\PigTransfer;
use App\Models\ReproductionCycle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PigController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', 'all');
        $source = (string) $request->query('source', 'all');
        $penFilter = (string) $request->query('pen', 'all');

        $pensForFilter = Pen::withCount(['activePigs as pigs_count'])
            ->get()
            ->sortBy(fn ($pen) => $pen->sortKey())
            ->values();

        $pigs = Pig::withTrashed()
            ->with([
                'pen',
                'healthLogs',
                'sales',
                'mortalityLogs',
                'feedLogs',
                'medications',
                'vaccinations',
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('ear_tag', 'like', '%' . $search . '%')
                        ->orWhere('breed', 'like', '%' . $search . '%')
                        ->orWhere('sex', 'like', '%' . $search . '%')
                        ->orWhere('pig_source', 'like', '%' . $search . '%')
                        ->orWhere('pen_location', 'like', '%' . $search . '%')
                        ->orWhere('age', 'like', '%' . $search . '%')
                        ->orWhereHas('pen', function ($penQuery) use ($search) {
                            $penQuery->where('name', 'like', '%' . $search . '%');
                        });
                });
            })
            ->when($source !== 'all', function ($query) use ($source) {
                $query->where('pig_source', $source);
            })
            ->when($penFilter !== 'all', function ($query) use ($penFilter) {
                $query->where('pen_id', $penFilter);
            })
            ->latest()
            ->get();

        $activePigs = $pigs->filter(function ($pig) {
            return !$pig->trashed()
                && $pig->mortalityLogs->isEmpty()
                && $pig->sales->isEmpty();
        })->values();

        $soldPigs = $pigs->filter(function ($pig) {
            return !$pig->trashed()
                && $pig->mortalityLogs->isEmpty()
                && $pig->sales->isNotEmpty();
        })->values();

        $deadPigs = $pigs->filter(function ($pig) {
            return !$pig->trashed()
                && $pig->mortalityLogs->isNotEmpty();
        })->values();

        $archivedPigs = $pigs->filter(function ($pig) {
            return $pig->trashed();
        })->values();

        if ($status === 'active') {
            $soldPigs = collect();
            $deadPigs = collect();
            $archivedPigs = collect();
        } elseif ($status === 'sold') {
            $activePigs = collect();
            $deadPigs = collect();
            $archivedPigs = collect();
        } elseif ($status === 'dead') {
            $activePigs = collect();
            $soldPigs = collect();
            $archivedPigs = collect();
        } elseif ($status === 'archived') {
            $activePigs = collect();
            $soldPigs = collect();
            $deadPigs = collect();
        }

        $activePenGroups = $activePigs
            ->groupBy(fn ($pig) => $pig->pen?->id ? 'pen-' . $pig->pen->id : 'unassigned')
            ->map(function ($group) {
                $sample = $group->first();
                $pen = $sample?->pen;

                return [
                    'pen' => $pen,
                    'title' => $pen?->name ?? 'Unassigned',
                    'type' => $pen?->type,
                    'pigs' => $group->sortBy(fn ($pig) => strtolower((string) $pig->ear_tag))->values(),
                ];
            })
            ->sortBy(fn ($group) => $group['pen']?->sortKey() ?? '9999-9999-unassigned')
            ->values();

        $destinationPens = $pensForFilter;
        $reasonOptions = PigTransfer::reasonOptions();
        $pricePerKg = FarmSetting::currentPricePerKg();

        return view('pigs.index', compact(
            'activePigs',
            'soldPigs',
            'deadPigs',
            'archivedPigs',
            'activePenGroups',
            'search',
            'status',
            'source',
            'penFilter',
            'pensForFilter',
            'destinationPens',
            'reasonOptions',
            'pricePerKg'
        ));
    }

    public function create()
    {
        $pens = Pen::withCount(['activePigs as pigs_count'])
            ->get()
            ->sortBy(fn ($pen) => $pen->sortKey())
            ->values();

        $pricePerKg = FarmSetting::currentPricePerKg();

        return view('pigs.create', compact('pens', 'pricePerKg'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ear_tag' => ['required', 'string', 'max:255', 'unique:pigs,ear_tag'],
            'breed' => ['required', 'string', 'max:255'],
            'sex' => ['required', Rule::in(['male', 'female'])],
            'pen_id' => ['required', 'exists:pens,id'],
            'pig_source' => ['required', Rule::in(['birthed', 'purchased'])],
            'age_value' => ['required', 'numeric', 'min:0'],
            'age_unit' => ['required', Rule::in(['days', 'weeks', 'months'])],
            'date_added' => ['required', 'date'],
            'latest_weight' => ['required', 'numeric', 'min:0'],
        ]);

        $pen = Pen::withCount(['activePigs as pigs_count'])->findOrFail($validated['pen_id']);

        if ($pen->pigs_count >= $pen->capacity) {
            return back()->withErrors(['pen_id' => 'Pen is FULL'])->withInput();
        }

        $validated['age'] = $this->convertAgeToDays(
            (float) $validated['age_value'],
            (string) $validated['age_unit']
        );

        unset($validated['age_value'], $validated['age_unit']);

        $validated['pen_location'] = $pen->name;
        $validated['asset_value'] = (float) $validated['latest_weight'] * FarmSetting::currentPricePerKg();

        Pig::create($validated);

        return redirect()->route('pigs.index')->with('success', 'Pig added successfully.');
    }

    public function createBornBatch(ReproductionCycle $reproductionCycle)
    {
        $reproductionCycle->load(['sow.pen', 'boar'])->loadCount('bornPiglets');

        $this->assertCycleCanRegisterBornPiglets($reproductionCycle);

        $pens = Pen::withCount(['activePigs as pigs_count'])
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $recommendedPens = $pens
            ->filter(fn ($pen) => in_array($pen->type, [
                Pen::TYPE_NURSERY,
                Pen::TYPE_FARROWING,
                Pen::TYPE_GROWER,
            ], true))
            ->values();

        $pricePerKg = FarmSetting::currentPricePerKg();
        $pigletCount = (int) $reproductionCycle->born_alive;

        return view('pigs.create-born-batch', [
            'cycle' => $reproductionCycle,
            'pens' => $pens,
            'recommendedPens' => $recommendedPens,
            'pricePerKg' => $pricePerKg,
            'pigletCount' => $pigletCount,
        ]);
    }

    public function storeBornBatch(Request $request, ReproductionCycle $reproductionCycle)
    {
        $reproductionCycle->load(['sow.pen', 'boar'])->loadCount('bornPiglets');

        $this->assertCycleCanRegisterBornPiglets($reproductionCycle);

        $pigletCount = (int) $reproductionCycle->born_alive;

        $validated = $request->validate([
            'piglets' => ['required', 'array', 'size:' . $pigletCount],
            'piglets.*.ear_tag' => ['required', 'string', 'max:255', 'distinct', 'unique:pigs,ear_tag'],
            'piglets.*.breed' => ['required', 'string', 'max:255'],
            'piglets.*.sex' => ['required', Rule::in(['male', 'female', 'undetermined'])],
            'piglets.*.pen_id' => ['required', 'integer', 'exists:pens,id'],
            'piglets.*.latest_weight' => ['required', 'numeric', 'min:0'],
        ]);

        $requestedPenIds = collect($validated['piglets'])
            ->pluck('pen_id')
            ->unique()
            ->values();

        $pens = Pen::withCount(['activePigs as pigs_count'])
            ->whereIn('id', $requestedPenIds)
            ->get()
            ->keyBy('id');

        $capacityErrors = [];
        $penRequestCounts = collect($validated['piglets'])->countBy('pen_id');

        foreach ($penRequestCounts as $penId => $requestedCount) {
            $pen = $pens->get((int) $penId);

            if (!$pen) {
                $capacityErrors['piglets.0.pen_id'] = 'One or more selected pens could not be loaded.';
                continue;
            }

            if ($pen->availableSlots() < (int) $requestedCount) {
                $capacityErrors["pen_{$penId}"] = "{$pen->name} does not have enough available slots for {$requestedCount} piglet(s).";
            }
        }

        if (!empty($capacityErrors)) {
            throw ValidationException::withMessages($capacityErrors);
        }

        $pricePerKg = FarmSetting::currentPricePerKg();
        $birthDate = $reproductionCycle->actual_farrow_date->toDateString();

        DB::transaction(function () use ($validated, $pens, $pricePerKg, $birthDate, $reproductionCycle): void {
            if ($reproductionCycle->bornPiglets()->exists()) {
                throw ValidationException::withMessages([
                    'piglets' => 'This litter already has registered piglets. Duplicate registration is blocked.',
                ]);
            }

            foreach ($validated['piglets'] as $piglet) {
                $pen = $pens->get((int) $piglet['pen_id']);
                $weight = (float) $piglet['latest_weight'];

                Pig::create([
                    'ear_tag' => trim((string) $piglet['ear_tag']),
                    'breed' => trim((string) $piglet['breed']),
                    'sex' => trim((string) $piglet['sex']),
                    'pen_id' => $pen->id,
                    'pen_location' => $pen->name,
                    'pig_source' => 'birthed',
                    'age' => 0,
                    'mother_sow_id' => $reproductionCycle->sow_id,
                    'reproduction_cycle_id' => $reproductionCycle->id,
                    'date_added' => $birthDate,
                    'latest_weight' => $weight,
                    'asset_value' => $weight * $pricePerKg,
                ]);
            }
        });

        return redirect()
            ->route('reproduction-cycles.show', $reproductionCycle)
            ->with('success', "{$pigletCount} born piglet(s) registered successfully.");
    }

    public function show($pig)
    {
        $pig = Pig::withTrashed()
            ->with([
                'pen',
                'healthLogs',
                'medications',
                'vaccinations',
                'mortalityLogs',
                'sales',
                'feedLogs',
                'transfers.fromPen',
                'transfers.toPen',
                'motherSow',
                'birthCycle',
            ])
            ->findOrFail($pig);

        return view('pigs.show', compact('pig'));
    }

    public function edit(Request $request, Pig $pig)
    {
        if ($pig->trashed()) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', 'Archived pigs cannot be edited. Restore the pig first.');
        }

        if ($request->query('code') !== '12345') {
            return redirect()
                ->route('pigs.index')
                ->with('error', 'Access denied. Type the correct edit code first.');
        }

        $pricePerKg = FarmSetting::currentPricePerKg();

        return view('pigs.edit', compact('pig', 'pricePerKg'));
    }

    public function update(Request $request, Pig $pig)
    {
        if ($pig->trashed()) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', 'Archived pigs cannot be updated. Restore the pig first.');
        }

        $validated = $request->validate([
            'ear_tag' => ['required', 'string', 'max:255', 'unique:pigs,ear_tag,' . $pig->id],
            'breed' => ['required', 'string', 'max:255'],
            'sex' => ['required', Rule::in(['male', 'female'])],
            'pig_source' => ['required', Rule::in(['birthed', 'purchased'])],
            'age_value' => ['required', 'numeric', 'min:0'],
            'age_unit' => ['required', Rule::in(['days', 'weeks', 'months'])],
            'date_added' => ['required', 'date'],
            'latest_weight' => ['required', 'numeric', 'min:0'],
        ]);

        $validated['age'] = $this->convertAgeToDays(
            (float) $validated['age_value'],
            (string) $validated['age_unit']
        );

        unset($validated['age_value'], $validated['age_unit']);

        $validated['pen_id'] = $pig->pen_id;
        $validated['pen_location'] = $pig->pen?->name ?? $pig->pen_location;
        $validated['asset_value'] = (float) $validated['latest_weight'] * FarmSetting::currentPricePerKg();

        $pig->update($validated);

        return redirect()->route('pigs.show', $pig->id)->with('success', 'Pig updated successfully.');
    }

    public function destroy(Pig $pig)
    {
        if ($pig->trashed()) {
            return redirect()->route('pigs.index')->with('error', 'Pig is already archived.');
        }

        $pig->delete();

        return redirect()->route('pigs.index')->with('success', 'Pig archived successfully.');
    }

    public function restore($pig)
    {
        $pig = Pig::withTrashed()->findOrFail($pig);

        if (!$pig->trashed()) {
            return redirect()->route('pigs.index')->with('error', 'Pig is already active.');
        }

        $pig->restore();

        return redirect()->route('pigs.index')->with('success', 'Pig restored successfully.');
    }

    public function forceDelete(Request $request, $pig)
    {
        $pig = Pig::withTrashed()->findOrFail($pig);

        if ($request->input('code') !== '12345') {
            return redirect()
                ->route('pigs.index')
                ->with('error', 'Permanent delete failed. Wrong challenge code.');
        }

        $pig->forceDelete();

        return redirect()->route('pigs.index')->with('success', 'Pig permanently deleted successfully.');
    }

    public function removeFromRecords(Request $request, $pig)
    {
        $pig = Pig::withTrashed()
            ->with([
                'healthLogs',
                'sales',
                'mortalityLogs',
                'feedLogs',
                'medications',
                'vaccinations',
                'transfers',
            ])
            ->findOrFail($pig);

        if ($request->input('code') !== 'REMOVE') {
            return redirect()
                ->route('pigs.index')
                ->with('error', 'Removal failed. Wrong security code.');
        }

        DB::transaction(function () use ($pig): void {
            $pig->healthLogs()->delete();
            $pig->sales()->delete();
            $pig->mortalityLogs()->delete();
            $pig->feedLogs()->delete();
            $pig->medications()->delete();
            $pig->vaccinations()->delete();
            $pig->transfers()->delete();

            $pig->forceDelete();
        });

        return redirect()
            ->route('pigs.index')
            ->with('success', 'Pig and all related records were permanently removed.');
    }

    protected function convertAgeToDays(float $value, string $unit): int
    {
        $value = max(0, $value);

        return match ($unit) {
            'weeks' => (int) round($value * 7),
            'months' => (int) round($value * 30),
            default => (int) round($value),
        };
    }

    protected function assertCycleCanRegisterBornPiglets(ReproductionCycle $reproductionCycle): void
    {
        if (!$reproductionCycle->actual_farrow_date) {
            abort(422, 'Born piglets can only be registered after actual farrowing has been recorded.');
        }

        if ((int) ($reproductionCycle->born_alive ?? 0) <= 0) {
            abort(422, 'This breeding case has no born-alive piglets to register.');
        }

        if ($reproductionCycle->pregnancy_result !== ReproductionCycle::PREGNANCY_RESULT_PREGNANT) {
            abort(422, 'Born piglets can only be registered from a successful pregnant-to-farrowed breeding case.');
        }

        if ($reproductionCycle->bornPiglets()->exists()) {
            abort(422, 'Born piglets for this litter were already registered. Duplicate registration is blocked.');
        }
    }
}