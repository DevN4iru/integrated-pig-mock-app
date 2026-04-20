<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProtocolExecution extends Model
{
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_SKIPPED = 'skipped';
    public const STATUS_DEFERRED = 'deferred';

    protected $fillable = [
        'pig_id',
        'protocol_rule_id',
        'scheduled_for_date',
        'status',
        'executed_date',
        'notes',
    ];

    protected $casts = [
        'scheduled_for_date' => 'date',
        'executed_date' => 'date',
    ];

    public function pig()
    {
        return $this->belongsTo(Pig::class);
    }

    public function rule()
    {
        return $this->belongsTo(ProtocolRule::class, 'protocol_rule_id');
    }

    public function medication()
    {
        return $this->hasOne(Medication::class, 'protocol_execution_id');
    }

    public function vaccination()
    {
        return $this->hasOne(Vaccination::class, 'protocol_execution_id');
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_SKIPPED => 'Skipped',
            self::STATUS_DEFERRED => 'Deferred',
        ];
    }

    public static function resolvedStatuses(): array
    {
        return [
            self::STATUS_COMPLETED,
            self::STATUS_SKIPPED,
        ];
    }

    public function isResolved(): bool
    {
        return in_array($this->status, static::resolvedStatuses(), true);
    }

    public function getStatusLabelAttribute(): string
    {
        return static::statusOptions()[$this->status]
            ?? ucfirst(str_replace('_', ' ', (string) $this->status));
    }
}
