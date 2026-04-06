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

        return redirect()
            ->route('pens.index')
            ->with('success', 'Pen added successfully.');
    }
}