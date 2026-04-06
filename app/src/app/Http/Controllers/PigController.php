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
            'ear_tag' => ['required', 'string', 'max:255', 'unique:pigs,ear_tag'],
            'breed' => ['required', 'string', 'max:255'],
            'sex' => ['required', 'string', 'in:male,female'],
            'pen_id' => ['required', 'exists:pens,id'],
            'pig_source' => ['required', 'string', 'in:birthed,purchased'],
            'date_added' => ['required', 'date'],
            'latest_weight' => ['required', 'numeric', 'min:0'],
            'asset_value' => ['required', 'numeric', 'min:0'],
        ]);

        $pen = Pen::findOrFail($validated['pen_id']);
        $validated['pen_location'] = $pen->name;

        Pig::create($validated);

        return redirect()
            ->route('pigs.index')
            ->with('success', 'Pig added successfully.');
    }
}