<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreFarrowChecklistStatus extends Model
{
    protected $fillable = [
        'reproduction_cycle_id',
        'checklist_key',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function cycle()
    {
        return $this->belongsTo(ReproductionCycle::class, 'reproduction_cycle_id');
    }
}
