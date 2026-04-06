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
            'condition' => ['required'],
            'notes' => ['nullable'],
            'log_date' => ['required', 'date'],
        ]);

        $validated['pig_id'] = $pig->id;

        HealthLog::create($validated);

        return redirect()
            ->route('pigs.show', $pig)
            ->with('success', 'Health log added.');
    }
}