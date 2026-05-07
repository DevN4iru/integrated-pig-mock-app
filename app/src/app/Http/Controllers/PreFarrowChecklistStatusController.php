<?php

namespace App\Http\Controllers;

use App\Models\PreFarrowChecklistStatus;
use App\Models\ReproductionCycle;
use App\Services\PreFarrowReminderSchedule;
use Illuminate\Http\Request;

class PreFarrowChecklistStatusController extends Controller
{
    public function toggle(Request $request, ReproductionCycle $reproductionCycle, string $checklistKey)
    {
        $validKeys = collect(PreFarrowReminderSchedule::items())
            ->pluck('code')
            ->filter()
            ->values()
            ->all();

        if (!in_array($checklistKey, $validKeys, true)) {
            abort(404, 'Checklist item not found.');
        }

        if (
            !$reproductionCycle->expected_farrow_date
            || $reproductionCycle->actual_farrow_date
            || $reproductionCycle->pregnancy_result !== ReproductionCycle::PREGNANCY_RESULT_PREGNANT
        ) {
            return redirect()
                ->route('reproduction-cycles.show', $reproductionCycle)
                ->with('error', 'Pre-farrow checklist items can only be updated while the pregnant case is still waiting for farrowing.');
        }

        $existing = PreFarrowChecklistStatus::query()
            ->where('reproduction_cycle_id', $reproductionCycle->id)
            ->where('checklist_key', $checklistKey)
            ->first();

        if ($existing) {
            $existing->delete();

            return redirect()
                ->route('reproduction-cycles.show', $reproductionCycle)
                ->with('success', 'Pre-farrow checklist item marked as not done.');
        }

        PreFarrowChecklistStatus::create([
            'reproduction_cycle_id' => $reproductionCycle->id,
            'checklist_key' => $checklistKey,
            'completed_at' => now(),
        ]);

        return redirect()
            ->route('reproduction-cycles.show', $reproductionCycle)
            ->with('success', 'Pre-farrow checklist item marked as done.');
    }
}
