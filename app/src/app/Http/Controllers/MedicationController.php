<?php

namespace App\Http\Controllers;

use App\Models\Pig;
use App\Models\Medication;
use Illuminate\Http\Request;

class MedicationController extends Controller
{
    public function create(Pig $pig)
    {
        return view('medications.create', compact('pig'));
    }

    public function store(Request $request, Pig $pig)
    {
        $validated = $request->validate([
            'medication_name' => ['required', 'string', 'max:255'],
            'dosage' => ['required', 'string', 'max:255'],
            'administered_at' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['pig_id'] = $pig->id;

        Medication::create($validated);

        return redirect()
            ->route('pigs.show', $pig)
            ->with('success', 'Medication record added.');
    }

    public function edit(Pig $pig, Medication $medication)
    {
        abort_if($medication->pig_id !== $pig->id, 404);

        return view('medications.edit', compact('pig', 'medication'));
    }

    public function update(Request $request, Pig $pig, Medication $medication)
    {
        abort_if($medication->pig_id !== $pig->id, 404);

        $validated = $request->validate([
            'medication_name' => ['required', 'string', 'max:255'],
            'dosage' => ['required', 'string', 'max:255'],
            'administered_at' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $medication->update($validated);

        return redirect()
            ->route('pigs.show', $pig)
            ->with('success', 'Medication record updated.');
    }

    public function destroy(Pig $pig, Medication $medication)
    {
        abort_if($medication->pig_id !== $pig->id, 404);

        $medication->delete();

        return redirect()
            ->route('pigs.show', $pig)
            ->with('success', 'Medication record deleted.');
    }
}