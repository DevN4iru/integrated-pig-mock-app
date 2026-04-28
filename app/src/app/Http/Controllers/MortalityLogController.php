<?php

namespace App\Http\Controllers;

use App\Models\MortalityLog;
use App\Models\Pig;
use Illuminate\Http\Request;

class MortalityLogController extends Controller
{
    protected function ensurePigCanReceiveMortality(Pig $pig)
    {
        if ($pig->trashed()) {
            return redirect()
                ->route('pigs.show', $pig)
                ->with('error', 'Cannot record mortality for an archived pig.');
        }

        if ($pig->sales()->exists()) {
            return redirect()
                ->route('pigs.show', $pig)
                ->with('error', 'Cannot record mortality for a pig that already has a sale record.');
        }

        if ($pig->mortalityLogs()->exists()) {
            return redirect()
                ->route('pigs.show', $pig)
                ->with('error', 'Cannot record mortality for a pig that already has a mortality record.');
        }

        return null;
    }

    public function create(Pig $pig)
    {
        if ($redirect = $this->ensurePigCanReceiveMortality($pig)) {
            return $redirect;
        }

        return view('mortality-logs.create', compact('pig'));
    }

    public function store(Request $request, Pig $pig)
    {
        if ($redirect = $this->ensurePigCanReceiveMortality($pig)) {
            return $redirect;
        }

        $validated = $request->validate([
            'death_date' => ['required', 'date'],
            'cause' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $weightAtDeath = (float) ($pig->computed_weight ?? 0);
        $lossValue = (float) ($pig->asset_value ?? 0);

        MortalityLog::create([
            'pig_id' => $pig->id,
            'death_date' => $validated['death_date'],
            'cause' => trim((string) $validated['cause']),
            'notes' => isset($validated['notes']) && trim((string) $validated['notes']) !== ''
                ? trim((string) $validated['notes'])
                : null,
            'weight_at_death' => $weightAtDeath,
            'price_per_kg_at_death' => 0,
            'loss_value' => $lossValue,
        ]);

        return redirect()
            ->route('pigs.show', $pig)
            ->with('success', 'Mortality recorded.');
    }

    public function edit(Pig $pig, MortalityLog $mortalityLog)
    {
        abort_if($mortalityLog->pig_id !== $pig->id, 404);

        return view('mortality-logs.edit', compact('pig', 'mortalityLog'));
    }

    public function update(Request $request, Pig $pig, MortalityLog $mortalityLog)
    {
        abort_if($mortalityLog->pig_id !== $pig->id, 404);

        $validated = $request->validate([
            'death_date' => ['required', 'date'],
            'cause' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $mortalityLog->update([
            'death_date' => $validated['death_date'],
            'cause' => trim((string) $validated['cause']),
            'notes' => isset($validated['notes']) && trim((string) $validated['notes']) !== ''
                ? trim((string) $validated['notes'])
                : null,
        ]);

        return redirect()
            ->route('pigs.show', $pig)
            ->with('success', 'Mortality record updated.');
    }

    public function destroy(Pig $pig, MortalityLog $mortalityLog)
    {
        abort_if($mortalityLog->pig_id !== $pig->id, 404);

        $mortalityLog->delete();

        return redirect()
            ->route('pigs.show', $pig)
            ->with('success', 'Mortality record deleted.');
    }
}
