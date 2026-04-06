<?php

namespace App\Http\Controllers;

use App\Models\Pen;
use Illuminate\Http\Request;

class PenController extends Controller
{
    public function index()
    {
        $pens = Pen::withCount('pigs')->latest()->get();

        return view('pens.index', compact('pens'));
    }

    public function create()
    {
        return view('pens.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:pens,name'],
            'type' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

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

        return view('pens.edit', compact('pen'));
    }

    public function update(Request $request, Pen $pen)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:pens,name,' . $pen->id],
            'type' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($validated['capacity'] < $pen->pigs()->count()) {
            return back()->withErrors([
                'capacity' => 'Capacity cannot be lower than current occupied count.'
            ])->withInput();
        }

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