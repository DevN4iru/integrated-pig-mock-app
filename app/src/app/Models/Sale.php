<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'pig_id',
        'sold_date',
        'price',
        'buyer',
        'notes',
    ];

    public function pig()
    {
        return $this->belongsTo(Pig::class);
    }
}