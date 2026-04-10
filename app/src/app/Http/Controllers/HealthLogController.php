<?php

namespace App\Http\Controllers;

use App\Models\FarmSetting;
use App\Models\HealthLog;
use App\Models\Pig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class HealthLogController extends Controller
{
    private function rules(): array
    {
        return [
            'purpose' => ['required', Rule::in([
                'weight_update',
                'sick',
                'recovered',
                'checkup',
                'injury',
                'observation',
            ])],
            'condition' => ['required', 'string', 'max:255'],
            'weight' => ['nullable', 'numeric', 'gt:0'],
            'notes' => ['nullable', 'string'],
            'log_date' => ['required', 'date', 'before_or_equal:today'],
        ];
    }

    private function isLocked(Pig $pig): bool
    {
        return $pig->trashed()
            || $pig->mortalityLogs()->exists()
            || $pig->sales()->exists();
    }

    private function lockedMessage(Pig $pig): string
    {
        if ($pig->trashed()) {
            return 'Archived pigs cannot receive health log changes. Restore the pig first.';
        }

        if ($pig->mortalityLogs()->exists()) {
            return 'Dead pigs cannot receive health log changes.';
        }

        if ($pig->sales()->exists()) {
            return 'Sold pigs cannot receive health log changes.';
        }

        return 'This pig is locked for health log changes.';
    }

    private function validatedPayload(Request $request): array
    {
        $validated = $request->validate($this->rules());

        if ($validated['purpose'] === 'weight_update' && ($validated['weight'] === null || $validated['weight'] === '')) {
            return back()
                ->withErrors(['weight' => 'Weight is required for a weight update.'])
                ->withInput()
                ->throwResponse();
        }

        if ($validated['purpose'] !== 'weight_update') {
            $validated['weight'] = null;
        }

        $validated['condition'] = trim((string) $validated['condition']);
        $validated['notes'] = isset($validated['notes']) ? trim((string) $validated['notes']) : null;
        $validated['notes'] = $validated['notes'] === '' ? null : $validated['notes'];

        return $validated;
    }

    private function syncPigAssetSnapshot(Pig $pig): void
    {
        $latestWeightLog = $pig->healthLogs()
            ->where('purpose', 'weight_update')
            ->whereNotNull('weight')
            ->orderByDesc('log_date')
            ->orderByDesc('id')
            ->first();

        $currentWeight = $latestWeightLog?->weight ?? $pig->latest_weight ?? 0;

        $pig->asset_value = (float) $currentWeight * FarmSetting::currentPricePerKg();
        $pig->save();
    }

    public function create(Pig $pig)
    {
        if ($this->isLocked($pig)) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', $this->lockedMessage($pig));
        }

        return view('health-logs.create', compact('pig'));
    }

    public function store(Request $request, Pig $pig)
    {
        if ($this->isLocked($pig)) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', $this->lockedMessage($pig));
        }

        $validated = $this->validatedPayload($request);
        $validated['pig_id'] = $pig->id;

        DB::transaction(function () use ($validated, $pig): void {
            HealthLog::create($validated);
            $this->syncPigAssetSnapshot($pig->fresh());
        });

        return redirect()
            ->route('pigs.show', $pig->id)
            ->with('success', 'Health log added.');
    }

    public function edit(Pig $pig, HealthLog $healthLog)
    {
        abort_if($healthLog->pig_id !== $pig->id, 404);

        if ($this->isLocked($pig)) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', $this->lockedMessage($pig));
        }

        return view('health-logs.edit', compact('pig', 'healthLog'));
    }

    public function update(Request $request, Pig $pig, HealthLog $healthLog)
    {
        abort_if($healthLog->pig_id !== $pig->id, 404);

        if ($this->isLocked($pig)) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', $this->lockedMessage($pig));
        }

        $validated = $this->validatedPayload($request);

        DB::transaction(function () use ($validated, $healthLog, $pig): void {
            $healthLog->update($validated);
            $this->syncPigAssetSnapshot($pig->fresh());
        });

        return redirect()
            ->route('pigs.show', $pig->id)
            ->with('success', 'Health log updated.');
    }

    public function destroy(Pig $pig, HealthLog $healthLog)
    {
        abort_if($healthLog->pig_id !== $pig->id, 404);

        if ($this->isLocked($pig)) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', $this->lockedMessage($pig));
        }

        DB::transaction(function () use ($healthLog, $pig): void {
            $healthLog->delete();
            $this->syncPigAssetSnapshot($pig->fresh());
        });

        return redirect()
            ->route('pigs.show', $pig->id)
            ->with('success', 'Health log deleted.');
    }
}
