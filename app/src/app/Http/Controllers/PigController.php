<?php

namespace App\Http\Controllers;

use App\Models\FarmSetting;
use App\Models\Pen;
use App\Models\Pig;
use App\Models\PigTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PigController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', 'all');
        $source = (string) $request->query('source', 'all');

        $pigs = Pig::withTrashed()
            ->with([
                'pen',
                'healthLogs',
                'sales',
                'mortalityLogs',
                'feedLogs',
                'medications',
                'vaccinations',
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('ear_tag', 'like', '%' . $search . '%')
                        ->orWhere('breed', 'like', '%' . $search . '%')
                        ->orWhere('sex', 'like', '%' . $search . '%')
                        ->orWhere('pig_source', 'like', '%' . $search . '%')
                        ->orWhere('pen_location', 'like', '%' . $search . '%')
                        ->orWhereHas('pen', function ($penQuery) use ($search) {
                            $penQuery->where('name', 'like', '%' . $search . '%');
                        });
                });
            })
            ->when($source !== 'all', function ($query) use ($source) {
                $query->where('pig_source', $source);
            })
            ->latest()
            ->get();

        $activePigs = $pigs->filter(function ($pig) {
            return !$pig->trashed()
                && $pig->mortalityLogs->isEmpty()
                && $pig->sales->isEmpty();
        })->values();

        $soldPigs = $pigs->filter(function ($pig) {
            return !$pig->trashed()
                && $pig->mortalityLogs->isEmpty()
                && $pig->sales->isNotEmpty();
        })->values();

        $deadPigs = $pigs->filter(function ($pig) {
            return !$pig->trashed()
                && $pig->mortalityLogs->isNotEmpty();
        })->values();

        $archivedPigs = $pigs->filter(function ($pig) {
            return $pig->trashed();
        })->values();

        if ($status === 'active') {
            $soldPigs = collect();
            $deadPigs = collect();
            $archivedPigs = collect();
        } elseif ($status === 'sold') {
            $activePigs = collect();
            $deadPigs = collect();
            $archivedPigs = collect();
        } elseif ($status === 'dead') {
            $activePigs = collect();
            $soldPigs = collect();
            $archivedPigs = collect();
        } elseif ($status === 'archived') {
            $activePigs = collect();
            $soldPigs = collect();
            $deadPigs = collect();
        }

        $destinationPens = Pen::withCount(['activePigs as pigs_count'])
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $reasonOptions = PigTransfer::reasonOptions();
        $pricePerKg = FarmSetting::currentPricePerKg();

        return view('pigs.index', compact(
            'activePigs',
            'soldPigs',
            'deadPigs',
            'archivedPigs',
            'search',
            'status',
            'source',
            'destinationPens',
            'reasonOptions',
            'pricePerKg'
        ));
    }

    public function create()
    {
        $pens = Pen::withCount(['activePigs as pigs_count'])
            ->orderBy('name')
            ->get();

        $pricePerKg = FarmSetting::currentPricePerKg();

        return view('pigs.create', compact('pens', 'pricePerKg'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ear_tag' => ['required', 'unique:pigs,ear_tag'],
            'breed' => ['required'],
            'sex' => ['required'],
            'pen_id' => ['required', 'exists:pens,id'],
            'pig_source' => ['required'],
            'date_added' => ['required', 'date'],
            'latest_weight' => ['required', 'numeric', 'min:0'],
        ]);

        $pen = Pen::withCount(['activePigs as pigs_count'])->findOrFail($validated['pen_id']);

        if ($pen->pigs_count >= $pen->capacity) {
            return back()->withErrors(['pen_id' => 'Pen is FULL'])->withInput();
        }

        $validated['pen_location'] = $pen->name;
        $validated['asset_value'] = (float) $validated['latest_weight'] * FarmSetting::currentPricePerKg();

        Pig::create($validated);

        return redirect()->route('pigs.index')->with('success', 'Pig added successfully.');
    }

    public function show($pig)
    {
        $pig = Pig::withTrashed()
            ->with([
                'pen',
                'healthLogs',
                'medications',
                'vaccinations',
                'mortalityLogs',
                'sales',
                'feedLogs',
                'transfers.fromPen',
                'transfers.toPen',
            ])
            ->findOrFail($pig);

        return view('pigs.show', compact('pig'));
    }

    public function edit(Request $request, Pig $pig)
    {
        if ($pig->trashed()) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', 'Archived pigs cannot be edited. Restore the pig first.');
        }

        if ($request->query('code') !== '12345') {
            return redirect()
                ->route('pigs.index')
                ->with('error', 'Access denied. Type the correct edit code first.');
        }

        $pens = Pen::withCount(['activePigs as pigs_count'])
            ->orderBy('name')
            ->get();

        return view('pigs.edit', compact('pig', 'pens'));
    }

    public function update(Request $request, Pig $pig)
    {
        if ($pig->trashed()) {
            return redirect()
                ->route('pigs.show', $pig->id)
                ->with('error', 'Archived pigs cannot be updated. Restore the pig first.');
        }

        $validated = $request->validate([
            'ear_tag' => ['required', 'unique:pigs,ear_tag,' . $pig->id],
            'breed' => ['required'],
            'sex' => ['required'],
            'pen_id' => ['required', 'exists:pens,id'],
            'pig_source' => ['required'],
            'date_added' => ['required', 'date'],
            'latest_weight' => ['required', 'numeric', 'min:0'],
        ]);

        $newPen = Pen::withCount(['activePigs as pigs_count'])->findOrFail($validated['pen_id']);

        if ((int) $pig->pen_id !== (int) $newPen->id && $newPen->pigs_count >= $newPen->capacity) {
            return back()->withErrors(['pen_id' => 'Selected pen is FULL'])->withInput();
        }

        $validated['pen_location'] = $newPen->name;
        $validated['asset_value'] = (float) $validated['latest_weight'] * FarmSetting::currentPricePerKg();

        $pig->update($validated);

        return redirect()->route('pigs.index')->with('success', 'Pig updated successfully.');
    }

    public function destroy(Pig $pig)
    {
        if ($pig->trashed()) {
            return redirect()->route('pigs.index')->with('error', 'Pig is already archived.');
        }

        $pig->delete();

        return redirect()->route('pigs.index')->with('success', 'Pig archived successfully.');
    }

    public function restore($pig)
    {
        $pig = Pig::withTrashed()->findOrFail($pig);

        if (!$pig->trashed()) {
            return redirect()->route('pigs.index')->with('error', 'Pig is already active.');
        }

        $pig->restore();

        return redirect()->route('pigs.index')->with('success', 'Pig restored successfully.');
    }

    public function forceDelete(Request $request, $pig)
    {
        $pig = Pig::withTrashed()->findOrFail($pig);

        if ($request->input('code') !== '12345') {
            return redirect()
                ->route('pigs.index')
                ->with('error', 'Permanent delete failed. Wrong challenge code.');
        }

        $pig->forceDelete();

        return redirect()->route('pigs.index')->with('success', 'Pig permanently deleted successfully.');
    }

    public function removeFromRecords(Request $request, $pig)
    {
        $pig = Pig::withTrashed()
            ->with([
                'healthLogs',
                'sales',
                'mortalityLogs',
                'feedLogs',
                'medications',
                'vaccinations',
                'transfers',
            ])
            ->findOrFail($pig);

        if ($request->input('code') !== 'REMOVE') {
            return redirect()
                ->route('pigs.index')
                ->with('error', 'Removal failed. Wrong security code.');
        }

        DB::transaction(function () use ($pig): void {
            $pig->healthLogs()->delete();
            $pig->sales()->delete();
            $pig->mortalityLogs()->delete();
            $pig->feedLogs()->delete();
            $pig->medications()->delete();
            $pig->vaccinations()->delete();
            $pig->transfers()->delete();

            $pig->forceDelete();
        });

        return redirect()
            ->route('pigs.index')
            ->with('success', 'Pig and all related records were permanently removed.');
    }
}
