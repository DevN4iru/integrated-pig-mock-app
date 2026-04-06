<?php

namespace App\Http\Controllers;

use App\Models\Pen;
use App\Models\Pig;
use Illuminate\Http\Request;

class PigController extends Controller
{
    public function index()
    {
        $pigs = Pig::with('pen')->latest()->get();

        return view('pigs.index', compact('pigs'));
    }

    public function create()
    {
        $pens = Pen::orderBy('name')->get();

        return view('pigs.create', compact('pens'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ear_tag' => ['required', 'unique:pigs,ear_tag'],
            'breed' => ['required'],
            'sex' => ['required'],
            'pen_id' => ['required', 'exists:pens,id'],
            'pig_source' => ['required'],
            'date_added' => ['required', 'date'],
            'latest_weight' => ['required', 'numeric'],
            'asset_value' => ['required', 'numeric'],
        ]);

        $pen = Pen::withCount('pigs')->findOrFail($validated['pen_id']);

        if ($pen->pigs_count >= $pen->capacity) {
            return back()->withErrors(['pen_id' => 'Pen is FULL'])->withInput();
        }

        $validated['pen_location'] = $pen->name;

        Pig::create($validated);

        return redirect()->route('pigs.index')->with('success', 'Pig added successfully.');
    }

    public function edit(Request $request, Pig $pig)
    {
        if ($request->query('code') !== '12345') {
            return redirect()
                ->route('pigs.index')
                ->with('error', 'Access denied. Type the correct edit code first.');
        }

        $pens = Pen::orderBy('name')->get();

        return view('pigs.edit', compact('pig', 'pens'));
    }

    public function update(Request $request, Pig $pig)
    {
        $validated = $request->validate([
            'ear_tag' => ['required', 'unique:pigs,ear_tag,' . $pig->id],
            'breed' => ['required'],
            'sex' => ['required'],
            'pen_id' => ['required', 'exists:pens,id'],
            'pig_source' => ['required'],
            'date_added' => ['required', 'date'],
            'latest_weight' => ['required', 'numeric'],
            'asset_value' => ['required', 'numeric'],
        ]);

        $newPen = Pen::withCount('pigs')->findOrFail($validated['pen_id']);

        if ((int) $pig->pen_id !== (int) $newPen->id && $newPen->pigs_count >= $newPen->capacity) {
            return back()->withErrors(['pen_id' => 'Selected pen is FULL'])->withInput();
        }

        $validated['pen_location'] = $newPen->name;

        $pig->update($validated);

        return redirect()->route('pigs.index')->with('success', 'Pig updated successfully.');
    }

    public function destroy(Request $request, Pig $pig)
    {
        if ($request->confirm_code !== 'DELETE') {
            return back()->withErrors(['confirm_code' => 'Wrong delete code'])->withInput();
        }

        $pig->delete();

        return redirect()->route('pigs.index')->with('success', 'Pig deleted successfully.');
    }
}