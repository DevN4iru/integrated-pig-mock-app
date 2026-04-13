<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReproductionCycle extends Model
{
    protected $fillable = [
        'sow_id',
        'boar_id',
        'breeding_type',
        'service_date',
        'pregnancy_check_date',
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
        'status_label',
        'total_recorded_outcome',
    ];

    public function sow()
    {
        return $this->belongsTo(Pig::class, 'sow_id');
    }

    public function boar()
    {
        return $this->belongsTo(Pig::class, 'boar_id');
    }

    public function getBreedingTypeLabelAttribute(): string
    {
        return match ($this->breeding_type) {
            'natural_mating' => 'Natural Mating',
            'artificial_insemination' => 'Artificial Insemination',
            default => ucfirst(str_replace('_', ' ', (string) $this->breeding_type)),
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'open' => 'Open',
            'pregnant' => 'Pregnant',
            'failed' => 'Failed',
            'farrowed' => 'Farrowed',
            default => ucfirst(str_replace('_', ' ', (string) $this->status)),
        };
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
}
