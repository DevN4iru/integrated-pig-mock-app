<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medication extends Model
{
    protected $fillable = [
        'pig_id',
        'medication_name',
        'dosage',
        'administered_at',
        'notes',
    ];

    public function pig()
    {
        return $this->belongsTo(Pig::class);
    }
}