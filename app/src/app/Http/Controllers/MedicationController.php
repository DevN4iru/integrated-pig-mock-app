<?php

namespace App\Http\Controllers;

use App\Models\Medication;
use App\Models\Pig;
use Illuminate\Http\Request;

class MedicationController extends Controller
{
    public function create(Pig $pig)
    {
        if ($pig->isOperationallyLocked()) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', $pig->operationalLockMessage('medication records'));
        }

        return view('medications.create', compact('pig'));
    }

    public function store(Request $request, Pig $pig)
    {
        if ($pig->isOperationallyLocked()) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', $pig->operationalLockMessage('medication records'));
        }

        $validated = $request->validate([
            'medication_name' => ['required', 'string', 'max:255'],
            'dosage' => ['required', 'string', 'max:255'],
            'administered_at' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['pig_id'] = $pig->id;

        Medication::create($validated);

        return redirect()
            ->route('pigs.show', $pig->id)
            ->with('success', 'Medication record added.');
    }

    public function edit(Pig $pig, Medication $medication)
    {
        abort_if($medication->pig_id !== $pig->id, 404);

        if ($pig->isOperationallyLocked()) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', $pig->operationalLockMessage('medication records'));
        }

        return view('medications.edit', compact('pig', 'medication'));
    }

    public function update(Request $request, Pig $pig, Medication $medication)
    {
        abort_if($medication->pig_id !== $pig->id, 404);

        if ($pig->isOperationallyLocked()) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', $pig->operationalLockMessage('medication records'));
        }

        $validated = $request->validate([
            'medication_name' => ['required', 'string', 'max:255'],
            'dosage' => ['required', 'string', 'max:255'],
            'administered_at' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $medication->update($validated);

        return redirect()
            ->route('pigs.show', $pig->id)
            ->with('success', 'Medication record updated.');
    }

    public function destroy(Pig $pig, Medication $medication)
    {
        abort_if($medication->pig_id !== $pig->id, 404);

        if ($pig->isOperationallyLocked()) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', $pig->operationalLockMessage('medication records'));
        }

        $medication->delete();

        return redirect()
            ->route('pigs.show', $pig->id)
            ->with('success', 'Medication record deleted.');
    }
}
