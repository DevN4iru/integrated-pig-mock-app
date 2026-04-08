<?php

namespace App\Http\Controllers;

use App\Models\FarmSetting;
use App\Models\Pig;
use Illuminate\Http\Request;

class FarmSettingController extends Controller
{
    public function edit()
    {
        $setting = FarmSetting::query()->find(1);

        if (!$setting) {
            $setting = FarmSetting::query()->create([
                'id' => 1,
                'price_per_kg' => 0,
            ]);
        }

        return view('settings.farm', compact('setting'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'price_per_kg' => ['required', 'numeric', 'min:0'],
        ]);

        $setting = FarmSetting::query()->find(1);

        if (!$setting) {
            $setting = FarmSetting::query()->create([
                'id' => 1,
                'price_per_kg' => $validated['price_per_kg'],
            ]);
        } else {
            $setting->update($validated);
        }

        // 🔥 PHASE 2: RECOMPUTE ALL PIG ASSET VALUES
        $pricePerKg = (float) $validated['price_per_kg'];

        Pig::query()->chunk(100, function ($pigs) use ($pricePerKg) {
            foreach ($pigs as $pig) {
                $weight = (float) $pig->latest_weight;
                $pig->asset_value = $weight * $pricePerKg;
                $pig->save();
            }
        });

        return redirect()
            ->route('settings.farm.edit')
            ->with('success', 'Farm pricing updated and all pig asset values recalculated.');
    }
}