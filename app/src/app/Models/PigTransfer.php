<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PigTransfer extends Model
{
    public const REASON_QUARANTINE_DUE_TO_SICKNESS = 'quarantine_due_to_sickness';
    public const REASON_ISOLATION_DUE_TO_LOW_WEIGHT = 'isolation_due_to_low_weight';
    public const REASON_REGROUP_BY_WEIGHT = 'regroup_by_weight';
    public const REASON_BREEDING_PREPARATION = 'breeding_preparation';
    public const REASON_GESTATION_TRANSFER = 'gestation_transfer';
    public const REASON_FARROWING_TRANSFER = 'farrowing_transfer';
    public const REASON_CAPACITY_REBALANCE = 'capacity_rebalance';
    public const REASON_TREATMENT_MONITORING = 'treatment_monitoring';
    public const REASON_OTHER = 'other';

    protected $fillable = [
        'pig_id',
        'from_pen_id',
        'to_pen_id',
        'transfer_date',
        'reason_code',
        'reason_notes',
    ];

    protected $casts = [
        'transfer_date' => 'date',
    ];

    public static function reasonOptions(): array
    {
        return [
            self::REASON_QUARANTINE_DUE_TO_SICKNESS => 'Quarantine due to sickness',
            self::REASON_ISOLATION_DUE_TO_LOW_WEIGHT => 'Isolation due to low weight',
            self::REASON_REGROUP_BY_WEIGHT => 'Regroup by weight',
            self::REASON_BREEDING_PREPARATION => 'Breeding preparation',
            self::REASON_GESTATION_TRANSFER => 'Gestation transfer',
            self::REASON_FARROWING_TRANSFER => 'Farrowing transfer',
            self::REASON_CAPACITY_REBALANCE => 'Capacity rebalance',
            self::REASON_TREATMENT_MONITORING => 'Treatment / monitoring',
            self::REASON_OTHER => 'Other',
        ];
    }

    public function pig(): BelongsTo
    {
        return $this->belongsTo(Pig::class);
    }

    public function fromPen(): BelongsTo
    {
        return $this->belongsTo(Pen::class, 'from_pen_id');
    }

    public function toPen(): BelongsTo
    {
        return $this->belongsTo(Pen::class, 'to_pen_id');
    }

    public function getReasonLabelAttribute(): string
    {
        return static::reasonOptions()[$this->reason_code] ?? ucfirst(str_replace('_', ' ', $this->reason_code));
    }
}
