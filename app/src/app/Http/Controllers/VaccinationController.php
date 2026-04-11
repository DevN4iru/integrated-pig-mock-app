<?php

namespace App\Http\Controllers;

use App\Models\Pig;
use App\Models\Vaccination;
use Illuminate\Http\Request;

class VaccinationController extends Controller
{
    public function create(Pig $pig)
    {
        if ($pig->isOperationallyLocked()) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', $pig->operationalLockMessage('vaccination records'));
        }

        return view('vaccinations.create', compact('pig'));
    }

    public function store(Request $request, Pig $pig)
    {
        if ($pig->isOperationallyLocked()) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', $pig->operationalLockMessage('vaccination records'));
        }

        $validated = $request->validate([
            'vaccine_name' => ['required', 'string', 'max:255'],
            'dose' => ['required', 'string', 'max:255'],
            'cost' => ['required', 'numeric', 'min:0'],
            'vaccinated_at' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['pig_id'] = $pig->id;

        Vaccination::create($validated);

        return redirect()
            ->route('pigs.show', $pig->id)
            ->with('success', 'Vaccination record added.');
    }

    public function edit(Pig $pig, Vaccination $vaccination)
    {
        abort_if($vaccination->pig_id !== $pig->id, 404);

        if ($pig->isOperationallyLocked()) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', $pig->operationalLockMessage('vaccination records'));
        }

        return view('vaccinations.edit', compact('pig', 'vaccination'));
    }

    public function update(Request $request, Pig $pig, Vaccination $vaccination)
    {
        abort_if($vaccination->pig_id !== $pig->id, 404);

        if ($pig->isOperationallyLocked()) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', $pig->operationalLockMessage('vaccination records'));
        }

        $validated = $request->validate([
            'vaccine_name' => ['required', 'string', 'max:255'],
            'dose' => ['required', 'string', 'max:255'],
            'cost' => ['required', 'numeric', 'min:0'],
            'vaccinated_at' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $vaccination->update($validated);

        return redirect()
            ->route('pigs.show', $pig->id)
            ->with('success', 'Vaccination record updated.');
    }

    public function destroy(Pig $pig, Vaccination $vaccination)
    {
        abort_if($vaccination->pig_id !== $pig->id, 404);

        if ($pig->isOperationallyLocked()) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', $pig->operationalLockMessage('vaccination records'));
        }

        $vaccination->delete();

        return redirect()
            ->route('pigs.show', $pig->id)
            ->with('success', 'Vaccination record deleted.');
    }
}
