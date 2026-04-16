<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReproductionCycleUpdate extends Model
{
    public const EVENT_SERVICE_STARTED = 'service_started';
    public const EVENT_PREGNANCY_CHECKED = 'pregnancy_checked';
    public const EVENT_RETURNED_TO_HEAT = 'returned_to_heat';
    public const EVENT_FARROWING_RECORDED = 'farrowing_recorded';
    public const EVENT_CYCLE_CLOSED = 'cycle_closed';

    protected $fillable = [
        'reproduction_cycle_id',
        'attempt_number',
        'boar_id',
        'breeding_type',
        'semen_source_type',
        'semen_source_name',
        'semen_cost',
        'event_type',
        'event_date',
        'status_after_event',
        'pregnancy_result',
        'actual_farrow_date',
        'total_born',
        'born_alive',
        'stillborn',
        'mummified',
        'added_cost',
        'notes',
    ];

    protected $casts = [
        'attempt_number' => 'integer',
        'event_date' => 'date',
        'actual_farrow_date' => 'date',
        'added_cost' => 'decimal:2',
        'semen_cost' => 'decimal:2',
        'total_born' => 'integer',
        'born_alive' => 'integer',
        'stillborn' => 'integer',
        'mummified' => 'integer',
    ];

    protected $appends = [
        'event_type_label',
        'status_after_event_label',
        'pregnancy_result_label',
        'total_recorded_outcome',
        'attempt_label',
        'service_setup_label',
    ];

    public function cycle()
    {
        return $this->belongsTo(ReproductionCycle::class, 'reproduction_cycle_id');
    }

    public function donorBoar()
    {
        return $this->belongsTo(Pig::class, 'boar_id');
    }

    public static function eventOptions(): array
    {
        return [
            self::EVENT_SERVICE_STARTED => 'Service Started',
            self::EVENT_PREGNANCY_CHECKED => 'Pregnancy Checked',
            self::EVENT_RETURNED_TO_HEAT => 'Returned to Heat',
            self::EVENT_FARROWING_RECORDED => 'Farrowing Recorded',
            self::EVENT_CYCLE_CLOSED => 'Cycle Closed',
        ];
    }

    public function getEventTypeLabelAttribute(): string
    {
        return static::eventOptions()[$this->event_type]
            ?? ucfirst(str_replace('_', ' ', (string) $this->event_type));
    }

    public function getStatusAfterEventLabelAttribute(): ?string
    {
        if (!$this->status_after_event) {
            return null;
        }

        return ReproductionCycle::statusOptions()[$this->status_after_event]
            ?? ucfirst(str_replace('_', ' ', (string) $this->status_after_event));
    }

    public function getPregnancyResultLabelAttribute(): ?string
    {
        if (!$this->pregnancy_result) {
            return null;
        }

        return ReproductionCycle::pregnancyResultOptions()[$this->pregnancy_result]
            ?? ucfirst(str_replace('_', ' ', (string) $this->pregnancy_result));
    }

    public function getTotalRecordedOutcomeAttribute(): ?int
    {
        $values = [
            $this->born_alive,
            $this->stillborn,
            $this->mummified,
        ];

        $hasAny = collect($values)->contains(fn ($value) => $value !== null);

        if (!$hasAny) {
            return $this->total_born;
        }

        return (int) collect($values)->sum(fn ($value) => (int) ($value ?? 0));
    }

    public function getAttemptLabelAttribute(): string
    {
        return 'Attempt ' . max(1, (int) ($this->attempt_number ?? 1));
    }

    public function getServiceSetupLabelAttribute(): string
    {
        $parts = [];

        if ($this->breeding_type) {
            $parts[] = ReproductionCycle::breedingTypeOptions()[$this->breeding_type]
                ?? ucfirst(str_replace('_', ' ', (string) $this->breeding_type));
        }

        if ($this->boar_id) {
            $parts[] = 'Boar: ' . ($this->donorBoar?->ear_tag ?? '—');
        }

        if ($this->semen_source_type) {
            $parts[] = 'Source: ' . (
                ReproductionCycle::semenSourceOptions()[$this->semen_source_type]
                ?? ucfirst(str_replace('_', ' ', (string) $this->semen_source_type))
            );
        }

        if ($this->semen_source_name) {
            $parts[] = 'Notes: ' . $this->semen_source_name;
        }

        return empty($parts) ? '—' : implode(' • ', $parts);
    }
}
