<?php

namespace App\Http\Controllers;

use App\Models\FarmSetting;
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

        return redirect()
            ->route('settings.farm.edit')
            ->with('success', 'Farm pricing updated. Active pig live values now reflect the new price dynamically.');
    }
}
