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
            'unit' => ['required', 'string', 'max:255'],
            'feeding_time' => ['required', 'in:Morning,Afternoon,Evening'],
            'status' => ['required', 'in:ongoing,completed'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($validated['status'] === 'completed' && empty($validated['end_feed_date'])) {
            return back()
                ->withErrors(['end_feed_date' => 'End feed date is required when status is completed.'])
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
}
