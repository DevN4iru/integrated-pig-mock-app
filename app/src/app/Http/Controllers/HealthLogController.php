<?php

namespace App\Http\Controllers;

use App\Models\FarmSetting;
use App\Models\HealthLog;
use App\Models\Pig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HealthLogController extends Controller
{
    private function validatedPayload(Request $request): array
    {
        $validated = $request->validate([
            'weight' => ['required', 'numeric', 'gt:0'],
            'log_date' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['purpose'] = 'weight_update';
        $validated['condition'] = 'Weight update';
        $validated['notes'] = isset($validated['notes']) ? trim((string) $validated['notes']) : null;
        $validated['notes'] = $validated['notes'] === '' ? null : $validated['notes'];

        return $validated;
    }

    private function syncPigWeightAndAssetSnapshots(Pig $pig): void
    {
        $latestWeightLog = $pig->healthLogs()
            ->where('purpose', 'weight_update')
            ->whereNotNull('weight')
            ->orderByDesc('log_date')
            ->orderByDesc('id')
            ->first();

        $currentWeight = $latestWeightLog
            ? (float) $latestWeightLog->weight
            : 0.0;

        $pig->latest_weight = $currentWeight;
        $pig->asset_value = $currentWeight * FarmSetting::currentPricePerKg();
        $pig->save();
    }

    public function create(Pig $pig)
    {
        if ($pig->isOperationallyLocked()) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', $pig->operationalLockMessage('weight history'));
        }

        return view('health-logs.create', compact('pig'));
    }

    public function store(Request $request, Pig $pig)
    {
        if ($pig->isOperationallyLocked()) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', $pig->operationalLockMessage('weight history'));
        }

        $validated = $this->validatedPayload($request);
        $validated['pig_id'] = $pig->id;

        DB::transaction(function () use ($validated, $pig): void {
            HealthLog::create($validated);
            $this->syncPigWeightAndAssetSnapshots($pig->fresh());
        });

        return redirect()
            ->route('pigs.show', $pig->id)
            ->with('success', 'Weight updated.');
    }

    public function edit(Pig $pig, HealthLog $healthLog)
    {
        abort_if($healthLog->pig_id !== $pig->id, 404);

        if ($pig->isOperationallyLocked()) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', $pig->operationalLockMessage('weight history'));
        }

        if ($healthLog->purpose !== 'weight_update') {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', 'This record is hidden in the simplified client view.');
        }

        return view('health-logs.edit', compact('pig', 'healthLog'));
    }

    public function update(Request $request, Pig $pig, HealthLog $healthLog)
    {
        abort_if($healthLog->pig_id !== $pig->id, 404);

        if ($pig->isOperationallyLocked()) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', $pig->operationalLockMessage('weight history'));
        }

        if ($healthLog->purpose !== 'weight_update') {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', 'This record is hidden in the simplified client view.');
        }

        $validated = $this->validatedPayload($request);

        DB::transaction(function () use ($validated, $healthLog, $pig): void {
            $healthLog->update($validated);
            $this->syncPigWeightAndAssetSnapshots($pig->fresh());
        });

        return redirect()
            ->route('pigs.show', $pig->id)
            ->with('success', 'Weight record updated.');
    }

    public function destroy(Pig $pig, HealthLog $healthLog)
    {
        abort_if($healthLog->pig_id !== $pig->id, 404);

        if ($pig->isOperationallyLocked()) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', $pig->operationalLockMessage('weight history'));
        }

        if ($healthLog->purpose !== 'weight_update') {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', 'This record is hidden in the simplified client view.');
        }

        DB::transaction(function () use ($healthLog, $pig): void {
            $healthLog->delete();
            $this->syncPigWeightAndAssetSnapshots($pig->fresh());
        });

        return redirect()
            ->route('pigs.show', $pig->id)
            ->with('success', 'Weight record deleted.');
    }
}
