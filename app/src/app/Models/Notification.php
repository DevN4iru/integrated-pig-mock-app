<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Route;

class Notification extends Model
{
    public const TYPE_PROTOCOL_OVERDUE = 'protocol.overdue';
    public const TYPE_PROTOCOL_DUE_TODAY = 'protocol.due_today';
    public const TYPE_PIG_STALE_WEIGHT = 'pig.stale_weight';
    public const TYPE_BREEDING_DUE_SOON = 'breeding.due_soon';
    public const TYPE_BREEDING_PIGLETS_UNREGISTERED = 'breeding.piglets_unregistered';

    public const SEVERITY_CRITICAL = 'critical';
    public const SEVERITY_WARNING = 'warning';
    public const SEVERITY_INFO = 'info';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'type_code',
        'severity',
        'title',
        'message',
        'route_name',
        'route_params_json',
        'pig_id',
        'reproduction_cycle_id',
        'due_date',
        'context_json',
        'fingerprint',
        'read_at',
        'dismissed_at',
        'resolved_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'route_params_json' => 'array',
        'context_json' => 'array',
        'due_date' => 'date',
        'read_at' => 'datetime',
        'dismissed_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public static function firstWaveTypeCodes(): array
    {
        return [
            self::TYPE_PROTOCOL_OVERDUE,
            self::TYPE_PROTOCOL_DUE_TODAY,
            self::TYPE_PIG_STALE_WEIGHT,
            self::TYPE_BREEDING_DUE_SOON,
            self::TYPE_BREEDING_PIGLETS_UNREGISTERED,
        ];
    }

    public function pig(): BelongsTo
    {
        return $this->belongsTo(Pig::class);
    }

    public function reproductionCycle(): BelongsTo
    {
        return $this->belongsTo(ReproductionCycle::class);
    }

    public function scopeFirstWaveGenerated(Builder $query): Builder
    {
        return $query->whereIn('type_code', static::firstWaveTypeCodes());
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->whereNull('dismissed_at')
            ->whereNull('resolved_at');
    }

    public function scopeHistory(Builder $query): Builder
    {
        return $query->where(function (Builder $builder) {
            $builder
                ->whereNotNull('dismissed_at')
                ->orWhereNotNull('resolved_at');
        });
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query
            ->whereNull('read_at')
            ->whereNull('dismissed_at')
            ->whereNull('resolved_at');
    }

    public function scopeOrderedForList(Builder $query): Builder
    {
        return $query
            ->orderByRaw('CASE WHEN read_at IS NULL THEN 0 ELSE 1 END')
            ->orderByRaw("
                CASE severity
                    WHEN 'critical' THEN 0
                    WHEN 'warning' THEN 1
                    WHEN 'info' THEN 2
                    ELSE 3
                END
            ")
            ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_date')
            ->latest('id');
    }

    public function getSeverityLabelAttribute(): string
    {
        return match ($this->severity) {
            self::SEVERITY_CRITICAL => 'Critical',
            self::SEVERITY_WARNING => 'Warning',
            self::SEVERITY_INFO => 'Info',
            default => ucfirst((string) $this->severity),
        };
    }

    public function getSeverityBadgeClassAttribute(): string
    {
        return match ($this->severity) {
            self::SEVERITY_CRITICAL => 'red',
            self::SEVERITY_WARNING => 'orange',
            default => 'blue',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type_code) {
            self::TYPE_PROTOCOL_OVERDUE => 'Protocol Overdue',
            self::TYPE_PROTOCOL_DUE_TODAY => 'Protocol Due Today',
            self::TYPE_PIG_STALE_WEIGHT => 'Stale Weight',
            self::TYPE_BREEDING_DUE_SOON => 'Breeding Due Soon',
            self::TYPE_BREEDING_PIGLETS_UNREGISTERED => 'Piglet Registration Pending',
            default => ucfirst(str_replace(['.', '_'], ' ', (string) $this->type_code)),
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match (true) {
            $this->resolved_at !== null => 'Resolved',
            $this->dismissed_at !== null => 'Dismissed',
            $this->read_at !== null => 'Read',
            default => 'Unread',
        };
    }

    public function getRouteUrlAttribute(): ?string
    {
        if (blank($this->route_name)) {
            return null;
        }

        if (!Route::has($this->route_name)) {
            return null;
        }

        return route($this->route_name, $this->route_params_json ?? []);
    }

    public function isUnread(): bool
    {
        return $this->read_at === null
            && $this->dismissed_at === null
            && $this->resolved_at === null;
    }

    public function isDismissed(): bool
    {
        return $this->dismissed_at !== null;
    }

    public function isResolved(): bool
    {
        return $this->resolved_at !== null;
    }

    public function syncFromFirstWaveSource(array $attributes): void
    {
        $this->fill($attributes);

        if ($this->resolved_at !== null) {
            $this->resolved_at = null;
        }

        $this->save();
    }

    public function markAsRead(): void
    {
        if ($this->read_at !== null) {
            return;
        }

        $this->forceFill([
            'read_at' => now(),
        ])->save();
    }

    public function dismiss(): void
    {
        $this->forceFill([
            'read_at' => $this->read_at ?? now(),
            'dismissed_at' => $this->dismissed_at ?? now(),
        ])->save();
    }

    public function resolve(): void
    {
        $this->forceFill([
            'read_at' => $this->read_at ?? now(),
            'resolved_at' => $this->resolved_at ?? now(),
        ])->save();
    }

    public function markResolvedFromMissingSource(): void
    {
        if ($this->resolved_at !== null) {
            return;
        }

        $this->resolve();
    }
}
