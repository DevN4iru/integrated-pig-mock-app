<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pen extends Model
{
    public const TYPE_NURSERY = 'Nursery';
    public const TYPE_GROWER = 'Grower';
    public const TYPE_FINISHER = 'Finisher';
    public const TYPE_REPLACEMENT_GILT = 'Replacement Gilt';
    public const TYPE_SOW = 'Sow';
    public const TYPE_GESTATION = 'Gestation';
    public const TYPE_FARROWING = 'Farrowing';
    public const TYPE_BOAR = 'Boar';
    public const TYPE_BREEDING_SERVICE = 'Breeding / Service';
    public const TYPE_QUARANTINE = 'Quarantine';
    public const TYPE_SOW_QUARANTINE = 'Sow Quarantine';
    public const TYPE_ISOLATION = 'Isolation';
    public const TYPE_HOSPITAL_TREATMENT = 'Hospital / Treatment';

    protected $fillable = [
        'name',
        'type',
        'capacity',
        'notes',
    ];

    public static function typeOptions(): array
    {
        return [
            self::TYPE_NURSERY,
            self::TYPE_GROWER,
            self::TYPE_FINISHER,
            self::TYPE_REPLACEMENT_GILT,
            self::TYPE_SOW,
            self::TYPE_GESTATION,
            self::TYPE_FARROWING,
            self::TYPE_BOAR,
            self::TYPE_BREEDING_SERVICE,
            self::TYPE_QUARANTINE,
            self::TYPE_SOW_QUARANTINE,
            self::TYPE_ISOLATION,
            self::TYPE_HOSPITAL_TREATMENT,
        ];
    }

    public function pigs(): HasMany
    {
        return $this->hasMany(Pig::class);
    }

    public function availableSlots(): int
    {
        $occupied = isset($this->pigs_count)
            ? (int) $this->pigs_count
            : (int) $this->pigs()->count();

        return max((int) $this->capacity - $occupied, 0);
    }

    public function occupancyPercent(): float
    {
        $capacity = max((int) $this->capacity, 1);
        $occupied = isset($this->pigs_count)
            ? (int) $this->pigs_count
            : (int) $this->pigs()->count();

        return min(100, round(($occupied / $capacity) * 100, 2));
    }

    public function occupancyStatus(): string
    {
        $available = $this->availableSlots();

        if ($available <= 0) {
            return 'full';
        }

        if ($available <= 2) {
            return 'limited';
        }

        return 'open';
    }

    public function typeBadgeClass(): string
    {
        return match ($this->type) {
            self::TYPE_NURSERY,
            self::TYPE_GROWER,
            self::TYPE_FINISHER => 'blue',

            self::TYPE_REPLACEMENT_GILT,
            self::TYPE_SOW,
            self::TYPE_GESTATION,
            self::TYPE_FARROWING,
            self::TYPE_BOAR,
            self::TYPE_BREEDING_SERVICE => 'green',

            self::TYPE_QUARANTINE,
            self::TYPE_SOW_QUARANTINE,
            self::TYPE_ISOLATION,
            self::TYPE_HOSPITAL_TREATMENT => 'red',

            default => 'orange',
        };
    }
}
