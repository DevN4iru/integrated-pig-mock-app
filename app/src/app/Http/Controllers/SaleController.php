<?php

namespace App\Http\Controllers;

use App\Models\Pig;
use App\Models\Sale;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function create(Pig $pig)
    {
        return view('sales.create', compact('pig'));
    }

    public function store(Request $request, Pig $pig)
    {
        $validated = $request->validate([
            'sold_date' => ['required', 'date'],
            'price' => ['required'],
            'buyer' => ['nullable'],
            'notes' => ['nullable'],
        ]);

        $validated['pig_id'] = $pig->id;

        Sale::create($validated);

        return redirect()->route('pigs.show', $pig)
            ->with('success', 'Sale recorded.');
    }
}