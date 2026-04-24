<?php

namespace App\Http\Controllers;

use App\Models\FarmSetting;
use Illuminate\Http\Request;

class FarmSettingController extends Controller
{
    public function edit()
    {
        $setting = FarmSetting::current();

        return view('settings.farm', compact('setting'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'price_per_kg' => ['required', 'numeric', 'min:0'],
            'alert_recipient_email' => ['nullable', 'email:rfc'],
            'server_close_reminder_time' => ['nullable', 'date_format:H:i'],
            'feed_reminder_time' => ['nullable', 'date_format:H:i'],
        ]);

        $setting = FarmSetting::current();
        $setting->update($validated);

        return redirect()
            ->route('settings.farm.edit')
            ->with('success', 'Farm settings updated. Pricing and email reminder settings are now saved.');
    }
}
