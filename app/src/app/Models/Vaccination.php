<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vaccination extends Model
{
    protected $fillable = [
        'pig_id',
        'vaccine_name',
        'dose',
        'vaccinated_at',
        'notes',
    ];

    public function pig()
    {
        return $this->belongsTo(Pig::class);
    }
}