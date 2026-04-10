<?php

namespace App\Http\Controllers;

use App\Models\Pig;
use App\Models\Vaccination;
use Illuminate\Http\Request;

class VaccinationController extends Controller
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
            return 'Archived pigs cannot receive vaccination changes. Restore the pig first.';
        }

        if ($pig->mortalityLogs()->exists()) {
            return 'Dead pigs cannot receive vaccination changes.';
        }

        if ($pig->sales()->exists()) {
            return 'Sold pigs cannot receive vaccination changes.';
        }

        return 'This pig is locked for vaccination changes.';
    }

    public function create(Pig $pig)
    {
        if ($this->isLocked($pig)) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', $this->lockedMessage($pig));
        }

        return view('vaccinations.create', compact('pig'));
    }

    public function store(Request $request, Pig $pig)
    {
        if ($this->isLocked($pig)) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', $this->lockedMessage($pig));
        }

        $validated = $request->validate([
            'vaccine_name' => ['required', 'string', 'max:255'],
            'dose' => ['required', 'string', 'max:255'],
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

        if ($this->isLocked($pig)) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', $this->lockedMessage($pig));
        }

        return view('vaccinations.edit', compact('pig', 'vaccination'));
    }

    public function update(Request $request, Pig $pig, Vaccination $vaccination)
    {
        abort_if($vaccination->pig_id !== $pig->id, 404);

        if ($this->isLocked($pig)) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', $this->lockedMessage($pig));
        }

        $validated = $request->validate([
            'vaccine_name' => ['required', 'string', 'max:255'],
            'dose' => ['required', 'string', 'max:255'],
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

        if ($this->isLocked($pig)) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', $this->lockedMessage($pig));
        }

        $vaccination->delete();

        return redirect()
            ->route('pigs.show', $pig->id)
            ->with('success', 'Vaccination record deleted.');
    }
}
