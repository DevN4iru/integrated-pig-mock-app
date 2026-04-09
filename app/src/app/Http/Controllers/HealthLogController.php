<?php

namespace App\Http\Controllers;

use App\Models\HealthLog;
use App\Models\Pig;
use Illuminate\Http\Request;
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
            'weight' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'log_date' => ['required', 'date'],
        ];
    }

    public function create(Pig $pig)
    {
        return view('health-logs.create', compact('pig'));
    }

    public function store(Request $request, Pig $pig)
    {
        $validated = $request->validate($this->rules());

        if ($validated['purpose'] === 'weight_update' && ($validated['weight'] === null || $validated['weight'] === '')) {
            return back()->withErrors([
                'weight' => 'Weight is required for a weight update.'
            ])->withInput();
        }

        // ✅ ALLOW WEIGHT REGRESSION (pigs can lose weight in real scenarios)

        $validated['pig_id'] = $pig->id;

        HealthLog::create($validated);

        return redirect()
            ->route('pigs.show', $pig)
            ->with('success', 'Health log added.');
    }

    public function edit(Pig $pig, HealthLog $healthLog)
    {
        abort_if($healthLog->pig_id !== $pig->id, 404);

        return view('health-logs.edit', compact('pig', 'healthLog'));
    }

    public function update(Request $request, Pig $pig, HealthLog $healthLog)
    {
        abort_if($healthLog->pig_id !== $pig->id, 404);

        $validated = $request->validate($this->rules());

        if ($validated['purpose'] === 'weight_update' && ($validated['weight'] === null || $validated['weight'] === '')) {
            return back()->withErrors([
                'weight' => 'Weight is required for a weight update.'
            ])->withInput();
        }

        $healthLog->update($validated);

        return redirect()
            ->route('pigs.show', $pig)
            ->with('success', 'Health log updated.');
    }

    public function destroy(Pig $pig, HealthLog $healthLog)
    {
        abort_if($healthLog->pig_id !== $pig->id, 404);

        $healthLog->delete();

        return redirect()
            ->route('pigs.show', $pig)
            ->with('success', 'Health log deleted.');
    }
}
