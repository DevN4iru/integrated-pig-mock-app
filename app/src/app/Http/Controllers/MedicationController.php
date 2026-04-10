<?php

namespace App\Http\Controllers;

use App\Models\Medication;
use App\Models\Pig;
use Illuminate\Http\Request;

class MedicationController extends Controller
{
    private function isLocked(Pig $pig): bool
    {
        return $pig->trashed()
            || $pig->mortalityLogs()->exists()
            || $pig->sales()->exists();
    }

    private function lockedMessage(Pig $pig): string
    {
        if ($pig->trashed()) {
            return 'Archived pigs cannot receive medication changes. Restore the pig first.';
        }

        if ($pig->mortalityLogs()->exists()) {
            return 'Dead pigs cannot receive medication changes.';
        }

        if ($pig->sales()->exists()) {
            return 'Sold pigs cannot receive medication changes.';
        }

        return 'This pig is locked for medication changes.';
    }

    public function create(Pig $pig)
    {
        if ($this->isLocked($pig)) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', $this->lockedMessage($pig));
        }

        return view('medications.create', compact('pig'));
    }

    public function store(Request $request, Pig $pig)
    {
        if ($this->isLocked($pig)) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', $this->lockedMessage($pig));
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

        if ($this->isLocked($pig)) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', $this->lockedMessage($pig));
        }

        return view('medications.edit', compact('pig', 'medication'));
    }

    public function update(Request $request, Pig $pig, Medication $medication)
    {
        abort_if($medication->pig_id !== $pig->id, 404);

        if ($this->isLocked($pig)) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', $this->lockedMessage($pig));
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

        if ($this->isLocked($pig)) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', $this->lockedMessage($pig));
        }

        $medication->delete();

        return redirect()
            ->route('pigs.show', $pig->id)
            ->with('success', 'Medication record deleted.');
    }
}
