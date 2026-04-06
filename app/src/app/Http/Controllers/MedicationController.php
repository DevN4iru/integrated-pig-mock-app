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
            'medication_name' => ['required'],
            'dosage' => ['required'],
            'administered_at' => ['required', 'date'],
            'notes' => ['nullable'],
        ]);

        $validated['pig_id'] = $pig->id;

        Medication::create($validated);

        return redirect()
            ->route('pigs.show', $pig)
            ->with('success', 'Medication record added.');
    }
}