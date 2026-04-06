<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pen extends Model
{
    protected $fillable = [
        'name',
        'type',
        'capacity',
        'notes',
    ];

    public function pigs(): HasMany
    {
        return $this->hasMany(Pig::class);
    }
}