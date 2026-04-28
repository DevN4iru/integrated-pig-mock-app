<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Pig;
use App\Models\ReproductionCycle;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class NotificationGenerationService
{
    protected static bool $hasGeneratedThisRequest = false;

    protected ?Collection $activePigCache = null;
    protected ?Collection $dueSoonCycleCache = null;
    protected ?Collection $pregnantCycleCache = null;
    protected ?Collection $farrowedCycleCache = null;
    protected ?array $registeredPigletCountCache = null;

    public function generateFirstWave(): void
    {
        if (self::$hasGeneratedThisRequest) {
            return;
        }

        self::$hasGeneratedThisRequest = true;

        if (!$this->canRun()) {
            return;
        }

        $seenFingerprints = [];

        foreach ($this->protocolNotificationCandidates() as $candidate) {
            $this->syncCandidate($candidate);
            $seenFingerprints[$candidate['fingerprint']] = true;
        }

        foreach ($this->staleWeightNotificationCandidates() as $candidate) {
            $this->syncCandidate($candidate);
            $seenFingerprints[$candidate['fingerprint']] = true;
        }

        foreach ($this->breedingDueSoonNotificationCandidates() as $candidate) {
            $this->syncCandidate($candidate);
            $seenFingerprints[$candidate['fingerprint']] = true;
        }

        foreach ($this->preFarrowMedicationNotificationCandidates() as $candidate) {
            $this->syncCandidate($candidate);
            $seenFingerprints[$candidate['fingerprint']] = true;
        }

        foreach ($this->farrowedPigletsPendingRegistrationCandidates() as $candidate) {
            $this->syncCandidate($candidate);
            $seenFingerprints[$candidate['fingerprint']] = true;
        }

        $this->resolveMissingFirstWaveNotifications(array_keys($seenFingerprints));
    }

    protected function canRun(): bool
    {
        foreach ([
            'notifications',
            'pigs',
            'health_logs',
            'reproduction_cycles',
            'protocol_templates',
            'protocol_rules',
            'protocol_executions',
        ] as $table) {
            if (!Schema::hasTable($table)) {
                return false;
            }
        }

        return true;
    }

    protected function activePigs(): Collection
    {
        if ($this->activePigCache !== null) {
            return $this->activePigCache;
        }

        $this->activePigCache = Pig::query()
            ->activeLifecycle()
            ->with([
                'birthCycle:id,actual_farrow_date',
                'healthLogs' => function ($query) {
                    $query
                        ->select(['id', 'pig_id', 'purpose', 'weight', 'log_date'])
                        ->where('purpose', 'weight_update')
                        ->whereNotNull('weight')
                        ->orderByDesc('log_date')
                        ->orderByDesc('id');
                },
                'protocolExecutions.medication',
                'protocolExecutions.vaccination',
                'reproductionCyclesAsSow' => function ($query) {
                    $query->select(['id', 'sow_id', 'service_date', 'actual_farrow_date']);
                },
            ])
            ->get();

        return $this->activePigCache;
    }

    protected function dueSoonCycles(): Collection
    {
        if ($this->dueSoonCycleCache !== null) {
            return $this->dueSoonCycleCache;
        }

        $this->dueSoonCycleCache = ReproductionCycle::query()
            ->with(['sow'])
            ->dueSoonDashboardCycles()
            ->get();

        return $this->dueSoonCycleCache;
    }

    protected function pregnantCycles(): Collection
    {
        if ($this->pregnantCycleCache !== null) {
            return $this->pregnantCycleCache;
        }

        $this->pregnantCycleCache = ReproductionCycle::query()
            ->with(['sow'])
            ->whereIn('status', [
                ReproductionCycle::STATUS_PREGNANT,
                ReproductionCycle::STATUS_DUE_SOON,
            ])
            ->where('pregnancy_result', ReproductionCycle::PREGNANCY_RESULT_PREGNANT)
            ->whereNotNull('expected_farrow_date')
            ->whereNull('actual_farrow_date')
            ->orderBy('expected_farrow_date')
            ->orderBy('id')
            ->get();

        return $this->pregnantCycleCache;
    }

    protected function farrowedCycles(): Collection
    {
        if ($this->farrowedCycleCache !== null) {
            return $this->farrowedCycleCache;
        }

        $this->farrowedCycleCache = ReproductionCycle::query()
            ->with(['sow'])
            ->where('status', ReproductionCycle::STATUS_FARROWED)
            ->whereNotNull('actual_farrow_date')
            ->whereDate('actual_farrow_date', '<=', now()->toDateString())
            ->where('born_alive', '>', 0)
            ->orderByDesc('actual_farrow_date')
            ->orderByDesc('id')
            ->get();

        return $this->farrowedCycleCache;
    }

    protected function registeredPigletCounts(): array
    {
        if ($this->registeredPigletCountCache !== null) {
            return $this->registeredPigletCountCache;
        }

        $this->registeredPigletCountCache = Pig::withTrashed()
            ->whereNotNull('reproduction_cycle_id')
            ->selectRaw('reproduction_cycle_id, COUNT(*) as aggregate')
            ->groupBy('reproduction_cycle_id')
            ->pluck('aggregate', 'reproduction_cycle_id')
            ->map(fn ($count) => (int) $count)
            ->all();

        return $this->registeredPigletCountCache;
    }

    protected function protocolNotificationCandidates(): array
    {
        $candidates = [];
        $eligibility = new ProtocolEligibilityService();

        foreach ($this->activePigs() as $pig) {
            if (!$eligibility->qualifiesForAnyClientProtocol($pig)) {
                continue;
            }

            $summary = $pig->protocol_summary;

            if (!is_array($summary)) {
                continue;
            }

            foreach ($summary['overdue'] ?? [] as $row) {
                $candidates[] = $this->buildProtocolCandidate($pig, $row, Notification::TYPE_PROTOCOL_OVERDUE);
            }

            foreach ($summary['due_today'] ?? [] as $row) {
                $candidates[] = $this->buildProtocolCandidate($pig, $row, Notification::TYPE_PROTOCOL_DUE_TODAY);
            }
        }

        return $candidates;
    }

    protected function staleWeightNotificationCandidates(): array
    {
        $candidates = [];

        foreach ($this->activePigs() as $pig) {
            if (!$pig->has_stale_weight) {
                continue;
            }

            $latestWeightDate = $this->normalizeDateString($pig->latest_weight_log_date);
            $daysSinceLatestWeight = $pig->days_since_latest_weight;
            $pigLabel = $this->pigLabel($pig);

            $title = $latestWeightDate === null
                ? 'Missing weight record for ' . $pigLabel
                : 'Stale weight record for ' . $pigLabel;

            $message = $latestWeightDate === null
                ? 'This pig has no recorded weight update yet.'
                : 'Latest weight update was ' . (int) $daysSinceLatestWeight . ' day(s) ago on ' . $this->displayDate($latestWeightDate) . '.';

            $candidates[] = [
                'fingerprint' => 'stale_weight:' . $pig->id . ':' . ($latestWeightDate ?? 'none'),
                'type_code' => Notification::TYPE_PIG_STALE_WEIGHT,
                'severity' => Notification::SEVERITY_WARNING,
                'title' => $title,
                'message' => $message,
                'route_name' => 'pigs.show',
                'route_params_json' => ['pig' => $pig->id],
                'pig_id' => $pig->id,
                'reproduction_cycle_id' => null,
                'due_date' => $latestWeightDate,
                'context_json' => [
                    'days_since_latest_weight' => $daysSinceLatestWeight,
                    'latest_weight_log_date' => $latestWeightDate,
                ],
            ];
        }

        return $candidates;
    }

    protected function breedingDueSoonNotificationCandidates(): array
    {
        $candidates = [];

        foreach ($this->dueSoonCycles() as $cycle) {
            $expectedFarrowDate = $this->normalizeDateString($cycle->expected_farrow_date);

            if ($expectedFarrowDate === null) {
                continue;
            }

            $daysUntilDue = now()->startOfDay()->diffInDays(Carbon::parse($expectedFarrowDate)->startOfDay(), false);

            $candidates[] = [
                'fingerprint' => 'breeding_due_soon:' . $cycle->id . ':' . $expectedFarrowDate,
                'type_code' => Notification::TYPE_BREEDING_DUE_SOON,
                'severity' => Notification::SEVERITY_WARNING,
                'title' => 'Farrowing due soon for ' . $this->pigLabel($cycle->sow, $cycle->sow_id),
                'message' => 'Expected farrowing date is ' . $this->displayDate($expectedFarrowDate) . ' (' . max(0, $daysUntilDue) . ' day(s) remaining).',
                'route_name' => 'reproduction-cycles.show',
                'route_params_json' => ['reproductionCycle' => $cycle->id],
                'pig_id' => $cycle->sow_id,
                'reproduction_cycle_id' => $cycle->id,
                'due_date' => $expectedFarrowDate,
                'context_json' => [
                    'expected_farrow_date' => $expectedFarrowDate,
                    'days_until_due' => $daysUntilDue,
                    'status' => $cycle->display_status,
                ],
            ];
        }

        return $candidates;
    }

    protected function preFarrowMedicationSchedule(): array
    {
        return [
            [
                'code' => 'pre_farrow_vaccine_review_35',
                'days_before_farrow' => 35,
                'title' => 'Pre-farrow vaccine/program review',
                'message' => 'Review sow pre-farrow vaccine plan with the farm protocol or veterinarian. This is a prevention reminder before farrowing, not an automatic drug order.',
            ],
            [
                'code' => 'parasite_check_21',
                'days_before_farrow' => 21,
                'title' => 'Pre-farrow parasite/deworming check',
                'message' => 'Check internal/external parasite control plan before farrowing. Follow vet direction and product label timing.',
            ],
            [
                'code' => 'booster_check_14',
                'days_before_farrow' => 14,
                'title' => 'Pre-farrow booster/vaccine check',
                'message' => 'Check if a booster or pre-farrow vaccine action is due. Product choice and dose must follow vet/farm protocol.',
            ],
            [
                'code' => 'final_prefarrow_check_7',
                'days_before_farrow' => 7,
                'title' => 'Final pre-farrow medication and hygiene check',
                'message' => 'Final check for pre-farrow parasite control, udder/belly hygiene, farrowing area readiness, and any vet-directed medication.',
            ],
        ];
    }

    protected function preFarrowMedicationNotificationCandidates(): array
    {
        $candidates = [];
        $today = now()->startOfDay();

        foreach ($this->pregnantCycles() as $cycle) {
            $expectedFarrowDate = $this->normalizeDateString($cycle->expected_farrow_date);

            if ($expectedFarrowDate === null) {
                continue;
            }

            $expectedFarrow = Carbon::parse($expectedFarrowDate)->startOfDay();

            foreach ($this->preFarrowMedicationSchedule() as $schedule) {
                $dueDate = $expectedFarrow
                    ->copy()
                    ->subDays((int) $schedule['days_before_farrow'])
                    ->startOfDay();

                if ($dueDate->gt($today)) {
                    continue;
                }

                $daysLate = $dueDate->diffInDays($today, false);
                $isOverdue = $daysLate > 0;
                $typeCode = $isOverdue
                    ? Notification::TYPE_BREEDING_PRE_FARROW_MEDICATION_OVERDUE
                    : Notification::TYPE_BREEDING_PRE_FARROW_MEDICATION_DUE;

                $candidates[] = [
                    'fingerprint' => 'pre_farrow_medication:' . $cycle->id . ':' . $schedule['code'] . ':' . $dueDate->toDateString(),
                    'type_code' => $typeCode,
                    'severity' => $isOverdue ? Notification::SEVERITY_CRITICAL : Notification::SEVERITY_WARNING,
                    'title' => $schedule['title'] . ' for ' . $this->pigLabel($cycle->sow, $cycle->sow_id),
                    'message' => $schedule['message'] . ' Due date: ' . $this->displayDate($dueDate->toDateString()) . '. Expected farrowing: ' . $this->displayDate($expectedFarrowDate) . '.',
                    'route_name' => 'reproduction-cycles.show',
                    'route_params_json' => ['reproductionCycle' => $cycle->id],
                    'pig_id' => $cycle->sow_id,
                    'reproduction_cycle_id' => $cycle->id,
                    'due_date' => $dueDate->toDateString(),
                    'context_json' => [
                        'schedule_code' => $schedule['code'],
                        'days_before_farrow' => (int) $schedule['days_before_farrow'],
                        'expected_farrow_date' => $expectedFarrowDate,
                        'days_late' => $daysLate,
                    ],
                ];
            }
        }

        return $candidates;
    }

    protected function farrowedPigletsPendingRegistrationCandidates(): array
    {
        $candidates = [];
        $registeredPigletCounts = $this->registeredPigletCounts();

        foreach ($this->farrowedCycles() as $cycle) {
            $actualFarrowDate = $this->normalizeDateString($cycle->actual_farrow_date);
            $expectedRegistrations = max(0, (int) ($cycle->born_alive ?? 0));
            $registeredCount = (int) ($registeredPigletCounts[$cycle->id] ?? 0);
            $pendingCount = max(0, $expectedRegistrations - $registeredCount);

            if ($actualFarrowDate === null || $expectedRegistrations === 0 || $pendingCount === 0) {
                continue;
            }

            $candidates[] = [
                'fingerprint' => 'farrowed_unregistered_piglets:' . $cycle->id . ':' . $actualFarrowDate,
                'type_code' => Notification::TYPE_BREEDING_PIGLETS_UNREGISTERED,
                'severity' => Notification::SEVERITY_WARNING,
                'title' => 'Piglet registration pending for ' . $this->pigLabel($cycle->sow, $cycle->sow_id),
                'message' => $pendingCount . ' of ' . $expectedRegistrations . ' live-born piglet(s) still need registration for the farrowing recorded on ' . $this->displayDate($actualFarrowDate) . '.',
                'route_name' => 'reproduction-cycles.show',
                'route_params_json' => ['reproductionCycle' => $cycle->id],
                'pig_id' => $cycle->sow_id,
                'reproduction_cycle_id' => $cycle->id,
                'due_date' => $actualFarrowDate,
                'context_json' => [
                    'actual_farrow_date' => $actualFarrowDate,
                    'expected_registrations' => $expectedRegistrations,
                    'registered_count' => $registeredCount,
                    'pending_count' => $pendingCount,
                ],
            ];
        }

        return $candidates;
    }

    protected function buildProtocolCandidate(Pig $pig, array $row, string $typeCode): array
    {
        $scheduledForDate = $this->normalizeDateString($row['due_start'] ?? null);
        $dueEndDate = $this->normalizeDateString($row['due_end'] ?? null);
        $ruleId = (int) ($row['rule_id'] ?? 0);
        $pigLabel = $this->pigLabel($pig);
        $isOverdue = $typeCode === Notification::TYPE_PROTOCOL_OVERDUE;

        $title = $isOverdue
            ? 'Protocol overdue for ' . $pigLabel
            : 'Protocol due today for ' . $pigLabel;

        $message = $isOverdue
            ? ($row['action'] ?? 'Protocol action') . ' was scheduled for ' . $this->displayDate($scheduledForDate) . ' and is still unresolved.'
            : ($row['action'] ?? 'Protocol action') . ' is due today for this pig.';

        if ($dueEndDate !== null && $dueEndDate !== $scheduledForDate) {
            $message .= ' Due window ends on ' . $this->displayDate($dueEndDate) . '.';
        }

        return [
            'fingerprint' => ($isOverdue ? 'protocol_overdue:' : 'protocol_due_today:') . $pig->id . ':' . $ruleId . ':' . ($scheduledForDate ?? 'unknown'),
            'type_code' => $typeCode,
            'severity' => $isOverdue ? Notification::SEVERITY_CRITICAL : Notification::SEVERITY_WARNING,
            'title' => $title,
            'message' => $message,
            'route_name' => 'pigs.show',
            'route_params_json' => ['pig' => $pig->id],
            'pig_id' => $pig->id,
            'reproduction_cycle_id' => null,
            'due_date' => $scheduledForDate,
            'context_json' => [
                'protocol_rule_id' => $ruleId,
                'scheduled_for_date' => $scheduledForDate,
                'due_end_date' => $dueEndDate,
                'action' => $row['action'] ?? null,
                'type' => $row['type'] ?? null,
                'requirement' => $row['requirement'] ?? null,
            ],
        ];
    }

    protected function syncCandidate(array $candidate): void
    {
        $notification = Notification::query()->firstOrNew([
            'fingerprint' => $candidate['fingerprint'],
        ]);

        $notification->syncFromFirstWaveSource($candidate);
    }

    protected function resolveMissingFirstWaveNotifications(array $seenFingerprints): void
    {
        Notification::query()
            ->firstWaveGenerated()
            ->whereNull('resolved_at')
            ->when($seenFingerprints !== [], function ($query) use ($seenFingerprints) {
                $query->whereNotIn('fingerprint', $seenFingerprints);
            })
            ->get()
            ->each(function (Notification $notification) {
                $notification->markResolvedFromMissingSource();
            });
    }

    protected function pigLabel(?Pig $pig, ?int $fallbackPigId = null): string
    {
        if ($pig !== null && filled($pig->ear_tag)) {
            return (string) $pig->ear_tag;
        }

        if ($pig !== null && $pig->id !== null) {
            return 'Pig #' . $pig->id;
        }

        return 'Pig #' . ($fallbackPigId ?? '?');
    }

    protected function normalizeDateString(mixed $value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (blank($value)) {
            return null;
        }

        return Carbon::parse((string) $value)->toDateString();
    }

    protected function displayDate(?string $date): string
    {
        if ($date === null) {
            return 'an unknown date';
        }

        return Carbon::parse($date)->format('M d, Y');
    }
}
