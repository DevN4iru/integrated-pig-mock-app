<?php

namespace App\Http\Controllers;

use App\Models\FeedLog;
use App\Models\Pig;
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
            'feed_type' => ['required'],
            'start_feed_date' => ['required', 'date'],
            'end_feed_date' => ['nullable', 'date'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'unit' => ['required'],
            'feeding_time' => ['required'],
            'status' => ['required'],
            'notes' => ['nullable'],
        ]);

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
            'feed_type' => ['required'],
            'start_feed_date' => ['required', 'date'],
            'end_feed_date' => ['nullable', 'date'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'unit' => ['required'],
            'feeding_time' => ['required'],
            'status' => ['required'],
            'notes' => ['nullable'],
        ]);

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
            ->with('success', 'Feed log deleted.');
    }
}