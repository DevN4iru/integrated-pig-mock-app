<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedLog extends Model
{
    protected $fillable = [
        'pig_id',
        'feed_type',
        'start_feed_date',
        'end_feed_date',
        'quantity',
        'cost',
        'unit',
        'feeding_time',
        'status',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'float',
        'cost' => 'float',
    ];

    public function pig()
    {
        return $this->belongsTo(Pig::class);
    }
}
