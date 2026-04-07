<?php

namespace App\Http\Controllers;

use App\Models\Pig;
use App\Models\FeedLog;
use Illuminate\Http\Request;

class FeedLogController extends Controller
{
    public function create(Pig $pig)
    {
        return view('feed-logs.create', compact('pig'));
    }

    public function store(Request $request, Pig $pig)
    {
        $validated = $request->validate([
            'feed_type' => ['required', 'string', 'max:255'],
            'start_feed_date' => ['required', 'date'],
            'end_feed_date' => ['nullable', 'date'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'in:kg,grams,sacks,bags'],
            'feeding_time' => ['required', 'in:Morning,Afternoon,Evening'],
            'status' => ['required', 'in:ongoing,completed'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($validated['status'] === 'completed' && empty($validated['end_feed_date'])) {
            return back()
                ->withErrors(['end_feed_date' => 'End feed date is required when status is completed.'])
                ->withInput();
        }

        if (!empty($validated['end_feed_date']) && $validated['end_feed_date'] < $validated['start_feed_date']) {
            return back()
                ->withErrors(['end_feed_date' => 'End feed date cannot be earlier than start feed date.'])
                ->withInput();
        }

        if ($validated['status'] === 'ongoing') {
            $validated['end_feed_date'] = null;
        }

        $validated['pig_id'] = $pig->id;

        FeedLog::create($validated);

        return redirect()
            ->route('pigs.show', $pig)
            ->with('success', 'Feed log added successfully.');
    }

    public function edit(Pig $pig, FeedLog $feedLog)
    {
        abort_if($feedLog->pig_id !== $pig->id, 404);

        return view('feed-logs.edit', compact('pig', 'feedLog'));
    }

    public function update(Request $request, Pig $pig, FeedLog $feedLog)
    {
        abort_if($feedLog->pig_id !== $pig->id, 404);

        $validated = $request->validate([
            'feed_type' => ['required', 'string', 'max:255'],
            'start_feed_date' => ['required', 'date'],
            'end_feed_date' => ['nullable', 'date'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'in:kg,grams,sacks,bags'],
            'feeding_time' => ['required', 'in:Morning,Afternoon,Evening'],
            'status' => ['required', 'in:ongoing,completed'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($validated['status'] === 'completed' && empty($validated['end_feed_date'])) {
            return back()
                ->withErrors(['end_feed_date' => 'End feed date is required when status is completed.'])
                ->withInput();
        }

        if (!empty($validated['end_feed_date']) && $validated['end_feed_date'] < $validated['start_feed_date']) {
            return back()
                ->withErrors(['end_feed_date' => 'End feed date cannot be earlier than start feed date.'])
                ->withInput();
        }

        if ($validated['status'] === 'ongoing') {
            $validated['end_feed_date'] = null;
        }

        $feedLog->update($validated);

        return redirect()
            ->route('pigs.show', $pig)
            ->with('success', 'Feed log updated successfully.');
    }

    public function destroy(Pig $pig, FeedLog $feedLog)
    {
        abort_if($feedLog->pig_id !== $pig->id, 404);

        $feedLog->delete();

        return redirect()
            ->route('pigs.show', $pig)
            ->with('success', 'Feed log deleted successfully.');
    }
}
