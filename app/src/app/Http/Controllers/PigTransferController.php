<?php

namespace App\Http\Controllers;

use App\Models\Pen;
use App\Models\Pig;
use App\Models\PigTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PigTransferController extends Controller
{
    public function create(Pig $pig)
    {
        if ($pig->isOperationallyLocked()) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', $pig->operationalLockMessage('transfers'));
        }

        $pig->load(['pen' => function ($query) {
            $query->withCount(['activePigs as pigs_count']);
        }]);

        if (!$pig->pen_id || !$pig->pen) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', 'This pig does not currently have an assigned pen, so transfer cannot be started.');
        }

        $destinationPens = Pen::withCount(['activePigs as pigs_count'])
            ->where('id', '!=', $pig->pen_id)
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $reasonOptions = PigTransfer::reasonOptions();

        return view('pig-transfers.create', compact('pig', 'destinationPens', 'reasonOptions'));
    }

    public function store(Request $request, Pig $pig)
    {
        if ($pig->isOperationallyLocked()) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', $pig->operationalLockMessage('transfers'));
        }

        if (!$pig->pen_id || !$pig->pen) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', 'This pig does not currently have an assigned pen, so transfer cannot be completed.');
        }

        $validated = $request->validate([
            'to_pen_id' => ['required', 'exists:pens,id'],
            'transfer_date' => ['required', 'date', 'before_or_equal:today'],
            'reason_code' => ['required', Rule::in(array_keys(PigTransfer::reasonOptions()))],
            'reason_notes' => ['nullable', 'string'],
        ]);

        if ($validated['reason_code'] === PigTransfer::REASON_OTHER && trim((string) ($validated['reason_notes'] ?? '')) === '') {
            return back()
                ->withErrors(['reason_notes' => 'Reason notes are required when "Other" is selected.'])
                ->withInput();
        }

        if ((int) $validated['to_pen_id'] === (int) $pig->pen_id) {
            return back()
                ->withErrors(['to_pen_id' => 'Destination pen must be different from the current pen.'])
                ->withInput();
        }

        $destinationPen = Pen::withCount(['activePigs as pigs_count'])->findOrFail($validated['to_pen_id']);

        if ($destinationPen->pigs_count >= $destinationPen->capacity) {
            return back()
                ->withErrors(['to_pen_id' => 'Destination pen is FULL.'])
                ->withInput();
        }

        $validated['reason_notes'] = isset($validated['reason_notes']) && trim((string) $validated['reason_notes']) !== ''
            ? trim((string) $validated['reason_notes'])
            : null;

        DB::transaction(function () use ($pig, $destinationPen, $validated): void {
            PigTransfer::create([
                'pig_id' => $pig->id,
                'from_pen_id' => $pig->pen_id,
                'to_pen_id' => $destinationPen->id,
                'transfer_date' => $validated['transfer_date'],
                'reason_code' => $validated['reason_code'],
                'reason_notes' => $validated['reason_notes'],
            ]);

            $pig->update([
                'pen_id' => $destinationPen->id,
                'pen_location' => $destinationPen->name,
            ]);
        });

        return redirect()
            ->route('pigs.show', $pig->id)
            ->with('success', 'Pig transferred successfully.');
    }

    public function batchStore(Request $request)
    {
        $validated = $request->validate([
            'pig_ids' => ['required', 'string'],
            'to_pen_id' => ['required', 'exists:pens,id'],
            'transfer_date' => ['required', 'date', 'before_or_equal:today'],
            'reason_code' => ['required', Rule::in(array_keys(PigTransfer::reasonOptions()))],
            'reason_notes' => ['nullable', 'string'],
        ]);

        if ($validated['reason_code'] === PigTransfer::REASON_OTHER && trim((string) ($validated['reason_notes'] ?? '')) === '') {
            return back()
                ->withErrors(['batch_transfer_reason_notes' => 'Reason notes are required when "Other" is selected.'])
                ->withInput();
        }

        $pigIds = collect(explode(',', (string) $validated['pig_ids']))
            ->map(fn ($id) => (int) trim($id))
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($pigIds->isEmpty()) {
            return back()->withErrors(['batch_transfer' => 'Select at least one pig for batch transfer.'])->withInput();
        }

        $pigs = Pig::with(['pen', 'sales', 'mortalityLogs'])
            ->whereIn('id', $pigIds)
            ->get();

        if ($pigs->count() !== $pigIds->count()) {
            return back()->withErrors(['batch_transfer' => 'Some selected pigs could not be found.'])->withInput();
        }

        $lockedPig = $pigs->first(fn ($pig) => $pig->isOperationallyLocked());
        if ($lockedPig) {
            return back()->withErrors([
                'batch_transfer' => 'Batch transfer failed. Pig ' . $lockedPig->ear_tag . ' is locked and cannot be transferred.'
            ])->withInput();
        }

        $pigWithoutPen = $pigs->first(fn ($pig) => !$pig->pen_id || !$pig->pen);
        if ($pigWithoutPen) {
            return back()->withErrors([
                'batch_transfer' => 'Batch transfer failed. Pig ' . $pigWithoutPen->ear_tag . ' has no current pen assignment.'
            ])->withInput();
        }

        $destinationPen = Pen::withCount(['activePigs as pigs_count'])->findOrFail($validated['to_pen_id']);

        $sameDestinationPig = $pigs->first(fn ($pig) => (int) $pig->pen_id === (int) $destinationPen->id);
        if ($sameDestinationPig) {
            return back()->withErrors([
                'batch_transfer' => 'Batch transfer failed. Pig ' . $sameDestinationPig->ear_tag . ' is already assigned to the selected destination pen.'
            ])->withInput();
        }

        $availableSlots = max((int) $destinationPen->capacity - (int) $destinationPen->pigs_count, 0);
        if ($pigIds->count() > $availableSlots) {
            return back()->withErrors([
                'batch_transfer' => 'Batch transfer failed. Destination pen does not have enough available capacity for all selected pigs.'
            ])->withInput();
        }

        $reasonNotes = isset($validated['reason_notes']) && trim((string) $validated['reason_notes']) !== ''
            ? trim((string) $validated['reason_notes'])
            : null;

        DB::transaction(function () use ($pigs, $destinationPen, $validated, $reasonNotes): void {
            foreach ($pigs as $pig) {
                PigTransfer::create([
                    'pig_id' => $pig->id,
                    'from_pen_id' => $pig->pen_id,
                    'to_pen_id' => $destinationPen->id,
                    'transfer_date' => $validated['transfer_date'],
                    'reason_code' => $validated['reason_code'],
                    'reason_notes' => $reasonNotes,
                ]);

                $pig->update([
                    'pen_id' => $destinationPen->id,
                    'pen_location' => $destinationPen->name,
                ]);
            }
        });

        return redirect()
            ->route('pigs.index')
            ->with('success', 'Batch transfer completed successfully for ' . $pigs->count() . ' pig(s).');
    }
}
