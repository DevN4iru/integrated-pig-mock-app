<?php

namespace App\Http\Controllers;

use App\Models\Pig;
use App\Models\Vaccination;
use Illuminate\Http\Request;

class VaccinationController extends Controller
{
    public function create(Pig $pig)
    {
        return view('vaccinations.create', compact('pig'));
    }

    public function store(Request $request, Pig $pig)
    {
        $validated = $request->validate([
            'vaccine_name' => ['required'],
            'dose' => ['required'],
            'vaccinated_at' => ['required', 'date'],
            'notes' => ['nullable'],
        ]);

        $validated['pig_id'] = $pig->id;

        Vaccination::create($validated);

        return redirect()
            ->route('pigs.show', $pig)
            ->with('success', 'Vaccination record added.');
    }
}