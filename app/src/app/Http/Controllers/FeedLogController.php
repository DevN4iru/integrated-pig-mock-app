<?php

namespace App\Http\Controllers;

use App\Models\FeedLog;
use App\Models\Pig;
use Illuminate\Http\Request;

class FeedLogController extends Controller
{
    public function create(Pig $pig)
    {
        if ($pig->isOperationallyLocked()) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', $pig->operationalLockMessage('assigned feeds'));
        }

        return view('feed-logs.create', compact('pig'));
    }

    public function store(Request $request, Pig $pig)
    {
        if ($pig->isOperationallyLocked()) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', $pig->operationalLockMessage('assigned feeds'));
        }

        $validated = $request->validate([
            'feed_type' => ['required', 'string', 'max:255'],
            'start_feed_date' => ['required', 'date'],
            'end_feed_date' => ['nullable', 'date', 'after_or_equal:start_feed_date'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:50'],
            'feeding_time' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['pig_id'] = $pig->id;
        $validated['cost'] = 0;
        $validated['feeding_time'] = trim((string) ($validated['feeding_time'] ?? '')) !== ''
            ? trim((string) $validated['feeding_time'])
            : 'Assigned period';
        $validated['status'] = trim((string) ($validated['status'] ?? '')) !== ''
            ? trim((string) $validated['status'])
            : 'ongoing';
        $validated['notes'] = isset($validated['notes']) ? trim((string) $validated['notes']) : null;
        $validated['notes'] = $validated['notes'] === '' ? null : $validated['notes'];

        FeedLog::create($validated);

        return redirect()
            ->route('pigs.show', $pig->id)
            ->with('success', 'Assigned feed saved.');
    }

    public function edit(Pig $pig, FeedLog $feedLog)
    {
        abort_if($feedLog->pig_id !== $pig->id, 404);

        if ($pig->isOperationallyLocked()) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', $pig->operationalLockMessage('assigned feeds'));
        }

        return view('feed-logs.edit', compact('pig', 'feedLog'));
    }

    public function update(Request $request, Pig $pig, FeedLog $feedLog)
    {
        abort_if($feedLog->pig_id !== $pig->id, 404);

        if ($pig->isOperationallyLocked()) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', $pig->operationalLockMessage('assigned feeds'));
        }

        $validated = $request->validate([
            'feed_type' => ['required', 'string', 'max:255'],
            'start_feed_date' => ['required', 'date'],
            'end_feed_date' => ['nullable', 'date', 'after_or_equal:start_feed_date'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:50'],
            'feeding_time' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['cost'] = 0;
        $validated['feeding_time'] = trim((string) ($validated['feeding_time'] ?? '')) !== ''
            ? trim((string) $validated['feeding_time'])
            : 'Assigned period';
        $validated['status'] = trim((string) ($validated['status'] ?? '')) !== ''
            ? trim((string) $validated['status'])
            : 'ongoing';
        $validated['notes'] = isset($validated['notes']) ? trim((string) $validated['notes']) : null;
        $validated['notes'] = $validated['notes'] === '' ? null : $validated['notes'];

        $feedLog->update($validated);

        return redirect()
            ->route('pigs.show', $pig->id)
            ->with('success', 'Assigned feed updated.');
    }

    public function destroy(Pig $pig, FeedLog $feedLog)
    {
        abort_if($feedLog->pig_id !== $pig->id, 404);

        if ($pig->isOperationallyLocked()) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', $pig->operationalLockMessage('assigned feeds'));
        }

        $feedLog->delete();

        return redirect()
            ->route('pigs.show', $pig->id)
            ->with('success', 'Assigned feed deleted.');
    }
}
