<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProtocolTemplate extends Model
{
    public const TARGET_PIGLET = 'piglet';
    public const TARGET_LACTATING_SOW = 'lactating_sow';

    public const ANCHOR_BIRTH = 'birth';
    public const ANCHOR_FARROWING = 'farrowing';

    protected $fillable = [
        'code',
        'name',
        'description',
        'target_type',
        'anchor_event',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function rules()
    {
        return $this->hasMany(ProtocolRule::class)
            ->orderBy('sequence_order')
            ->orderBy('id');
    }

    public function activeRules()
    {
        return $this->rules()->where('is_active', true);
    }

    public static function targetTypeOptions(): array
    {
        return [
            self::TARGET_PIGLET => 'Piglet',
            self::TARGET_LACTATING_SOW => 'Lactating Sow',
        ];
    }

    public static function anchorEventOptions(): array
    {
        return [
            self::ANCHOR_BIRTH => 'Birth',
            self::ANCHOR_FARROWING => 'Farrowing',
        ];
    }

    public function getTargetTypeLabelAttribute(): string
    {
        return static::targetTypeOptions()[$this->target_type]
            ?? ucfirst(str_replace('_', ' ', (string) $this->target_type));
    }

    public function getAnchorEventLabelAttribute(): string
    {
        return static::anchorEventOptions()[$this->anchor_event]
            ?? ucfirst(str_replace('_', ' ', (string) $this->anchor_event));
    }
}
