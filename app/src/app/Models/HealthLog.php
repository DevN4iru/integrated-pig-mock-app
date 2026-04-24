<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HealthLog extends Model
{
    protected $fillable = [
        'pig_id',
        'purpose',
        'condition',
        'weight',
        'notes',
        'log_date',
    ];

    public function pig()
    {
        return $this->belongsTo(Pig::class);
    }
}