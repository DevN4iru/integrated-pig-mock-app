<?php

namespace App\Http\Controllers;

use App\Models\Pig;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    protected function ensurePigCanBeSold(Pig $pig): ?\Illuminate\Http\RedirectResponse
    {
        if ($pig->trashed()) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', 'Cannot record a sale for an archived pig. Only active live pigs can be sold.');
        }

        if ($pig->mortalityLogs()->exists()) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', 'Cannot record a sale for a pig that already has a mortality record.');
        }

        if ($pig->sales()->exists()) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', 'Cannot record a sale for a pig that already has a sale record.');
        }

        return null;
    }

    public function create(Pig $pig)
    {
        if ($redirect = $this->ensurePigCanBeSold($pig)) {
            return $redirect;
        }

        $currentWeight = (float) ($pig->computed_weight ?? 0);
        $recommendedPrice = (float) ($pig->asset_value ?? 0);

        return view('sales.create', compact('pig', 'currentWeight', 'recommendedPrice'));
    }

    public function store(Request $request, Pig $pig)
    {
        if ($redirect = $this->ensurePigCanBeSold($pig)) {
            return $redirect;
        }

        $validated = $request->validate([
            'sold_date' => ['required', 'date'],
            'price' => ['required', 'numeric', 'min:0'],
            'buyer' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $currentWeight = (float) ($pig->computed_weight ?? 0);
        $recommendedPrice = (float) ($pig->asset_value ?? 0);

        Sale::create([
            'pig_id' => $pig->id,
            'sold_date' => $validated['sold_date'],
            'price' => (float) $validated['price'],
            'buyer' => isset($validated['buyer']) && trim((string) $validated['buyer']) !== ''
                ? trim((string) $validated['buyer'])
                : null,
            'notes' => isset($validated['notes']) && trim((string) $validated['notes']) !== ''
                ? trim((string) $validated['notes'])
                : null,
            'weight_at_sale' => $currentWeight,
            'price_per_kg_at_sale' => 0,
            'recommended_price' => $recommendedPrice,
        ]);

        return redirect()
            ->route('pigs.show', $pig)
            ->with('success', 'Sale recorded.');
    }

    public function batchStore(Request $request)
    {
        $validated = $request->validate([
            'pig_ids' => ['required', 'string'],
            'sold_date' => ['required', 'date'],
            'pricing_mode' => ['required', 'in:recommended,custom'],
            'custom_price' => ['nullable', 'numeric', 'min:0'],
            'buyer' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $pigIds = collect(explode(',', (string) $validated['pig_ids']))
            ->map(fn ($id) => (int) trim($id))
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($pigIds->isEmpty()) {
            return back()
                ->withErrors(['batch_sale' => 'Select at least one active pig for batch sale.'])
                ->withInput();
        }

        if ($validated['pricing_mode'] === 'custom' && !isset($validated['custom_price'])) {
            return back()
                ->withErrors(['custom_price' => 'Custom price is required when custom pricing mode is selected.'])
                ->withInput();
        }

        $pigs = Pig::with(['healthLogs', 'sales', 'mortalityLogs'])
            ->whereIn('id', $pigIds)
            ->get();

        if ($pigs->count() !== $pigIds->count()) {
            return back()
                ->withErrors(['batch_sale' => 'Some selected pigs could not be found.'])
                ->withInput();
        }

        $invalidPig = $pigs->first(function ($pig) {
            return $pig->trashed() || $pig->mortalityLogs->isNotEmpty() || $pig->sales->isNotEmpty();
        });

        if ($invalidPig) {
            return back()
                ->withErrors([
                    'batch_sale' => 'Batch sale failed. Pig ' . $invalidPig->ear_tag . ' is not an active live unsold pig.'
                ])
                ->withInput();
        }

        $buyer = isset($validated['buyer']) && trim((string) $validated['buyer']) !== ''
            ? trim((string) $validated['buyer'])
            : null;
        $notes = isset($validated['notes']) && trim((string) $validated['notes']) !== ''
            ? trim((string) $validated['notes'])
            : null;
        $customPrice = isset($validated['custom_price'])
            ? (float) $validated['custom_price']
            : null;

        DB::transaction(function () use ($pigs, $validated, $buyer, $notes, $customPrice): void {
            foreach ($pigs as $pig) {
                $currentWeight = (float) ($pig->computed_weight ?? 0);
                $recommendedPrice = (float) ($pig->asset_value ?? 0);

                $finalPrice = $validated['pricing_mode'] === 'recommended'
                    ? $recommendedPrice
                    : (float) $customPrice;

                Sale::create([
                    'pig_id' => $pig->id,
                    'sold_date' => $validated['sold_date'],
                    'price' => $finalPrice,
                    'buyer' => $buyer,
                    'notes' => $notes,
                    'weight_at_sale' => $currentWeight,
                    'price_per_kg_at_sale' => 0,
                    'recommended_price' => $recommendedPrice,
                ]);
            }
        });

        return redirect()
            ->route('pigs.index')
            ->with('success', 'Batch sale recorded successfully for ' . $pigs->count() . ' pig(s).');
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

        $sale->update([
            'sold_date' => $validated['sold_date'],
            'price' => (float) $validated['price'],
            'buyer' => isset($validated['buyer']) && trim((string) $validated['buyer']) !== ''
                ? trim((string) $validated['buyer'])
                : null,
            'notes' => isset($validated['notes']) && trim((string) $validated['notes']) !== ''
                ? trim((string) $validated['notes'])
                : null,
        ]);

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
