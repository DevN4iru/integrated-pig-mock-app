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
            'ear_tag' => 'required',
            'breed' => 'required',
            'sex' => 'required',
            'pen_location' => 'required',
            'status' => 'required',
            'origin_date' => 'required|date',
            'latest_weight' => 'required|numeric',
            'weight_date_added' => 'required|date',
            'asset_value' => 'required|numeric',
        ]);

        Pig::create($validated);

        return redirect()->route('pigs.index');
    }
}
