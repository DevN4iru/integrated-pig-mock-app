<?php

namespace App\Http\Controllers;

use App\Models\Pig;
use App\Models\Sale;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function create(Pig $pig)
    {
        if ($pig->mortalityLogs()->exists()) {
            return redirect()
                ->route('pigs.show', $pig)
                ->with('error', 'Cannot record a sale for a pig that already has a mortality record.');
        }

        return view('sales.create', compact('pig'));
    }

    public function store(Request $request, Pig $pig)
    {
        if ($pig->mortalityLogs()->exists()) {
            return redirect()
                ->route('pigs.show', $pig)
                ->with('error', 'Cannot record a sale for a pig that already has a mortality record.');
        }

        $validated = $request->validate([
            'sold_date' => ['required', 'date'],
            'price' => ['required', 'numeric', 'min:0'],
            'buyer' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['pig_id'] = $pig->id;

        Sale::create($validated);

        return redirect()
            ->route('pigs.show', $pig)
            ->with('success', 'Sale recorded.');
    }

    public function edit(Pig $pig, Sale $sale)
    {
        abort_if($sale->pig_id !== $pig->id, 404);

        return view('sales.edit', compact('pig', 'sale'));
    }

    public function update(Request $request, Pig $pig, Sale $sale)
    {
        abort_if($sale->pig_id !== $pig->id, 404);

        $validated = $request->validate([
            'sold_date' => ['required', 'date'],
            'price' => ['required', 'numeric', 'min:0'],
            'buyer' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $sale->update($validated);

        return redirect()
            ->route('pigs.show', $pig)
            ->with('success', 'Sale record updated.');
    }

    public function destroy(Pig $pig, Sale $sale)
    {
        abort_if($sale->pig_id !== $pig->id, 404);

        $sale->delete();

        return redirect()
            ->route('pigs.show', $pig)
            ->with('success', 'Sale record deleted.');
    }
}
