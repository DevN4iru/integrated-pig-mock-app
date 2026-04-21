<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MortalityLog extends Model
{
    protected $fillable = [
        'pig_id',
        'death_date',
        'cause',
        'notes',
        'weight_at_death',
        'price_per_kg_at_death',
        'loss_value',
    ];

    protected $casts = [
        'death_date' => 'date',
        'weight_at_death' => 'decimal:2',
        'price_per_kg_at_death' => 'decimal:2',
        'loss_value' => 'decimal:2',
    ];

    public function pig()
    {
        return $this->belongsTo(Pig::class);
    }
}
