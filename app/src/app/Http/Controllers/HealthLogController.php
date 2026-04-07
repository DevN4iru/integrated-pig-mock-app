<?php

namespace App\Http\Controllers;

use App\Models\HealthLog;
use App\Models\Pig;
use Illuminate\Http\Request;

class HealthLogController extends Controller
{
    public function create(Pig $pig)
    {
        return view('health-logs.create', compact('pig'));
    }

    public function store(Request $request, Pig $pig)
    {
        $validated = $request->validate([
            'condition' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'log_date' => ['required', 'date'],
        ]);

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

        $validated = $request->validate([
            'condition' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'log_date' => ['required', 'date'],
        ]);

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