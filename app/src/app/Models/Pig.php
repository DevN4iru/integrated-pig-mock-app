<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pig extends Model
{
    protected $fillable = [
        'ear_tag',
        'breed',
        'sex',
        'pen_location',
        'status',
        'origin_date',
        'latest_weight',
        'weight_date_added',
        'asset_value',
        'date_sold',
        'weight_sold_kg',
        'price_sold',
    ];
}
