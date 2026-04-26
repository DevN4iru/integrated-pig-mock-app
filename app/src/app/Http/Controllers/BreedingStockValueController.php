<?php

namespace App\Http\Controllers;

use App\Models\Pig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class BreedingStockValueController extends Controller
{
    public function update(Request $request, Pig $pig): RedirectResponse
    {
        if (!Schema::hasColumn('pigs', 'exclude_from_value_computation')) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', 'Run the latest client migration before using the breeding stock value toggle.');
        }

        $excluded = $request->boolean('exclude_from_value_computation');
        $weight = (float) ($pig->computed_weight ?? $pig->latest_weight ?? 0);

        $pig->forceFill([
            'exclude_from_value_computation' => $excluded,
            'asset_value' => $excluded ? 0 : Pig::preservedAssetValueSeedFromWeight($weight),
        ])->save();

        return redirect()
            ->route('pigs.show', $pig->id)
            ->with('success', $excluded ? 'Pig removed from farm value totals.' : 'Pig restored to farm value totals.');
    }
}
