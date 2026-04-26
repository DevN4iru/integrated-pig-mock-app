<?php

namespace App\Http\Controllers;

use App\Models\Pig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BreedingStockValueController extends Controller
{
    public function update(Request $request, Pig $pig): RedirectResponse
    {
        $excluded = $request->boolean('exclude_from_value_computation');
        $weight = (float) ($pig->computed_weight ?? $pig->latest_weight ?? 0);

        $pig->exclude_from_value_computation = $excluded;
        $pig->asset_value = $excluded ? 0 : Pig::preservedAssetValueSeedFromWeight($weight);
        $pig->save();

        return redirect()
            ->route('pigs.show', $pig->id)
            ->with('success', $excluded ? 'Pig removed from farm value totals.' : 'Pig restored to farm value totals.');
    }
}
