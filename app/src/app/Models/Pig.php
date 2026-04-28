<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pig extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'ear_tag',
        'breed',
        'sex',
        'pen_id',
        'pen_location',
        'pig_source',
        'age',
        'mother_sow_id',
        'sire_boar_id',
        'reproduction_cycle_id',
        'date_added',
        'latest_weight',
        'asset_value',
        'exclude_from_value_computation',
    ];

    protected $casts = [
        'age' => 'integer',
        'date_added' => 'date',
        'latest_weight' => 'decimal:2',
        'asset_value' => 'decimal:2',
        'exclude_from_value_computation' => 'boolean',
    ];

    protected $appends = [
        'computed_weight',
        'computed_asset_value',
        'active_live_value',
        'latest_weight_log_date',
        'days_since_latest_weight',
        'has_stale_weight',
        'positive_gain_from_start',
        'recent_weight_trend_direction',
        'recent_weight_trend_symbol',
        'recent_weight_trend_label',
        'lifecycle_state',
        'is_archived_lifecycle',
        'is_dead_lifecycle',
        'is_sold_lifecycle',
        'is_active_lifecycle',
        'age_display',
        'weight_gain',
        'daily_gain',
        'growth_status',
        'total_feed_cost',
        'total_medication_cost',
        'total_vaccination_cost',
        'total_semen_cost',
        'total_breeding_service_cost',
        'total_breeding_cost',
        'total_care_liability',
        'total_operating_cost',
        'total_feed_kg',
        'feed_efficiency',
        'cost_per_kg_gain',
        'performance_status',
        'protocol_summary',
        'breeding_status_label',
        'breeding_status_badge_class',
    ];

    public function pen()
    {
        return $this->belongsTo(Pen::class);
    }

    public function motherSow()
    {
        return $this->belongsTo(Pig::class, 'mother_sow_id');
    }

    public function sireBoar()
    {
        return $this->belongsTo(Pig::class, 'sire_boar_id');
    }

    public function birthCycle()
    {
        return $this->belongsTo(ReproductionCycle::class, 'reproduction_cycle_id');
    }

    public function birthedPiglets()
    {
        return $this->hasMany(Pig::class, 'mother_sow_id')
            ->orderBy('date_added')
            ->orderBy('id');
    }

    public function siredPiglets()
    {
        return $this->hasMany(Pig::class, 'sire_boar_id')
            ->orderBy('date_added')
            ->orderBy('id');
    }

    protected function loadLineageAncestors(): void
    {
        $this->loadMissing([
            'motherSow',
            'sireBoar',
            'motherSow.motherSow',
            'motherSow.sireBoar',
            'sireBoar.motherSow',
            'sireBoar.sireBoar',
        ]);
    }

    protected function lineageParentIds(): array
    {
        return collect([
            $this->mother_sow_id,
            $this->sire_boar_id,
        ])
            ->filter(fn ($id) => $id !== null)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    protected function lineageGrandparentIds(): array
    {
        $this->loadLineageAncestors();

        return collect([
            $this->motherSow?->mother_sow_id,
            $this->motherSow?->sire_boar_id,
            $this->sireBoar?->mother_sow_id,
            $this->sireBoar?->sire_boar_id,
        ])
            ->filter(fn ($id) => $id !== null)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    public function hasProvenDirectParentOffspringRelationWith(Pig $other): bool
    {
        return in_array((int) $other->id, $this->lineageParentIds(), true)
            || in_array((int) $this->id, $other->lineageParentIds(), true);
    }

    public function hasProvenFullSiblingRelationWith(Pig $other): bool
    {
        if ($this->mother_sow_id === null || $this->sire_boar_id === null) {
            return false;
        }

        if ($other->mother_sow_id === null || $other->sire_boar_id === null) {
            return false;
        }

        return (int) $this->mother_sow_id === (int) $other->mother_sow_id
            && (int) $this->sire_boar_id === (int) $other->sire_boar_id;
    }

    public function hasProvenHalfSiblingRelationWith(Pig $other): bool
    {
        if ($this->hasProvenFullSiblingRelationWith($other)) {
            return false;
        }

        return !empty(array_intersect(
            $this->lineageParentIds(),
            $other->lineageParentIds()
        ));
    }

    public function hasProvenGrandparentGrandchildRelationWith(Pig $other): bool
    {
        return in_array((int) $other->id, $this->lineageGrandparentIds(), true)
            || in_array((int) $this->id, $other->lineageGrandparentIds(), true);
    }

    public function breedingPairingGuardWith(Pig $other): array
    {
        $this->loadLineageAncestors();
        $other->loadLineageAncestors();

        if ((int) $this->id === (int) $other->id) {
            return [
                'blocked' => true,
                'code' => 'self_pairing',
                'message' => 'This sow and boar are the same pig. Self pairing is blocked.',
            ];
        }

        if ($this->hasProvenDirectParentOffspringRelationWith($other)) {
            return [
                'blocked' => true,
                'code' => 'parent_offspring',
                'message' => 'This pairing is blocked because the sow and boar have a proven parent-offspring relationship.',
            ];
        }

        if ($this->hasProvenFullSiblingRelationWith($other)) {
            return [
                'blocked' => true,
                'code' => 'full_siblings',
                'message' => 'This pairing is blocked because the sow and boar are proven full siblings.',
            ];
        }

        if ($this->hasProvenHalfSiblingRelationWith($other)) {
            return [
                'blocked' => true,
                'code' => 'half_siblings',
                'message' => 'This pairing is blocked because the sow and boar share a proven parent.',
            ];
        }

        if ($this->hasProvenGrandparentGrandchildRelationWith($other)) {
            return [
                'blocked' => true,
                'code' => 'grandparent_grandchild',
                'message' => 'This pairing is blocked because the sow and boar have a proven grandparent-grandchild relationship.',
            ];
        }

        return [
            'blocked' => false,
            'code' => null,
            'message' => null,
        ];
    }

    public function healthLogs()
    {
        return $this->hasMany(HealthLog::class);
    }

    public function medications()
    {
        return $this->hasMany(Medication::class)->latest();
    }

    public function vaccinations()
    {
        return $this->hasMany(Vaccination::class)->latest();
    }

    public function mortalityLogs()
    {
        return $this->hasMany(MortalityLog::class)->latest();
    }

    public function sales()
    {
        return $this->hasMany(Sale::class)->latest();
    }

    public function feedLogs()
    {
        return $this->hasMany(FeedLog::class)->latest();
    }

    public function transfers()
    {
        return $this->hasMany(PigTransfer::class)
            ->orderByDesc('transfer_date')
            ->orderByDesc('id');
    }

    public function reproductionCyclesAsSow()
    {
        return $this->hasMany(ReproductionCycle::class, 'sow_id')
            ->orderByDesc('service_date')
            ->orderByDesc('id');
    }

    public function reproductionCyclesAsBoar()
    {
        return $this->hasMany(ReproductionCycle::class, 'boar_id')
            ->orderByDesc('service_date')
            ->orderByDesc('id');
    }

    public function latestBreedingRecordForStatus(): ?ReproductionCycle
    {
        if (strtolower((string) $this->sex) !== 'female') {
            return null;
        }

        if ($this->relationLoaded('reproductionCyclesAsSow')) {
            return $this->reproductionCyclesAsSow
                ->sortByDesc(fn ($cycle) => sprintf(
                    '%s-%010d',
                    optional($cycle->service_date)->format('Y-m-d') ?? '',
                    (int) $cycle->id
                ))
                ->first();
        }

        return $this->reproductionCyclesAsSow()
            ->select([
                'id',
                'sow_id',
                'service_date',
                'pregnancy_result',
                'expected_farrow_date',
                'actual_farrow_date',
                'status',
            ])
            ->first();
    }

    public function breedingStatusLabel(): string
    {
        if (strtolower((string) $this->sex) !== 'female') {
            return '—';
        }

        $cycle = $this->latestBreedingRecordForStatus();

        if (!$cycle) {
            return 'No Breeding Record Yet';
        }

        $status = $cycle->display_status;

        if ($cycle->actual_farrow_date) {
            return 'Farrowed';
        }

        return match ($status) {
            ReproductionCycle::STATUS_DUE_SOON => 'Due Soon',
            ReproductionCycle::STATUS_PREGNANT => 'Pregnant',
            ReproductionCycle::STATUS_RETURNED_TO_HEAT => 'In Heat',
            ReproductionCycle::STATUS_SERVICED => 'Serviced',
            default => $cycle->display_status_label,
        };
    }

    public function breedingStatusBadgeClass(): string
    {
        if (strtolower((string) $this->sex) !== 'female') {
            return 'gray';
        }

        $cycle = $this->latestBreedingRecordForStatus();

        if (!$cycle) {
            return 'gray';
        }

        $status = $cycle->display_status;

        if ($cycle->actual_farrow_date) {
            return 'green';
        }

        return match ($status) {
            ReproductionCycle::STATUS_DUE_SOON => 'orange',
            ReproductionCycle::STATUS_PREGNANT => 'green',
            ReproductionCycle::STATUS_RETURNED_TO_HEAT,
            ReproductionCycle::STATUS_NOT_PREGNANT => 'red',
            ReproductionCycle::STATUS_SERVICED => 'blue',
            default => 'gray',
        };
    }

    public function getBreedingStatusLabelAttribute(): string
    {
        return $this->breedingStatusLabel();
    }

    public function getBreedingStatusBadgeClassAttribute(): string
    {
        return $this->breedingStatusBadgeClass();
    }


    public function protocolExecutions()
    {
        return $this->hasMany(ProtocolExecution::class)
            ->orderBy('scheduled_for_date')
            ->orderBy('id');
    }

    public function scopeActiveLifecycle($query)
    {
        return $query
            ->whereNull($this->qualifyColumn('deleted_at'))
            ->whereDoesntHave('sales')
            ->whereDoesntHave('mortalityLogs');
    }

    public function scopeSoldLifecycle($query)
    {
        return $query
            ->whereNull($this->qualifyColumn('deleted_at'))
            ->whereDoesntHave('mortalityLogs')
            ->whereHas('sales');
    }

    public function scopeDeadLifecycle($query)
    {
        return $query
            ->whereNull($this->qualifyColumn('deleted_at'))
            ->whereHas('mortalityLogs');
    }

    public function scopeArchivedLifecycle($query)
    {
        return $query->onlyTrashed();
    }

    public static function preservedAssetValueSeedFromWeight(float $weight): float
    {
        return 0.0;
    }

    public function preserveCurrentDisplayValueForArchive(): void
    {
        $this->forceFill([
            'asset_value' => (float) $this->display_value_amount,
        ])->save();
    }

    protected function activeProtocolTemplateByTarget(string $targetType): ?ProtocolTemplate
    {
        return ProtocolTemplate::query()
            ->where('target_type', $targetType)
            ->where('is_active', true)
            ->first();
    }

    protected function latestFarrowingCycle(): ?ReproductionCycle
    {
        if ($this->relationLoaded('reproductionCyclesAsSow')) {
            return $this->reproductionCyclesAsSow
                ->filter(fn ($cycle) => $cycle->actual_farrow_date !== null)
                ->sortByDesc(fn ($cycle) => sprintf(
                    '%s-%010d',
                    optional($cycle->actual_farrow_date)->format('Y-m-d') ?? '',
                    (int) $cycle->id
                ))
                ->first();
        }

        return $this->reproductionCyclesAsSow()
            ->whereNotNull('actual_farrow_date')
            ->orderByDesc('actual_farrow_date')
            ->orderByDesc('id')
            ->first();
    }

    protected function hasSowReproductionHistory(): bool
    {
        if ($this->relationLoaded('reproductionCyclesAsSow')) {
            return $this->reproductionCyclesAsSow->isNotEmpty();
        }

        return $this->reproductionCyclesAsSow()->exists();
    }

    protected function linkedBirthActualFarrowDate(): ?Carbon
    {
        if ($this->reproduction_cycle_id === null) {
            return null;
        }

        if ($this->relationLoaded('birthCycle')) {
            return $this->birthCycle?->actual_farrow_date
                ? Carbon::parse($this->birthCycle->actual_farrow_date)->startOfDay()
                : null;
        }

        $birthCycle = $this->birthCycle()
            ->whereNotNull('actual_farrow_date')
            ->first(['id', 'actual_farrow_date']);

        return $birthCycle?->actual_farrow_date
            ? Carbon::parse($birthCycle->actual_farrow_date)->startOfDay()
            : null;
    }

    protected function approximateBirthAnchorDate(): ?Carbon
    {
        if (!$this->date_added) {
            return null;
        }

        $dateAdded = Carbon::parse($this->date_added)->startOfDay();
        $storedAgeDays = max(0, (int) ($this->age ?? 0));

        return $dateAdded->copy()->subDays($storedAgeDays);
    }

    protected function protocolCoverageEndDay(?ProtocolTemplate $template): ?int
    {
        if (!$template) {
            return null;
        }

        $rules = $template->relationLoaded('rules')
            ? $template->rules->where('is_active', true)->values()
            : $template->rules()->where('is_active', true)->get();

        if ($rules->isEmpty()) {
            return null;
        }

        return $rules->max(function ($rule) {
            return $rule->day_offset_end !== null
                ? (int) $rule->day_offset_end
                : (int) $rule->day_offset_start;
        });
    }

    protected function isWithinProtocolCoverageWindow(?ProtocolTemplate $template, ?Carbon $anchorDate): bool
    {
        if (!$template || !$anchorDate) {
            return false;
        }

        $coverageEndDay = $this->protocolCoverageEndDay($template);

        if ($coverageEndDay === null) {
            return false;
        }

        $today = Carbon::today();
        $windowStart = $anchorDate->copy()->startOfDay();
        $windowEnd = $anchorDate->copy()->addDays($coverageEndDay)->startOfDay();

        return $today->greaterThanOrEqualTo($windowStart)
            && $today->lessThanOrEqualTo($windowEnd);
    }

    protected function hasProtocolAnchorStarted(?ProtocolTemplate $template, ?Carbon $anchorDate): bool
    {
        if (!$template || !$anchorDate) {
            return false;
        }

        return Carbon::today()->greaterThanOrEqualTo($anchorDate->copy()->startOfDay());
    }

    protected function qualifiesForLactatingSowProtocol(?ProtocolTemplate $template = null): bool
    {
        if (strtolower((string) $this->sex) !== 'female') {
            return false;
        }

        $template = $template ?: $this->activeProtocolTemplateByTarget(ProtocolTemplate::TARGET_LACTATING_SOW);

        if (!$template) {
            return false;
        }

        $anchorDate = $this->resolveProtocolAnchorDate($template);

        return $this->hasProtocolAnchorStarted($template, $anchorDate);
    }

    protected function qualifiesForPigletProtocol(?ProtocolTemplate $template = null): bool
    {
        if (strtolower((string) $this->pig_source) !== 'birthed') {
            return false;
        }

        if ($this->reproduction_cycle_id === null) {
            return false;
        }

        if ($this->qualifiesForLactatingSowProtocol()) {
            return false;
        }

        if ($this->hasSowReproductionHistory()) {
            return false;
        }

        $template = $template ?: $this->activeProtocolTemplateByTarget(ProtocolTemplate::TARGET_PIGLET);

        if (!$template) {
            return false;
        }

        $anchorDate = $this->resolveProtocolAnchorDate($template);

        return $this->hasProtocolAnchorStarted($template, $anchorDate);
    }

    protected function resolveProtocolTemplate(): ?ProtocolTemplate
    {
        $lactatingSowTemplate = $this->activeProtocolTemplateByTarget(ProtocolTemplate::TARGET_LACTATING_SOW);

        if ($this->qualifiesForLactatingSowProtocol($lactatingSowTemplate)) {
            return $lactatingSowTemplate;
        }

        $pigletTemplate = $this->activeProtocolTemplateByTarget(ProtocolTemplate::TARGET_PIGLET);

        if ($this->qualifiesForPigletProtocol($pigletTemplate)) {
            return $pigletTemplate;
        }

        return null;
    }

    protected function resolveProtocolAnchorDate(?ProtocolTemplate $template): ?Carbon
    {
        if (!$template) {
            return null;
        }

        if ($template->anchor_event === ProtocolTemplate::ANCHOR_BIRTH) {
            return $this->linkedBirthActualFarrowDate();
        }

        if ($template->anchor_event === ProtocolTemplate::ANCHOR_FARROWING) {
            $cycle = $this->latestFarrowingCycle();

            return $cycle?->actual_farrow_date
                ? Carbon::parse($cycle->actual_farrow_date)->startOfDay()
                : null;
        }

        return null;
    }

    protected function evaluateProtocolCondition(ProtocolRule $rule): bool
    {
        if (!$rule->condition_key) {
            return true;
        }

        return match ($rule->condition_key) {
            ProtocolRule::CONDITION_SEX_MALE => strtolower((string) $this->sex) === 'male',
            default => true,
        };
    }

    protected function protocolExecutionMap(): array
    {
        if ($this->relationLoaded('protocolExecutions')) {
            $executions = $this->protocolExecutions;
            $executions->loadMissing(['medication', 'vaccination']);
        } else {
            $executions = $this->protocolExecutions()
                ->with(['medication', 'vaccination'])
                ->get();
        }

        $map = [];

        foreach ($executions as $execution) {
            $key = $execution->protocol_rule_id . '|' . $execution->scheduled_for_date?->toDateString();
            $map[$key] = $execution;
        }

        return $map;
    }

    public function getProtocolSummaryAttribute(): ?array
    {
        $template = $this->resolveProtocolTemplate();

        if (!$template) {
            return null;
        }

        $template->loadMissing(['rules' => function ($query) {
            $query->where('is_active', true)
                ->orderBy('sequence_order')
                ->orderBy('id');
        }]);

        $anchorDate = $this->resolveProtocolAnchorDate($template);

        if (!$anchorDate) {
            return null;
        }

        $today = Carbon::today();
        $executionMap = $this->protocolExecutionMap();

        $dueToday = [];
        $upcoming = [];
        $overdue = [];

        foreach ($template->rules as $rule) {
            if (!$this->evaluateProtocolCondition($rule)) {
                continue;
            }

            $start = $anchorDate->copy()->addDays((int) $rule->day_offset_start)->startOfDay();
            $end = $rule->day_offset_end !== null
                ? $anchorDate->copy()->addDays((int) $rule->day_offset_end)->startOfDay()
                : $start->copy();

            $occurrenceKey = $rule->id . '|' . $start->toDateString();
            $execution = $executionMap[$occurrenceKey] ?? null;

            if ($execution && $execution->isResolved()) {
                continue;
            }

            $linkedMedication = $execution?->medication;
            $linkedVaccination = $execution?->vaccination;

            $actualProductName = null;
            $actualDose = null;
            $actualCost = null;
            $actualNotes = null;

            if ($rule->action_type === ProtocolRule::ACTION_MEDICATION && $linkedMedication) {
                $actualProductName = $linkedMedication->medication_name;
                $actualDose = $linkedMedication->dosage;
                $actualCost = (float) $linkedMedication->cost;
                $actualNotes = $linkedMedication->notes;
            }

            if ($rule->action_type === ProtocolRule::ACTION_VACCINATION && $linkedVaccination) {
                $actualProductName = $linkedVaccination->vaccine_name;
                $actualDose = $linkedVaccination->dose;
                $actualCost = (float) $linkedVaccination->cost;
                $actualNotes = $linkedVaccination->notes;
            }

            $row = [
                'rule_id' => $rule->id,
                'action' => $rule->action_name,
                'type' => $rule->action_type,
                'requirement' => $rule->requirement_level,
                'due_start' => $start->toDateString(),
                'due_end' => $end->toDateString(),

                'product_note' => $rule->product_note,
                'dosage_note' => $rule->dosage_note,
                'administration_note' => $rule->administration_note,
                'market_note' => $rule->market_note,
                'condition_note' => $rule->condition_note,

                'execution_status' => $execution?->status,
                'executed_date' => $execution?->executed_date?->toDateString(),
                'execution_notes' => $execution?->notes,

                'actual_product_name' => $actualProductName ?? $rule->product_note,
                'actual_dose' => $actualDose ?? $rule->dosage_note,
                'actual_cost' => $actualCost,
                'actual_notes' => $actualNotes,
                'has_linked_admin_log' => (bool) ($linkedMedication || $linkedVaccination),
            ];

            if ($today->between($start, $end)) {
                $dueToday[] = $row;
            } elseif ($today->lt($start)) {
                $upcoming[] = $row;
            } elseif ($today->gt($end)) {
                $overdue[] = $row;
            }
        }

        return [
            'template_code' => $template->code,
            'anchor_date' => $anchorDate->toDateString(),
            'due_today' => $dueToday,
            'upcoming' => $upcoming,
            'overdue' => $overdue,
        ];
    }

    public function getProtocolExecutionHistoryAttribute(): array
    {
        if ($this->relationLoaded('protocolExecutions')) {
            $executions = $this->protocolExecutions;
            $executions->loadMissing(['rule.template', 'medication', 'vaccination']);
        } else {
            $executions = $this->protocolExecutions()
                ->with(['rule.template', 'medication', 'vaccination'])
                ->orderByDesc('scheduled_for_date')
                ->orderByDesc('id')
                ->get();
        }

        return $executions
            ->sortByDesc(fn ($execution) => sprintf(
                '%s-%010d',
                optional($execution->scheduled_for_date)->format('Y-m-d') ?? (string) $execution->scheduled_for_date,
                (int) $execution->id
            ))
            ->values()
            ->map(function ($execution) {
                $rule = $execution->rule;
                $linkedMedication = $execution->medication;
                $linkedVaccination = $execution->vaccination;

                $actualProductName = null;
                $actualDose = null;
                $actualCost = null;
                $actualNotes = null;

                if ($rule?->action_type === ProtocolRule::ACTION_MEDICATION && $linkedMedication) {
                    $actualProductName = $linkedMedication->medication_name;
                    $actualDose = $linkedMedication->dosage;
                    $actualCost = (float) $linkedMedication->cost;
                    $actualNotes = $linkedMedication->notes;
                }

                if ($rule?->action_type === ProtocolRule::ACTION_VACCINATION && $linkedVaccination) {
                    $actualProductName = $linkedVaccination->vaccine_name;
                    $actualDose = $linkedVaccination->dose;
                    $actualCost = (float) $linkedVaccination->cost;
                    $actualNotes = $linkedVaccination->notes;
                }

                return [
                    'execution_id' => $execution->id,
                    'rule_id' => $execution->protocol_rule_id,
                    'template_code' => $rule?->template?->code,
                    'action' => $rule?->action_name ?? '—',
                    'type' => $rule?->action_type,
                    'requirement' => $rule?->requirement_level,
                    'scheduled_for_date' => $execution->scheduled_for_date?->toDateString(),
                    'status' => $execution->status,
                    'status_label' => $execution->status_label,
                    'executed_date' => $execution->executed_date?->toDateString(),
                    'notes' => $execution->notes,

                    'product_note' => $rule?->product_note,
                    'dosage_note' => $rule?->dosage_note,
                    'administration_note' => $rule?->administration_note,
                    'market_note' => $rule?->market_note,
                    'condition_note' => $rule?->condition_note,

                    'actual_product_name' => $actualProductName,
                    'actual_dose' => $actualDose,
                    'actual_cost' => $actualCost,
                    'actual_notes' => $actualNotes,
                    'has_linked_admin_log' => (bool) ($linkedMedication || $linkedVaccination),
                    'is_resolved' => $execution->isResolved(),
                ];
            })
            ->all();
    }

    protected function relationHasAny(string $relation): bool
    {
        if ($this->relationLoaded($relation)) {
            return $this->{$relation}->isNotEmpty();
        }

        return $this->{$relation}()->exists();
    }

    public function getLifecycleStateAttribute(): string
    {
        if ($this->trashed()) {
            return 'archived';
        }

        if ($this->relationHasAny('mortalityLogs')) {
            return 'dead';
        }

        if ($this->relationHasAny('sales')) {
            return 'sold';
        }

        return 'active';
    }

    public function getIsArchivedLifecycleAttribute(): bool
    {
        return $this->lifecycle_state === 'archived';
    }

    public function getIsDeadLifecycleAttribute(): bool
    {
        return $this->lifecycle_state === 'dead';
    }

    public function getIsSoldLifecycleAttribute(): bool
    {
        return $this->lifecycle_state === 'sold';
    }

    public function getIsActiveLifecycleAttribute(): bool
    {
        return $this->lifecycle_state === 'active';
    }

    protected function latestMortalityRecord(): ?MortalityLog
    {
        if ($this->relationLoaded('mortalityLogs')) {
            return $this->mortalityLogs
                ->sortByDesc(fn ($log) => sprintf(
                    '%s-%010d',
                    optional($log->death_date)->format('Y-m-d') ?? (string) $log->death_date,
                    (int) $log->id
                ))
                ->first();
        }

        return $this->mortalityLogs()
            ->orderByDesc('death_date')
            ->orderByDesc('id')
            ->first();
    }

    protected function latestSaleRecord(): ?Sale
    {
        if ($this->relationLoaded('sales')) {
            return $this->sales
                ->sortByDesc(fn ($sale) => sprintf(
                    '%s-%010d',
                    optional($sale->sold_date)->format('Y-m-d') ?? (string) $sale->sold_date,
                    (int) $sale->id
                ))
                ->first();
        }

        return $this->sales()
            ->orderByDesc('sold_date')
            ->orderByDesc('id')
            ->first();
    }

    public function isOperationallyLocked(): bool
    {
        return !$this->is_active_lifecycle;
    }

    public function operationalLockState(): ?string
    {
        return $this->is_active_lifecycle
            ? null
            : $this->lifecycle_state;
    }

    public function operationalLockMessage(string $moduleLabel = 'records'): string
    {
        $moduleLabel = trim($moduleLabel) !== '' ? trim($moduleLabel) : 'records';

        return match ($this->operationalLockState()) {
            'archived' => 'This pig is archived. ' . ucfirst($moduleLabel) . ' are locked until the pig is restored.',
            'dead' => 'This pig already has a mortality record. ' . ucfirst($moduleLabel) . ' are locked to protect lifecycle integrity.',
            'sold' => 'This pig already has a sale record. ' . ucfirst($moduleLabel) . ' are locked to protect lifecycle integrity.',
            default => 'This pig is not available for ' . $moduleLabel . '.',
        };
    }

    protected function orderedWeightLogs()
    {
        return $this->healthLogs()
            ->where('purpose', 'weight_update')
            ->whereNotNull('weight')
            ->orderByDesc('log_date')
            ->orderByDesc('id');
    }

    protected function chronologicalWeightLogs()
    {
        return $this->healthLogs()
            ->where('purpose', 'weight_update')
            ->whereNotNull('weight')
            ->orderBy('log_date')
            ->orderBy('id');
    }

    protected function loadedOrderedWeightLogs()
    {
        return $this->healthLogs
            ->filter(fn ($log) => $log->purpose === 'weight_update' && $log->weight !== null)
            ->sortByDesc(fn ($log) => sprintf(
                '%s-%010d',
                (string) ($log->log_date ?? ''),
                (int) $log->id
            ))
            ->values();
    }

    protected function loadedChronologicalWeightLogs()
    {
        return $this->healthLogs
            ->filter(fn ($log) => $log->purpose === 'weight_update' && $log->weight !== null)
            ->sortBy(fn ($log) => sprintf(
                '%s-%010d',
                (string) ($log->log_date ?? ''),
                (int) $log->id
            ))
            ->values();
    }

    protected function currentBaselineWeight(): ?float
    {
        return $this->latest_weight !== null && $this->latest_weight !== ''
            ? (float) $this->latest_weight
            : null;
    }

    protected function recentWeightTrendSnapshot(): array
    {
        $logs = $this->relationLoaded('healthLogs')
            ? $this->loadedOrderedWeightLogs()
            : $this->orderedWeightLogs()->take(2)->get()->values();

        $latest = $logs->get(0);
        $previous = $logs->get(1);

        if ($latest && $previous) {
            if ((float) $latest->weight > (float) $previous->weight) {
                return [
                    'direction' => 'up',
                    'symbol' => '↑',
                    'label' => 'Increasing',
                ];
            }

            if ((float) $latest->weight < (float) $previous->weight) {
                return [
                    'direction' => 'down',
                    'symbol' => '↓',
                    'label' => 'Dropping',
                ];
            }

            return [
                'direction' => 'flat',
                'symbol' => '→',
                'label' => 'Stable',
            ];
        }

        if ($latest) {
            return [
                'direction' => 'flat',
                'symbol' => '→',
                'label' => 'Only one record',
            ];
        }

        return [
            'direction' => 'flat',
            'symbol' => '—',
            'label' => 'No recent weight data',
        ];
    }

    public function getComputedWeightAttribute()
    {
        $latestLog = $this->relationLoaded('healthLogs')
            ? $this->loadedOrderedWeightLogs()->first()
            : $this->orderedWeightLogs()->first();

        return $latestLog?->weight ?? $this->latest_weight;
    }

    public function getComputedAssetValueAttribute()
    {
        if ((bool) ($this->exclude_from_value_computation ?? false)) {
            return 0;
        }

        return (float) ($this->asset_value ?? 0);
    }

    public function getActiveLiveValueAttribute(): float
    {
        if (!$this->is_active_lifecycle) {
            return 0.0;
        }

        if ((bool) ($this->exclude_from_value_computation ?? false)) {
            return 0.0;
        }

        return (float) $this->computed_asset_value;
    }

    public function getLatestWeightLogDateAttribute()
    {
        $latestLog = $this->relationLoaded('healthLogs')
            ? $this->loadedOrderedWeightLogs()->first()
            : $this->orderedWeightLogs()->first();

        return $latestLog?->log_date;
    }

    public function getDaysSinceLatestWeightAttribute(): ?int
    {
        if (!$this->latest_weight_log_date) {
            return null;
        }

        $latestWeightDate = Carbon::parse($this->latest_weight_log_date)->startOfDay();
        $today = Carbon::today()->startOfDay();

        return max(0, $latestWeightDate->diffInDays($today, false));
    }

    public function getHasStaleWeightAttribute(): bool
    {
        return $this->days_since_latest_weight === null || $this->days_since_latest_weight > 7;
    }

    public function getPositiveGainFromStartAttribute(): ?float
    {
        $logs = $this->relationLoaded('healthLogs')
            ? $this->loadedChronologicalWeightLogs()
            : $this->chronologicalWeightLogs()->get()->values();

        if ($logs->count() < 2) {
            return null;
        }

        $firstLog = $logs->first();
        $latestLog = $logs->last();

        $gain = (float) $latestLog->weight - (float) $firstLog->weight;

        return $gain > 0 ? $gain : null;
    }

    public function getRecentWeightTrendDirectionAttribute(): string
    {
        return $this->recentWeightTrendSnapshot()['direction'];
    }

    public function getRecentWeightTrendSymbolAttribute(): string
    {
        return $this->recentWeightTrendSnapshot()['symbol'];
    }

    public function getRecentWeightTrendLabelAttribute(): string
    {
        return $this->recentWeightTrendSnapshot()['label'];
    }

    public function getFrozenMortalityValueAttribute(): float
    {
        $mortality = $this->latestMortalityRecord();

        if (!$mortality) {
            return (float) ($this->asset_value ?? 0);
        }

        if ($mortality->loss_value !== null) {
            return (float) $mortality->loss_value;
        }

        return (float) ($this->asset_value ?? 0);
    }

    public function getFrozenSaleValueAttribute(): float
    {
        $sale = $this->latestSaleRecord();

        if (!$sale) {
            return (float) ($this->asset_value ?? 0);
        }

        return (float) $sale->price;
    }

    public function getDisplayValueLabelAttribute(): string
    {
        if ($this->is_dead_lifecycle) {
            return 'Frozen Loss Value';
        }

        if ($this->is_sold_lifecycle) {
            return 'Sale Value';
        }

        return 'Asset Value';
    }

    public function getDisplayValueAmountAttribute(): float
    {
        if ($this->is_dead_lifecycle) {
            return $this->frozen_mortality_value;
        }

        if ($this->is_sold_lifecycle) {
            return $this->frozen_sale_value;
        }

        if ($this->is_archived_lifecycle) {
            return (float) ($this->asset_value ?? 0);
        }

        return $this->active_live_value;
    }

    public function getAgeDisplayAttribute(): string
    {
        $days = (int) ($this->age ?? 0);

        if ($days <= 0) {
            return '0 days';
        }

        if ($days < 14) {
            return $days . ' day' . ($days === 1 ? '' : 's');
        }

        if ($days < 60) {
            $weeks = $days / 7;

            return $days . ' days (~' . number_format($weeks, 1) . ' weeks)';
        }

        $months = $days / 30;

        return $days . ' days (~' . number_format($months, 1) . ' months)';
    }

    public function getWeightGainAttribute()
    {
        $logs = $this->relationLoaded('healthLogs')
            ? $this->loadedOrderedWeightLogs()
            : $this->orderedWeightLogs()->take(2)->get()->values();

        if ($logs->count() >= 2) {
            return (float) $logs[0]->weight - (float) $logs[1]->weight;
        }

        if ($logs->count() === 1 && $this->currentBaselineWeight() !== null) {
            return (float) $logs[0]->weight - (float) $this->currentBaselineWeight();
        }

        return null;
    }

    public function getDailyGainAttribute()
    {
        $logs = $this->relationLoaded('healthLogs')
            ? $this->loadedOrderedWeightLogs()
            : $this->orderedWeightLogs()->take(2)->get()->values();

        if ($logs->count() >= 2) {
            $latest = $logs[0];
            $previous = $logs[1];

            $days = max(
                1,
                Carbon::parse($latest->log_date)->diffInDays(Carbon::parse($previous->log_date))
            );

            $gain = (float) $latest->weight - (float) $previous->weight;

            return $gain / $days;
        }

        if ($logs->count() === 1 && $this->currentBaselineWeight() !== null) {
            $latest = $logs[0];
            $baselineDate = $this->date_added ? Carbon::parse($this->date_added) : null;

            $days = $baselineDate
                ? max(1, Carbon::parse($latest->log_date)->diffInDays($baselineDate))
                : 1;

            $gain = (float) $latest->weight - (float) $this->currentBaselineWeight();

            return $gain / $days;
        }

        return null;
    }

    public function getGrowthStatusAttribute()
    {
        $gain = $this->weight_gain;

        if ($gain === null) {
            return 'no_data';
        }

        if ($gain > 0) {
            return 'good';
        }

        if ($gain < 0) {
            return 'declining';
        }

        return 'stagnant';
    }

    public function getTotalFeedCostAttribute()
    {
        if ($this->relationLoaded('feedLogs')) {
            return (float) $this->feedLogs->sum(fn ($log) => (float) ($log->cost ?? 0));
        }

        return (float) $this->feedLogs()->sum('cost');
    }

    public function getTotalMedicationCostAttribute()
    {
        if ($this->relationLoaded('medications')) {
            return (float) $this->medications->sum(fn ($log) => (float) ($log->cost ?? 0));
        }

        return (float) $this->medications()->sum('cost');
    }

    public function getTotalVaccinationCostAttribute()
    {
        if ($this->relationLoaded('vaccinations')) {
            return (float) $this->vaccinations->sum(fn ($log) => (float) ($log->cost ?? 0));
        }

        return (float) $this->vaccinations()->sum('cost');
    }

    public function getTotalSemenCostAttribute()
    {
        if (
            $this->relationLoaded('reproductionCyclesAsSow')
            && $this->reproductionCyclesAsSow->every(
                fn ($cycle) => !$cycle->supportsAttemptMetadata() || $cycle->relationLoaded('updates')
            )
        ) {
            return (float) $this->reproductionCyclesAsSow->sum(fn ($cycle) => (float) $cycle->total_semen_cost);
        }

        return (float) ReproductionCycleUpdate::query()
            ->where('event_type', ReproductionCycleUpdate::EVENT_SERVICE_STARTED)
            ->whereHas('cycle', function ($query) {
                $query->where('sow_id', $this->id);
            })
            ->sum('semen_cost');
    }

    public function getTotalBreedingServiceCostAttribute()
    {
        if ($this->relationLoaded('reproductionCyclesAsSow')) {
            return (float) $this->reproductionCyclesAsSow->sum(fn ($cycle) => (float) ($cycle->breeding_cost ?? 0));
        }

        return (float) $this->reproductionCyclesAsSow()->sum('breeding_cost');
    }

    public function getTotalBreedingCostAttribute()
    {
        return (float) $this->total_breeding_service_cost + (float) $this->total_semen_cost;
    }

    public function getTotalCareLiabilityAttribute()
    {
        return (float) $this->total_medication_cost + (float) $this->total_vaccination_cost;
    }

    public function getTotalOperatingCostAttribute()
    {
        return (float) $this->total_feed_cost
            + (float) $this->total_care_liability
            + (float) $this->total_breeding_cost;
    }

    public function getTotalFeedKgAttribute()
    {
        if ($this->relationLoaded('feedLogs')) {
            return (float) $this->feedLogs
                ->filter(fn ($log) => strtolower((string) $log->unit) === 'kg')
                ->sum(fn ($log) => (float) ($log->quantity ?? 0));
        }

        return (float) $this->feedLogs()
            ->whereRaw('LOWER(unit) = ?', ['kg'])
            ->sum('quantity');
    }

    public function getFeedEfficiencyAttribute()
    {
        $feedKg = (float) $this->total_feed_kg;

        $logs = $this->relationLoaded('healthLogs')
            ? $this->loadedChronologicalWeightLogs()
            : $this->chronologicalWeightLogs()->get()->values();

        $firstLog = $logs->first();
        $latestLog = $logs->last();

        $gainFromStart = null;

        if ($firstLog && $latestLog) {
            $gainFromStart = (float) $latestLog->weight - (float) $firstLog->weight;
        } elseif ($latestLog && $this->currentBaselineWeight() !== null) {
            $gainFromStart = (float) $latestLog->weight - (float) $this->currentBaselineWeight();
        }

        if ($feedKg <= 0 || $gainFromStart === null || $gainFromStart <= 0) {
            return null;
        }

        return $feedKg / $gainFromStart;
    }

    public function getCostPerKgGainAttribute()
    {
        $logs = $this->relationLoaded('healthLogs')
            ? $this->loadedChronologicalWeightLogs()
            : $this->chronologicalWeightLogs()->get()->values();

        $firstLog = $logs->first();
        $latestLog = $logs->last();

        $gainFromStart = null;

        if ($firstLog && $latestLog) {
            $gainFromStart = (float) $latestLog->weight - (float) $firstLog->weight;
        } elseif ($latestLog && $this->currentBaselineWeight() !== null) {
            $gainFromStart = (float) $latestLog->weight - (float) $this->currentBaselineWeight();
        }

        if ($gainFromStart === null || $gainFromStart <= 0) {
            return null;
        }

        return (float) $this->total_operating_cost / $gainFromStart;
    }

    public function getPerformanceStatusAttribute()
    {
        $costPerKgGain = $this->cost_per_kg_gain;
        $feedEfficiency = $this->feed_efficiency;
        $growthStatus = $this->growth_status;

        if ($growthStatus === 'declining') {
            return 'risk';
        }

        if ($growthStatus === 'no_data') {
            return 'no_data';
        }

        if ($growthStatus === 'stagnant') {
            return 'monitor';
        }

        if ($costPerKgGain === null || $feedEfficiency === null) {
            return 'good';
        }

        if ($costPerKgGain > 300 || $feedEfficiency > 4.5) {
            return 'inefficient';
        }

        return 'good';
    }
}
