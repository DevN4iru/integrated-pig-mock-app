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
    ];

    public function pig()
    {
        return $this->belongsTo(Pig::class);
    }
}