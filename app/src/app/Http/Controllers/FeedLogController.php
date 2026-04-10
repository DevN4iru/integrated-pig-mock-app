<?php

namespace App\Http\Controllers;

use App\Models\FeedLog;
use App\Models\Pig;
use Illuminate\Http\Request;

class FeedLogController extends Controller
{
    private function isLocked(Pig $pig): bool
    {
        return $pig->trashed()
            || $pig->mortalityLogs()->exists()
            || $pig->sales()->exists();
    }

    private function lockedMessage(Pig $pig): string
    {
        if ($pig->trashed()) {
            return 'Archived pigs cannot receive feed log changes. Restore the pig first.';
        }

        if ($pig->mortalityLogs()->exists()) {
            return 'Dead pigs cannot receive feed log changes.';
        }

        if ($pig->sales()->exists()) {
            return 'Sold pigs cannot receive feed log changes.';
        }

        return 'This pig is locked for feed log changes.';
    }

    public function create(Pig $pig)
    {
        if ($this->isLocked($pig)) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', $this->lockedMessage($pig));
        }

        return view('feed-logs.create', compact('pig'));
    }

    public function store(Request $request, Pig $pig)
    {
        if ($this->isLocked($pig)) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', $this->lockedMessage($pig));
        }

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
            ->route('pigs.show', $pig->id)
            ->with('success', 'Feed log added successfully.');
    }

    public function edit(Pig $pig, FeedLog $feedLog)
    {
        abort_if($feedLog->pig_id !== $pig->id, 404);

        if ($this->isLocked($pig)) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', $this->lockedMessage($pig));
        }

        return view('feed-logs.edit', compact('pig', 'feedLog'));
    }

    public function update(Request $request, Pig $pig, FeedLog $feedLog)
    {
        abort_if($feedLog->pig_id !== $pig->id, 404);

        if ($this->isLocked($pig)) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', $this->lockedMessage($pig));
        }

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
            ->route('pigs.show', $pig->id)
            ->with('success', 'Feed log updated successfully.');
    }

    public function destroy(Pig $pig, FeedLog $feedLog)
    {
        abort_if($feedLog->pig_id !== $pig->id, 404);

        if ($this->isLocked($pig)) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', $this->lockedMessage($pig));
        }

        $feedLog->delete();

        return redirect()
            ->route('pigs.show', $pig->id)
            ->with('success', 'Feed log deleted.');
    }
}
