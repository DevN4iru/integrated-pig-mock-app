<?php

namespace App\Http\Controllers;

use App\Models\Pig;
use Illuminate\Http\Request;

class PigController extends Controller
{
    public function index()
    {
        $pigs = Pig::latest()->get();

        return view('pigs.index', compact('pigs'));
    }

    public function create()
    {
        return view('pigs.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ear_tag' => ['required', 'string', 'max:255', 'unique:pigs,ear_tag'],
            'breed' => ['required', 'string', 'max:255'],
            'sex' => ['required', 'string', 'in:male,female'],
            'pen_location' => ['required', 'string', 'max:255'],
            'pig_source' => ['required', 'string', 'in:birthed,purchased'],
            'date_added' => ['required', 'date'],
            'latest_weight' => ['required', 'numeric', 'min:0'],
            'asset_value' => ['required', 'numeric', 'min:0'],
        ]);

        Pig::create($validated);

        return redirect()
            ->route('pigs.index')
            ->with('success', 'Pig added successfully.');
    }
}