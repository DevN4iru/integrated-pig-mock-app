<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReproductionCycle extends Model
{
    public const BREEDING_TYPE_NATURAL_MATING = 'natural_mating';
    public const BREEDING_TYPE_ARTIFICIAL_INSEMINATION = 'artificial_insemination';

    public const SEMEN_SOURCE_LOCAL = 'local';
    public const SEMEN_SOURCE_PURCHASED = 'purchased';

    public const STATUS_SERVICED = 'serviced';
    public const STATUS_PREGNANT = 'pregnant';
    public const STATUS_NOT_PREGNANT = 'not_pregnant';
    public const STATUS_RETURNED_TO_HEAT = 'returned_to_heat';
    public const STATUS_DUE_SOON = 'due_soon';
    public const STATUS_FARROWED = 'farrowed';
    public const STATUS_CLOSED = 'closed';

    public const PREGNANCY_RESULT_PENDING = 'pending';
    public const PREGNANCY_RESULT_PREGNANT = 'pregnant';
    public const PREGNANCY_RESULT_NOT_PREGNANT = 'not_pregnant';

    protected $fillable = [
        'sow_id',
        'boar_id',
        'breeding_type',
        'service_date',
        'pregnancy_check_date',
        'pregnancy_result',
        'expected_farrow_date',
        'actual_farrow_date',
        'status',
        'semen_source_type',
        'semen_source_name',
        'semen_cost',
        'breeding_cost',
        'total_born',
        'born_alive',
        'stillborn',
        'mummified',
        'notes',
    ];

    protected $casts = [
        'service_date' => 'date',
        'pregnancy_check_date' => 'date',
        'expected_farrow_date' => 'date',
        'actual_farrow_date' => 'date',
        'semen_cost' => 'decimal:2',
        'breeding_cost' => 'decimal:2',
        'total_born' => 'integer',
        'born_alive' => 'integer',
        'stillborn' => 'integer',
        'mummified' => 'integer',
    ];

    protected $appends = [
        'breeding_type_label',
        'display_status',
        'display_status_label',
        'status_label',
        'pregnancy_result_label',
        'total_recorded_outcome',
        'recommended_pen_type',
        'is_active_cycle',
        'is_due_soon',
    ];

    public function sow()
    {
        return $this->belongsTo(Pig::class, 'sow_id');
    }

    public function boar()
    {
        return $this->belongsTo(Pig::class, 'boar_id');
    }

    public function updates()
    {
        return $this->hasMany(ReproductionCycleUpdate::class)
            ->orderBy('event_date')
            ->orderBy('id');
    }

    public function bornPiglets()
    {
        return $this->hasMany(Pig::class, 'reproduction_cycle_id')
            ->orderBy('date_added')
            ->orderBy('id');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', static::activeStatuses());
    }

    public static function breedingTypeOptions(): array
    {
        return [
            self::BREEDING_TYPE_NATURAL_MATING => 'Natural Mating',
            self::BREEDING_TYPE_ARTIFICIAL_INSEMINATION => 'Artificial Insemination',
        ];
    }

    public static function semenSourceOptions(): array
    {
        return [
            self::SEMEN_SOURCE_LOCAL => 'Locally Sourced',
            self::SEMEN_SOURCE_PURCHASED => 'Purchased',
        ];
    }

    public static function pregnancyResultOptions(): array
    {
        return [
            self::PREGNANCY_RESULT_PENDING => 'Pending',
            self::PREGNANCY_RESULT_PREGNANT => 'Pregnant',
            self::PREGNANCY_RESULT_NOT_PREGNANT => 'Not Pregnant',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_SERVICED => 'Serviced',
            self::STATUS_PREGNANT => 'Pregnant',
            self::STATUS_NOT_PREGNANT => 'Not Pregnant',
            self::STATUS_RETURNED_TO_HEAT => 'Returned to Heat',
            self::STATUS_DUE_SOON => 'Due Soon',
            self::STATUS_FARROWED => 'Farrowed',
            self::STATUS_CLOSED => 'Closed',
        ];
    }

    public static function activeStatuses(): array
    {
        return [
            self::STATUS_SERVICED,
            self::STATUS_PREGNANT,
            self::STATUS_DUE_SOON,
        ];
    }

    public static function dueSoonThresholdDays(): int
    {
        return 7;
    }

    public function getBreedingTypeLabelAttribute(): string
    {
        return static::breedingTypeOptions()[$this->breeding_type]
            ?? ucfirst(str_replace('_', ' ', (string) $this->breeding_type));
    }

    public function getDisplayStatusAttribute(): string
    {
        return $this->resolveDisplayStatus();
    }

    public function getDisplayStatusLabelAttribute(): string
    {
        $status = $this->display_status;

        return static::statusOptions()[$status]
            ?? ucfirst(str_replace('_', ' ', (string) $status));
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->display_status_label;
    }

    public function getPregnancyResultLabelAttribute(): string
    {
        return static::pregnancyResultOptions()[$this->pregnancy_result]
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

    public function getRecommendedPenTypeAttribute(): ?string
    {
        return match ($this->display_status) {
            self::STATUS_SERVICED,
            self::STATUS_RETURNED_TO_HEAT => Pen::TYPE_BREEDING_SERVICE,
            self::STATUS_PREGNANT => Pen::TYPE_GESTATION,
            self::STATUS_DUE_SOON,
            self::STATUS_FARROWED => Pen::TYPE_FARROWING,
            default => null,
        };
    }

    public function getIsActiveCycleAttribute(): bool
    {
        return in_array($this->display_status, static::activeStatuses(), true);
    }

    public function getIsDueSoonAttribute(): bool
    {
        return $this->display_status === self::STATUS_DUE_SOON;
    }

    protected function resolveDisplayStatus(): string
    {
        if (
            in_array($this->status, [self::STATUS_PREGNANT, self::STATUS_DUE_SOON], true)
            && $this->pregnancy_result === self::PREGNANCY_RESULT_PREGNANT
            && !$this->actual_farrow_date
        ) {
            if ($this->expected_farrow_date) {
                $daysUntilDue = now()->startOfDay()->diffInDays($this->expected_farrow_date->copy()->startOfDay(), false);

                if ($daysUntilDue >= 0 && $daysUntilDue <= static::dueSoonThresholdDays()) {
                    return self::STATUS_DUE_SOON;
                }
            }

            return self::STATUS_PREGNANT;
        }

        return $this->status;
    }
}
