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
        'weight_at_sale',
        'price_per_kg_at_sale',
        'recommended_price',
    ];

    protected $casts = [
        'sold_date' => 'date',
    ];

    public function pig()
    {
        return $this->belongsTo(Pig::class);
    }
}
