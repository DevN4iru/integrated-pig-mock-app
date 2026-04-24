<?php

namespace App\Http\Controllers;

use App\Models\ProtocolTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProtocolProgramController extends Controller
{
    public function index()
    {
        $programs = ProtocolTemplate::query()
            ->withCount([
                'rules',
                'activeRules',
            ])
            ->orderBy('target_type')
            ->orderBy('code')
            ->get();

        return view('protocol-programs.index', compact('programs'));
    }

    public function show(ProtocolTemplate $protocolTemplate)
    {
        $protocolTemplate->load([
            'rules' => function ($query) {
                $query->orderBy('sequence_order')->orderBy('id');
            },
        ]);

        $rules = $protocolTemplate->rules->values();

        $medicationProgramRules = $rules
            ->filter(fn ($rule) => $rule->action_type !== 'vaccination')
            ->values();

        $vaccinationProgramRules = $rules
            ->filter(fn ($rule) => $rule->action_type === 'vaccination')
            ->values();

        $medicationGuideRows = $medicationProgramRules
            ->map(function ($rule) {
                $rows = [];

                if ($rule->product_note) {
                    $rows[] = [
                        'label' => 'Product / Option',
                        'content' => $rule->action_name . ' — ' . $rule->product_note,
                    ];
                }

                if ($rule->dosage_note) {
                    $rows[] = [
                        'label' => 'Dosage Note',
                        'content' => $rule->action_name . ' — ' . $rule->dosage_note,
                    ];
                }

                if ($rule->administration_note) {
                    $rows[] = [
                        'label' => 'Administration Note',
                        'content' => $rule->action_name . ' — ' . $rule->administration_note,
                    ];
                }

                if ($rule->market_note) {
                    $rows[] = [
                        'label' => 'Alternative / Market Note',
                        'content' => $rule->action_name . ' — ' . $rule->market_note,
                    ];
                }

                if ($rule->condition_note) {
                    $rows[] = [
                        'label' => 'Condition',
                        'content' => $rule->action_name . ' — ' . $rule->condition_note,
                    ];
                }

                return $rows;
            })
            ->flatten(1)
            ->unique(fn ($row) => $row['label'] . '|' . $row['content'])
            ->values();

        $vaccinationGuideRows = $vaccinationProgramRules
            ->map(function ($rule) {
                $rows = [];

                if ($rule->product_note) {
                    $rows[] = [
                        'label' => 'Product / Option',
                        'content' => $rule->action_name . ' — ' . $rule->product_note,
                    ];
                }

                if ($rule->dosage_note) {
                    $rows[] = [
                        'label' => 'Dosage Note',
                        'content' => $rule->action_name . ' — ' . $rule->dosage_note,
                    ];
                }

                if ($rule->administration_note) {
                    $rows[] = [
                        'label' => 'Administration Note',
                        'content' => $rule->action_name . ' — ' . $rule->administration_note,
                    ];
                }

                if ($rule->market_note) {
                    $rows[] = [
                        'label' => 'Alternative / Market Note',
                        'content' => $rule->action_name . ' — ' . $rule->market_note,
                    ];
                }

                if ($rule->condition_note) {
                    $rows[] = [
                        'label' => 'Condition',
                        'content' => $rule->action_name . ' — ' . $rule->condition_note,
                    ];
                }

                return $rows;
            })
            ->flatten(1)
            ->unique(fn ($row) => $row['label'] . '|' . $row['content'])
            ->values();

        $whyExplanationRows = match ($protocolTemplate->target_type) {
            ProtocolTemplate::TARGET_PIGLET => collect([
                'Colostrum, warmth, dry body, clean navel, and strong suckling remain the true early piglet survival foundation.',
                'Iron support matters because sow milk does not provide enough iron for rapid piglet growth.',
                'Scours is not one disease. Treatment and support should follow the reason, not one universal blanket routine.',
                'Castration and weaning should not be treated as medication-only items. They belong in procedure or management context.',
                'Some timing depends on exact product label, herd status, and veterinary direction, so guide content must stay separate from rigid schedule truth.',
            ]),
            ProtocolTemplate::TARGET_LACTATING_SOW => collect([
                'Fresh-sow monitoring is centered on appetite, water intake, temperature, udder, discharge, nursing, and piglet performance.',
                'Parvo and reproductive vaccines belong to breeding-stage logic, not blindly as fixed lactation-day shots.',
                'Illness-driven treatment stays sign-based and clinical, not a universal blanket routine for every sow.',
                'Weaning is a management event and should stay visible because it affects the next breeding-stage workflow.',
                'Exact product label, veterinarian advice, and local disease program still govern real timing and product choice.',
            ]),
            default => collect([
                'Guide content should remain separate from rigid operational schedule truth.',
                'Some timing and product use depend on exact label, herd program, and farm context.',
            ]),
        };

        $previewRules = $rules->take(4)->values();

        return view('protocol-programs.show', compact(
            'protocolTemplate',
            'rules',
            'medicationProgramRules',
            'vaccinationProgramRules',
            'medicationGuideRows',
            'vaccinationGuideRows',
            'whyExplanationRows',
            'previewRules',
        ));
    }

    public function edit(ProtocolTemplate $protocolTemplate)
    {
        $protocolTemplate->load([
            'rules' => function ($query) {
                $query->orderBy('sequence_order')->orderBy('id');
            },
        ]);

        $rules = $protocolTemplate->rules->values();

        return view('protocol-programs.edit', compact('protocolTemplate', 'rules'));
    }

    public function update(Request $request, ProtocolTemplate $protocolTemplate)
    {
        $protocolTemplate->load([
            'rules' => function ($query) {
                $query->orderBy('sequence_order')->orderBy('id');
            },
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'rules' => ['nullable', 'array'],
            'rules.*.id' => ['required', 'integer'],
            'rules.*.product_note' => ['nullable', 'string', 'max:5000'],
            'rules.*.dosage_note' => ['nullable', 'string', 'max:5000'],
            'rules.*.administration_note' => ['nullable', 'string', 'max:5000'],
            'rules.*.market_note' => ['nullable', 'string', 'max:5000'],
            'rules.*.condition_note' => ['nullable', 'string', 'max:5000'],
        ]);

        $ruleInputs = collect($validated['rules'] ?? [])
            ->keyBy(fn ($ruleInput) => (int) $ruleInput['id']);

        $allowedRuleIds = $protocolTemplate->rules
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $submittedRuleIds = $ruleInputs
            ->keys()
            ->map(fn ($id) => (int) $id)
            ->all();

        if (array_diff($submittedRuleIds, $allowedRuleIds)) {
            return back()
                ->withErrors(['rules' => 'One or more submitted rules do not belong to this protocol program.'])
                ->withInput();
        }

        DB::transaction(function () use ($protocolTemplate, $validated, $ruleInputs): void {
            $protocolTemplate->update([
                'name' => $validated['name'],
                'description' => $this->normalizeNullableString($validated['description'] ?? null),
            ]);

            foreach ($protocolTemplate->rules as $rule) {
                $ruleInput = $ruleInputs->get((int) $rule->id);

                if (!$ruleInput) {
                    continue;
                }

                $rule->update([
                    'product_note' => $this->normalizeNullableString($ruleInput['product_note'] ?? null),
                    'dosage_note' => $this->normalizeNullableString($ruleInput['dosage_note'] ?? null),
                    'administration_note' => $this->normalizeNullableString($ruleInput['administration_note'] ?? null),
                    'market_note' => $this->normalizeNullableString($ruleInput['market_note'] ?? null),
                    'condition_note' => $this->normalizeNullableString($ruleInput['condition_note'] ?? null),
                ]);
            }
        });

        return redirect()
            ->route('protocol-programs.show', $protocolTemplate)
            ->with('success', 'Protocol program display and guide content updated. Scheduling and execution logic were not changed.');
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
