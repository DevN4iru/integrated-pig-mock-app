<?php

namespace App\Http\Controllers;

use App\Models\Pen;
use App\Models\PigTransfer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PenController extends Controller
{
    public function index()
    {
        $pens = Pen::withCount([
                'activePigs as pigs_count',
            ])
            ->get()
            ->sortBy(function ($pen) {
                return $pen->sortKey();
            })
            ->values();

        $penTypes = Pen::typeOptions();

        $penGroups = collect($penTypes)->mapWithKeys(function ($type) use ($pens) {
            return [$type => $pens->where('type', $type)->values()];
        });

        $summary = [
            'total_pens' => $pens->count(),
            'full' => $pens->where(fn ($pen) => $pen->occupancyStatus() === 'full')->count(),
            'limited' => $pens->where(fn ($pen) => $pen->occupancyStatus() === 'limited')->count(),
            'open' => $pens->where(fn ($pen) => $pen->occupancyStatus() === 'open')->count(),
            'occupied_slots' => $pens->sum(fn ($pen) => $pen->occupiedCount()),
            'total_capacity' => $pens->sum(fn ($pen) => (int) $pen->capacity),
        ];

        return view('pens.index', compact('pens', 'penTypes', 'penGroups', 'summary'));
    }

    public function show(Pen $pen)
    {
        $pen->loadCount(['activePigs as pigs_count']);

        $activePigs = $pen->activePigs()
            ->with([
                'healthLogs',
                'sales',
                'mortalityLogs',
            ])
            ->orderBy('ear_tag')
            ->get();

        $recentTransfers = PigTransfer::with(['pig', 'fromPen', 'toPen'])
            ->where(function ($query) use ($pen) {
                $query->where('from_pen_id', $pen->id)
                    ->orWhere('to_pen_id', $pen->id);
            })
            ->orderByDesc('transfer_date')
            ->orderByDesc('id')
            ->take(15)
            ->get();

        $summary = [
            'occupied' => $pen->occupiedCount(),
            'available' => $pen->availableSlots(),
            'capacity' => (int) $pen->capacity,
            'occupancy_percent' => $pen->occupancyPercent(),
            'status' => $pen->occupancyStatus(),
        ];

        return view('pens.show', compact('pen', 'activePigs', 'recentTransfers', 'summary'));
    }

    public function create()
    {
        $penTypes = Pen::typeOptions();

        return view('pens.create', compact('penTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:pens,name'],
            'type' => ['required', Rule::in(Pen::typeOptions())],
            'capacity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['notes'] = isset($validated['notes']) && trim((string) $validated['notes']) !== ''
            ? trim((string) $validated['notes'])
            : null;

        Pen::create($validated);

        return redirect()->route('pens.index')->with('success', 'Pen added successfully.');
    }

    public function edit(Request $request, Pen $pen)
    {
        if ($request->query('code') !== '12345') {
            return redirect()
                ->route('pens.index')
                ->with('error', 'Access denied. Type the correct edit code first.');
        }

        $penTypes = Pen::typeOptions();

        return view('pens.edit', compact('pen', 'penTypes'));
    }

    public function update(Request $request, Pen $pen)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:pens,name,' . $pen->id],
            'type' => ['required', Rule::in(Pen::typeOptions())],
            'capacity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($validated['capacity'] < $pen->activePigs()->count()) {
            return back()->withErrors([
                'capacity' => 'Capacity cannot be lower than current occupied count.'
            ])->withInput();
        }

        $validated['notes'] = isset($validated['notes']) && trim((string) $validated['notes']) !== ''
            ? trim((string) $validated['notes'])
            : null;

        $pen->update($validated);

        return redirect()->route('pens.index')->with('success', 'Pen updated successfully.');
    }

    public function destroy(Request $request, Pen $pen)
    {
        if ($request->confirm_code !== 'DELETE') {
            return back()->withErrors(['confirm_code' => 'Wrong delete code'])->withInput();
        }

        if ($pen->activePigs()->count() > 0) {
            return back()->withErrors([
                'confirm_code' => 'Cannot delete a pen that still has pigs assigned.'
            ])->withInput();
        }

        $hasTransferHistory = PigTransfer::query()
            ->where('from_pen_id', $pen->id)
            ->orWhere('to_pen_id', $pen->id)
            ->exists();

        if ($hasTransferHistory) {
            return back()->withErrors([
                'confirm_code' => 'Cannot delete this pen because it is already referenced in transfer history.'
            ])->withInput();
        }

        $pen->delete();

        return redirect()->route('pens.index')->with('success', 'Pen deleted successfully.');
    }
}