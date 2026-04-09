<?php

namespace App\Http\Controllers;

use App\Models\FarmSetting;
use App\Models\Pen;
use App\Models\Pig;
use Illuminate\Http\Request;

class PigController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', 'all');
        $source = (string) $request->query('source', 'all');

        $pigs = Pig::withTrashed()
            ->with(['pen', 'sales', 'mortalityLogs'])
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

        $activePigs = $pigs->filter(function ($pig) use ($status) {
            $isArchived = !is_null($pig->deleted_at);
            $isDead = !$isArchived && $pig->mortalityLogs->isNotEmpty();
            $isSold = !$isArchived && $pig->sales->isNotEmpty();
            $statusLabel = $isDead ? 'dead' : ($isSold ? 'sold' : 'active');

            if ($isArchived) {
                return false;
            }

            return $status === 'all' ? true : $status === $statusLabel;
        })->values();

        $archivedPigs = $pigs->filter(function ($pig) use ($status) {
            $isArchived = !is_null($pig->deleted_at);

            if (!$isArchived) {
                return false;
            }

            return $status === 'all' || $status === 'archived';
        })->values();

        return view('pigs.index', compact('activePigs', 'archivedPigs', 'search', 'status', 'source'));
    }

    public function create()
    {
        $pens = Pen::orderBy('name')->get();
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

        $pen = Pen::withCount('pigs')->findOrFail($validated['pen_id']);

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
            ->with(['pen', 'healthLogs', 'medications', 'vaccinations', 'mortalityLogs', 'sales', 'feedLogs'])
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

        $pens = Pen::orderBy('name')->get();

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

        $newPen = Pen::withCount('pigs')->findOrFail($validated['pen_id']);

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
}
