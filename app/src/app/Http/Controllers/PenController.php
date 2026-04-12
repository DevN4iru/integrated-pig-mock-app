<?php

namespace App\Http\Controllers;

use App\Models\Pen;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PenController extends Controller
{
    public function index()
    {
        $pens = Pen::withCount('pigs')
            ->orderBy('name')
            ->get();

        return view('pens.index', compact('pens'));
    }

    public function create()
    {
        $penTypes = Pen::typeOptions();

        return view('pens.create', compact('penTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:pens,name'],
            'type' => ['required', Rule::in(Pen::typeOptions())],
            'capacity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['notes'] = isset($validated['notes']) && trim((string) $validated['notes']) !== ''
            ? trim((string) $validated['notes'])
            : null;

        Pen::create($validated);

        return redirect()->route('pens.index')->with('success', 'Pen added successfully.');
    }

    public function edit(Request $request, Pen $pen)
    {
        if ($request->query('code') !== '12345') {
            return redirect()
                ->route('pens.index')
                ->with('error', 'Access denied. Type the correct edit code first.');
        }

        $penTypes = Pen::typeOptions();

        return view('pens.edit', compact('pen', 'penTypes'));
    }

    public function update(Request $request, Pen $pen)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:pens,name,' . $pen->id],
            'type' => ['required', Rule::in(Pen::typeOptions())],
            'capacity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($validated['capacity'] < $pen->pigs()->count()) {
            return back()->withErrors([
                'capacity' => 'Capacity cannot be lower than current occupied count.'
            ])->withInput();
        }

        $validated['notes'] = isset($validated['notes']) && trim((string) $validated['notes']) !== ''
            ? trim((string) $validated['notes'])
            : null;

        $pen->update($validated);

        return redirect()->route('pens.index')->with('success', 'Pen updated successfully.');
    }

    public function destroy(Request $request, Pen $pen)
    {
        if ($request->confirm_code !== 'DELETE') {
            return back()->withErrors(['confirm_code' => 'Wrong delete code'])->withInput();
        }

        if ($pen->pigs()->count() > 0) {
            return back()->withErrors(['confirm_code' => 'Cannot delete a pen that still has pigs assigned.']);
        }

        $pen->delete();

        return redirect()->route('pens.index')->with('success', 'Pen deleted successfully.');
    }
}
