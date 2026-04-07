<?php

namespace App\Http\Controllers;

use App\Models\Pig;
use App\Models\MortalityLog;
use Illuminate\Http\Request;

class MortalityLogController extends Controller
{
    public function create(Pig $pig)
    {
        if ($pig->sales()->exists()) {
            return redirect()
                ->route('pigs.show', $pig)
                ->with('error', 'Cannot record mortality for a pig that already has a sale record.');
        }

        return view('mortality-logs.create', compact('pig'));
    }

    public function store(Request $request, Pig $pig)
    {
        if ($pig->sales()->exists()) {
            return redirect()
                ->route('pigs.show', $pig)
                ->with('error', 'Cannot record mortality for a pig that already has a sale record.');
        }

        $validated = $request->validate([
            'death_date' => ['required', 'date'],
            'cause' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['pig_id'] = $pig->id;

        MortalityLog::create($validated);

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

        $mortalityLog->update($validated);

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