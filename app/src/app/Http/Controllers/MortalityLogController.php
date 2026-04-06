<?php

namespace App\Http\Controllers;

use App\Models\Pig;
use App\Models\MortalityLog;
use Illuminate\Http\Request;

class MortalityLogController extends Controller
{
    public function create(Pig $pig)
    {
        return view('mortality-logs.create', compact('pig'));
    }

    public function store(Request $request, Pig $pig)
    {
        $validated = $request->validate([
            'death_date' => ['required', 'date'],
            'cause' => ['required'],
            'notes' => ['nullable'],
        ]);

        $validated['pig_id'] = $pig->id;

        MortalityLog::create($validated);

        return redirect()->route('pigs.show', $pig)
            ->with('success', 'Mortality recorded.');
    }
}