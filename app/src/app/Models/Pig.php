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
        'reproduction_cycle_id',
        'date_added',
        'latest_weight',
        'asset_value',
    ];

    protected $casts = [
        'age' => 'integer',
    ];

    protected $appends = [
        'computed_weight',
        'computed_asset_value',
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
    ];

    public function pen()
    {
        return $this->belongsTo(Pen::class);
    }

    public function motherSow()
    {
        return $this->belongsTo(Pig::class, 'mother_sow_id');
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

    public function protocolExecutions()
    {
        return $this->hasMany(ProtocolExecution::class)
            ->orderBy('scheduled_for_date')
            ->orderBy('id');
    }

    protected function resolveProtocolTemplate(): ?ProtocolTemplate
    {
        if ($this->pig_source === 'birthed') {
            return ProtocolTemplate::where('target_type', ProtocolTemplate::TARGET_PIGLET)
                ->where('is_active', true)
                ->first();
        }

        $cycle = $this->reproductionCyclesAsSow()
            ->whereNotNull('actual_farrow_date')
            ->latest('actual_farrow_date')
            ->first();

        if ($cycle) {
            return ProtocolTemplate::where('target_type', ProtocolTemplate::TARGET_LACTATING_SOW)
                ->where('is_active', true)
                ->first();
        }

        return null;
    }

    protected function resolveProtocolAnchorDate(?ProtocolTemplate $template): ?Carbon
    {
        if (!$template) {
            return null;
        }

        if ($template->anchor_event === ProtocolTemplate::ANCHOR_BIRTH) {
            return $this->date_added ? Carbon::parse($this->date_added) : null;
        }

        if ($template->anchor_event === ProtocolTemplate::ANCHOR_FARROWING) {
            $cycle = $this->reproductionCyclesAsSow()
                ->whereNotNull('actual_farrow_date')
                ->latest('actual_farrow_date')
                ->first();

            return $cycle?->actual_farrow_date
                ? Carbon::parse($cycle->actual_farrow_date)
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
            ProtocolRule::CONDITION_SEX_MALE => $this->sex === 'male',
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

            $start = $anchorDate->copy()->addDays((int) $rule->day_offset_start);
            $end = $rule->day_offset_end !== null
                ? $anchorDate->copy()->addDays((int) $rule->day_offset_end)
                : $start;

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

    protected function relationHasAny(string $relation): bool
    {
        if ($this->relationLoaded($relation)) {
            return $this->{$relation}->isNotEmpty();
        }

        return $this->{$relation}()->exists();
    }

    public function isOperationallyLocked(): bool
    {
        return $this->trashed()
            || $this->relationHasAny('mortalityLogs')
            || $this->relationHasAny('sales');
    }

    public function operationalLockState(): ?string
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

        return null;
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

    protected function currentBaselineWeight(): ?float
    {
        return $this->latest_weight !== null && $this->latest_weight !== ''
            ? (float) $this->latest_weight
            : null;
    }

    public function getComputedWeightAttribute()
    {
        $latestLog = $this->orderedWeightLogs()->first();

        return $latestLog?->weight ?? $this->latest_weight;
    }

    public function getComputedAssetValueAttribute()
    {
        $weight = $this->computed_weight;

        if ($weight === null || $weight === '') {
            return 0;
        }

        return (float) $weight * FarmSetting::currentPricePerKg();
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
        $logs = $this->orderedWeightLogs()
            ->take(2)
            ->get()
            ->values();

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
        $logs = $this->orderedWeightLogs()
            ->take(2)
            ->get()
            ->values();

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

        $logs = $this->chronologicalWeightLogs()->get()->values();
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
        $logs = $this->chronologicalWeightLogs()->get()->values();
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
